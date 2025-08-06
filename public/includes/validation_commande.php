<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Génération du token CSRF si absent
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Connexion à la BDD
$pdo = require __DIR__ . '/../db.php';
if (!$pdo) {
    echo "Erreur de connexion à la base de données.";
    exit();
}

// Fonction de redirection centralisée
function redirect($url) {
    header("Location: $url");
    exit();
}

// Traitement uniquement sur POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Méthode non autorisée.";
    exit();
}

// ✅ Vérification CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    redirect("/public/panier.php?error=1");
}

// ✅ Honeypot anti-bot
if (!empty($_POST['website'])) {
    redirect("/public/panier.php?success=1");
}

// ✅ Adresse IP
$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

// ✅ Limitation des tentatives sur 10 min
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM contact_attempts 
    WHERE ip_address = :ip 
      AND created_at >= NOW() - INTERVAL 10 MINUTE
");
$stmt->execute(['ip' => $ip]);
$attempts = $stmt->fetchColumn();

if ($attempts >= 3) {
    redirect("/public/panier.php?error=1"); // Trop de tentatives
}

// ✅ Récupération et nettoyage des données
$nom       = htmlspecialchars(trim($_POST['nom'] ?? ''), ENT_QUOTES, 'UTF-8');
$prenom    = htmlspecialchars(trim($_POST['prenom'] ?? ''), ENT_QUOTES, 'UTF-8');
$email     = htmlspecialchars(trim($_POST['email'] ?? ''), ENT_QUOTES, 'UTF-8');
$telephone = htmlspecialchars(trim($_POST['telephone'] ?? ''), ENT_QUOTES, 'UTF-8');
$message   = htmlspecialchars(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');

// ✅ Vérifications basiques
if (
    empty($nom) || empty($prenom) || empty($email) || empty($telephone) ||
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {
    redirect("/public/panier.php?error=1");
}

// ✅ Protection contre l'injection d'en-tête email
if (preg_match("/[\r\n]/", $email)) {
    redirect("/public/panier.php?error=1");
}

// ✅ Enregistrement de la tentative
$stmt = $pdo->prepare("
    INSERT INTO contact_attempts (ip_address, created_at)
    VALUES (:ip, NOW())
");
$stmt->execute(['ip' => $ip]);

// ✅ Contenu de l’email
$destinataire = "vergnepier@gmail.com"; // À adapter
$sujet = "Nouvelle commande de $prenom $nom";

$contenu = <<<EOD
Nom : $nom
Prénom : $prenom
Email : $email
Téléphone : $telephone
Message :
$message

🛒 Détail du panier :

EOD;

$panier = $_SESSION['panier'] ?? [];
$total = 0;

if (!empty($panier)) {
    foreach ($panier as $article) {
        $nomProduit = htmlspecialchars($article['nom'], ENT_QUOTES, 'UTF-8');
        $quantite   = (int) $article['quantite'];
        $prix       = number_format($article['prix'], 2, ',', ' ');
        $sousTotal  = number_format($quantite * $article['prix'], 2, ',', ' ');
        $total     += $quantite * $article['prix'];

        $contenu .= "\n-----------------------------\n";
        $contenu .= "$nomProduit\n";
        $contenu .= "Quantité : $quantite\n";
        $contenu .= "Prix unitaire : $prix €\n";
        $contenu .= "Total : $sousTotal €\n";
    }

    $contenu .= "\n-----------------------------\n";
    $contenu .= "Total général : " . number_format($total, 2, ',', ' ') . " €\n";
} else {
    $contenu .= "Le panier est vide.\n";
}

// ✅ Headers de l’email
$headers = "From: Site Boutique <no-reply@tonsite.fr>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// ✅ Envoi de l’e-mail
if (mail($destinataire, $sujet, $contenu, $headers)) {
    unset($_SESSION['panier']); // Vider le panier après envoi
    redirect("/public/panier.php?success=1");
} else {
    redirect("/public/panier.php?error=1");
}
