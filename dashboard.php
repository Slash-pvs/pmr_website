<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/functions.php';
$pdo = require __DIR__ . '/db.php';

// Vérifie si l'utilisateur est connecté
if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Récupère les informations de l'utilisateur connecté
$userInfo = getUserInfo($pdo);

// Récupération des données pour la navigation et les partenaires
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
$partenaires = enrichPartnersWithVersions($pdo, $partenaires);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Tableau de bord</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/nav.css">
    <link rel="stylesheet" href="/css/footer.css">
    <link rel="stylesheet" href="/css/dashboard.css">
</head>

<body>
    <?php safeRequire('nav.php'); ?>

    <main id="mainContent" class="main-content">
        <h1>Tableau de bord</h1>

        <div class="dashboard">
            <a href="article_form.php" class="dashboard-card">
                <span>➕</span>
                <h3>Ajouter un article</h3>
            </a>
            <a href="article_list.php" class="dashboard-card">
                <span>✏️</span>
                <h3>Modifier un article</h3>
            </a>
            <a href="presentation_crud.php" class="dashboard-card">
                <span>🖼️</span>
                <h3>Présentation</h3>
            </a>
            <a href="contacts_crud.php" class="dashboard-card">
                <span>📇</span>
                <h3>Contacts</h3>
            </a>
            <a href="product_list.php" class="dashboard-card">
                <span>🛒</span>
                <h3>Produits</h3>
            </a>
            <a href="partenaires_crud.php" class="dashboard-card">
                <span>🤝</span>
                <h3>Partenaires</h3>
            </a>
            <a href="upload_img.php" class="dashboard-card">
                <span>📤</span>
                <h3>Ajouter une image</h3>
            </a>
            <a href="edit_image_nav.php" class="dashboard-card">
                <span>🖼️</span>
                <h3>Bannière</h3>
            </a>
            <a href="list_img.php" class="dashboard-card">
                <span>🗂️</span>
                <h3>Liste / Supprimer images</h3>
            </a>
        </div>
    </main>

    <?php includeFooter($contact, $partenaires); ?>

    <script src="/js/scroll.js" defer></script>
    <script src="/js/nav_img.js" defer></script>
    <script src="/js/modal_image_background_nav.js" defer></script>
    <script src="/js/menuburger.js" defer></script>
    <script src="/js/modal_gallery.js" defer></script>
    <script src="/js/slide-partenaire.js" defer></script>
</body>

</html>
