<?php
// Démarrage de session sécurisé
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion des dépendances
$pdo = require __DIR__ . '/../db.php';
require_once 'article_functions.php';

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit("⛔ Accès non autorisé. Veuillez vous connecter.");
}

// Vérifie que la requête est bien en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit("❌ Méthode non autorisée.");
}

// Vérifie la validité du token CSRF
if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    exit("🔒 Token CSRF invalide.");
}

// Récupération et validation de l'ID
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    http_response_code(400);
    exit("⚠️ ID invalide.");
}

// Tentative de suppression de l'article
if (deleteArticle($pdo, $id)) {
    header("Location: dashboard.php");
    exit;
}
 else {
    // Échec de suppression (peut être journalisé)
    http_response_code(500);
    error_log("❗ Échec de suppression de l'article avec l'ID : $id");
    exit("Erreur lors de la suppression de l'article.");
}
