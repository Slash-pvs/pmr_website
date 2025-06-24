<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once './db.php';
require_once './functions.php';

if (!isset($_SESSION['user_id'])) {
    die("Accès non autorisé.");
}

$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Liste des articles</title>
    <link rel="stylesheet" href="/public/css/style.css" />
    <link rel="stylesheet" href="/public/css/nav.css" />
    <link rel="stylesheet" href="/public/css/footer.css" />
</head>

<body>
    <?php safeRequire('nav.php'); ?>

    <main class="main-content">
        <h1>enregistre une nouvelle image</h1>

        <form action="/includes/upload_img_webp_function.php" method="post" enctype="multipart/form-data">
            <label for="image">Choisir une image :</label>
            <input type="file" name="image" id="image" accept="image/*" required>

            <label for="category">Choisir une catégorie :</label>
            <select name="category" id="category" required>
                <option value="">-- Sélectionner --</option>
                <option value="boutique">Boutique</option>
                <option value="image_nav">Image Nav</option>
                <option value="partenaire">Partenaire</option>
                <option value="gallery">Gallery</option>
            </select>

            <button type="submit">Uploader</button>
        </form>

    </main>

    <?php includeFooter($contact, $partenaires); ?>

    <!-- Scripts -->
    <script src="/public/js/scroll.js" defer></script>
    <script src="/public/js/nav_img.js" defer></script>
    <script src="/public/js/modal_image_background_nav.js" defer></script>
    <script src="/public/js/menuburger.js" defer></script>
    <script src="/public/js/modal_gallery.js" defer></script>
    <script src="/public/js/slide-partenaire.js" defer></script>
</body>

</html>