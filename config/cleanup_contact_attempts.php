<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Accès interdit.');
}
// Fichier : cleanup_contact_attempts.php

$pdo = require __DIR__ . '/../db.php'; // adapte le chemin si nécessaire

$sql = "DELETE FROM contact_attempts WHERE created_at < NOW() - INTERVAL 1 DAY";
$pdo->exec($sql);

// Optionnel : log
file_put_contents(__DIR__ . '/cleanup_log.txt', "[" . date('Y-m-d H:i:s') . "] Tentatives anciennes supprimées.\n", FILE_APPEND);
?>
