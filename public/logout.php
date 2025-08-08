<?php
session_start();
handleRequest();
session_destroy();  // Détruit toutes les sessions
header('Location: page_login.php');  // Redirige vers la page de connexion
exit;
?>
