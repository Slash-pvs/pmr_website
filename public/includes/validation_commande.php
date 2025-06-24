<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$pdo = require __DIR__ . '/../db.php';
if (!$pdo) {
    echo "Erreur de connexion à la base de données.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        header("Location: /public/panier.php?error=1");
        exit();
    }

    // Honeypot anti-bot
    if (!empty($_POST['website'])) {
        header("Location: /public/panier.php?success=1");
        exit();
    }

    // Adresse IP
    $ip = $_SERVER['REMOTE_ADDR'];

    // Limitation des tentatives
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_attempts WHERE ip_address = :ip AND created_at >= NOW() - INTERVAL 10 MINUTE");
    $stmt->execute(['ip' => $ip]);
    $attempts = $stmt->fetchColumn();

    if ($attempts >= 3) {
        header("Location: /public/panier.php?error=1"); // Trop de tentatives
        exit();
    }

    // Récupération des données
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (
        empty($nom) || empty($prenom) || empty($email) ||
        empty($telephone) || !filter_var($email, FILTER_VALIDATE_EMAIL)
    ) {
        header("Location: /public/panier.php?error=1");
        exit();
    }

    // Protection contre l'injection d'en-tête
    if (preg_match("/[\r\n]/", $email)) {
        header("Location: /public/panier.php?error=1");
        exit();
    }

    // Enregistrer la tentative
    $stmt = $pdo->prepare("INSERT INTO contact_attempts (ip_address) VALUES (:ip)");
    $stmt->execute(['ip' => $ip]);

    // Envoi de l'e-mail
    $destinataire = "vergnepier@gmail.com"; // À personnaliser
    $sujet = "Nouvelle commande de $prenom $nom";

    $contenu = "Nom: $nom\nPrénom: $prenom\nEmail: $email\nTéléphone: $telephone\nMessage:\n$message\n\n";

    // Ajouter le détail du panier
    $contenu .= "🛒 Détail du panier :\n\n";

    $panier = $_SESSION['panier'] ?? [];
    $total = 0;

    if (!empty($panier)) {
        foreach ($panier as $article) {
            $nomProduit = $article['nom'];
            $quantite = $article['quantite'];
            $prix = number_format($article['prix'], 2, ',', ' ');
            $sousTotal = number_format($quantite * $article['prix'], 2, ',', ' ');
            $total += $quantite * $article['prix'];

            $contenu .= "- $nomProduit | Quantité : $quantite | Prix unitaire : $prix € | Total : $sousTotal €\n";
        }

        $contenu .= "\nTotal général : " . number_format($total, 2, ',', ' ') . " €\n";
    } else {
        $contenu .= "Le panier est vide.\n";
    }


    $headers = "From: contact@tonsite.fr\r\n";
    $headers .= "Reply-To: $email\r\n";

    if (mail($destinataire, $sujet, $contenu, $headers)) {
        unset($_SESSION['panier']); // Vider le panier
        header("Location: /public/panier.php?success=1");
    } else {
        header("Location: /public/panier.php?error=1");
    }


    exit();
} else {
    http_response_code(405);
    echo "Méthode non autorisée.";
}
