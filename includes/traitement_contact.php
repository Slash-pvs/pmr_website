<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Connexion PDO
$pdo = require __DIR__ . '/../db.php';
if (!$pdo) {
    exit("Erreur de connexion à la base de données.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("Méthode non autorisée.");
}

// Vérification CSRF
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header("Location: contact.php?error=1");
    exit();
}

// Honeypot anti-bot
if (!empty($_POST['website'])) {
    header("Location: contact.php?success=1");
    exit();
}

// Récupérer IP
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

// Limitation : max 3 tentatives en 10 minutes
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM contact_attempts 
    WHERE ip_address = :ip AND created_at >= NOW() - INTERVAL 10 MINUTE
");
$stmt->execute(['ip' => $ip]);
$attempts = (int)$stmt->fetchColumn();

if ($attempts >= 3) {
    header("Location: contact.php?error=1");
    exit();
}

// Récupérer et valider les champs
$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if (empty($nom) || empty($email) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: contact.php?error=1");
    exit();
}

// Prévenir l’injection d’entêtes mail
if (preg_match("/[\r\n]/", $email)) {
    header("Location: contact.php?error=1");
    exit();
}

// Enregistrer la tentative
$stmt = $pdo->prepare("INSERT INTO contact_attempts (ip_address) VALUES (:ip)");
$stmt->execute(['ip' => $ip]);

// Préparer l’e-mail
$destinataire = "pays.medoc.rugby@orange.fr";
$sujet = "Nouveau message de contact de $nom";
$contenu = "Nom: $nom\nEmail: $email\nMessage:\n$message";

$headers = "From: contact@tonsite.fr\r\n";
$headers .= "Reply-To: $email\r\n";

// Envoyer l’e-mail
if (mail($destinataire, $sujet, $contenu, $headers)) {
    header("Location: contact.php?success=1");
} else {
    header("Location: contact.php?error=1");
}

exit();
