<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/functions.php';
$pdo = require __DIR__ . '/db.php';

// Vérifie si l'utilisateur est connecté, sinon redirige vers la page de connexion
if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Récupère les informations de l'utilisateur connecté
$userInfo = getUserInfo($pdo);

$posts = getAllPosts($pdo);
// Récupération des données pour la navigation et les partenaires
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/nav.css">
    <link rel="stylesheet" href="/public/css/footer.css">
</head>

<body>

    <!-- Menu de navigation -->
    <?php safeRequire('nav.php'); ?>
    <main id="mainContent" class="main-content">
        <!-- Contenu principal -->
        <div class="dashboard">
            <a href="article_form.php"> ajouter un article</a>
            <a href="article_list.php"> modifier un article</a>
            <a href="presentation_crud.php"> modifier / ajouter une présentation</a>
            <a href="contacts_crud.php"> modifier / ajouter un contact</a>
            <a href="product_list.php"> modifier / ajouter un produit de la boutique</a>
            <a href="partenaires_crud.php"> modifier / ajouter un partenaire</a>
             <a href="upload_img.php"> ajouter une image</a>
             <a href="edit_image_nav.php">  modifier l'image de la Bannière du site</a>     
        </div>
    </main>
    <!-- Footer -->
    <?php
    // Récupération de l'image de navigation
    includeFooter($contact, $partenaires);
    ?>

    <!-- Scripts -->
    <script src="/public/js/scroll.js" defer></script>
    <script src="/public/js/nav_img.js" defer></script>
    <script src="/public/js/modal_image_background_nav.js" defer></script>
    <script src="/public/js/menuburger.js" defer></script>
    <script src="/public/js/modal_gallery.js" defer></script>
    <script src="/public/js/slide-partenaire.js" defer></script>
</body>

</html>