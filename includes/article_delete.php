<?php
session_start();
require_once '../db.php';
require_once 'article_functions.php';

// Vérification CSRF, POST et session déjà comme avant
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    http_response_code(400);
    exit("⚠️ ID invalide.");
}

$errorMessage = '';
if (deleteArticleByIdWithCheck($pdo, $id, $errorMessage)) {
    $_SESSION['flash_message'] = [
        'type' => 'success',
        'text' => "✅ Article supprimé avec succès."
    ];
} else {
    $_SESSION['flash_message'] = [
        'type' => 'error',
        'text' => "❌ $errorMessage"
    ];
}

header("Location: dashboard.php");
exit;
