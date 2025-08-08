<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
handleRequest($pdo);
require_once __DIR__ . '/functions.php';
$pdo = require __DIR__ . '/db.php';

$tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';
$images = ($tag === '') ? getAllImages($pdo) : getImagesByCategory($pdo, $tag);

if (empty($images)) {
    echo "<p>Aucune image n'a été trouvée.</p>";
}
// Récupération des données pour la navigation et les partenaires
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script crossorigin defer src="https://widget.club.ffr.fr/static/js/main.js"></script>
    <link crossorigin rel="stylesheet" href="https://widget.club.ffr.fr/static/css/main.css">
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/nav.css">
    <link rel="stylesheet" href="/public/css/footer.css">
    <title>Ecole de rugby</title>
    <meta name="robots" content="noindex, nofollow"> <!-- empeche l'indexation du site web -->
</head>

<body>
    <!-- Nav -->
    <?php safeRequire('nav.php'); ?>
    <!-- Conteneur principal -->
    <div id="mainContent" class="main-content">
        <?php
        $posts = getPostsByCategory($pdo, 'ecole_rugby');

        if ($posts) {
            echo "<div class='feed'>";
            foreach ($posts as $row) {
                echo "<div class='post'>";
                echo "<h2>" . htmlspecialchars($row['title']) . "</h2>";
                echo "<p>" . nl2br(htmlspecialchars($row['content'])) . "</p>";

                if (!empty($row['image_path'])) {
                    echo "<img src='" . htmlspecialchars($row['image_path']) . "' alt='Image du post' style='max-width: 100%; height: auto;'>";
                }

                echo "<p><small>Catégorie : " . htmlspecialchars($row['category'] ?? '') . " | Publié le : " . htmlspecialchars($row['created_at'] ?? '') . "</small></p>";
                echo "</div><hr>";
            }
            echo "</div>";
        } else {
            echo "<p>Aucun article trouvé.</p>";
        }
        ?>
    </div>

    <!-- Footer -->
    <?php
    // Récupération de l'image de navigation
    includeFooter($contact, $partenaires);
    ?>

    <!-- Ajout de la variable images dans un attribut data -->
   <div id="myDiv" data-images='<?= htmlspecialchars(json_encode($images), ENT_QUOTES, "UTF-8") ?>'></div>


    <!-- Scripts -->
   <script src="/public/js/rewrite_url.js" defer></script>
    <script src="/public/js/scroll.js" defer></script>
    <script src="/public/js/nav_img.js" defer></script>
    <script src="/public/js/modal_image_background_nav.js" defer></script>
    <script src="/public/js/menuburger.js" defer></script>
    <script src="/public/js/modal_gallery.js" defer></script>
    <script src="/public/js/slide-partenaire.js" defer></script>
</body>

</html>