<?php
session_start();

// Détruit toutes les données de session
$_SESSION = [];
session_unset();
session_destroy();

// Redirige vers la page de login
header('Location: page_login.php');
exit;
