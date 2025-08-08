<?php
session_start();
require_once __DIR__ . '/functions.php';
$pdo = require __DIR__ . '/db.php';
handleRequest($pdo);
session_destroy();  // Détruit toutes les sessions
header('Location: page_login.php');  // Redirige vers la page de connexion
exit;
?>
