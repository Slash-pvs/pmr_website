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
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: /public/contact.php?error=1");
        exit();
    }

    // Honeypot : champ caché rempli = bot
    if (!empty($_POST['website'])) {
        header("Location: /public/contact.php?success=1");
        exit();
    }

    // Adresse IP de l'utilisateur
    $ip = $_SERVER['REMOTE_ADDR'];

    // Vérification du nombre de soumissions récentes (3 en 10 minutes)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM contact_attempts WHERE ip_address = :ip AND created_at >= NOW() - INTERVAL 10 MINUTE");
    $stmt->execute(['ip' => $ip]);
    $attempts = $stmt->fetchColumn();

    if ($attempts >= 3) {
        header("Location: /public/contact.php?error=1"); // Trop de tentatives
        exit();
    }

    // Validation des champs
    $nom = trim($_POST["nom"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $message = trim($_POST["message"] ?? '');

    if (empty($nom) || empty($email) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: /public/contact.php?error=1");
        exit();
    }

    if (preg_match("/[\r\n]/", $email)) {
        header("Location: /public/contact.php?error=1");
        exit();
    }

    // Sauvegarde de la tentative
    $stmt = $pdo->prepare("INSERT INTO contact_attempts (ip_address) VALUES (:ip)");
    $stmt->execute(['ip' => $ip]);

    // Envoi de l'e-mail
    $destinataire = "pays.medoc.rugby@orange.fr"; // à personnaliser
    $sujet = "Nouveau message de contact de $nom";
    $contenu = "Nom: $nom\nEmail: $email\nMessage:\n$message";

    $headers = "From: contact@tonsite.fr\r\n";
    $headers .= "Reply-To: $email\r\n";

    if (mail($destinataire, $sujet, $contenu, $headers)) {
        header("Location: /public/contact.php?success=1");
    } else {
        header("Location: /public/contact.php?error=1");
    }

    exit();
} else {
    http_response_code(405);
    echo "Méthode non autorisée.";
}
