<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
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
$partenaires = enrichPartnersWithVersions($pdo, $partenaires);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script crossorigin defer src="https://widget.club.ffr.fr/static/js/main.js"></script>
    <link crossorigin rel="stylesheet" href="https://widget.club.ffr.fr/static/css/main.css">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/nav.css">
    <link rel="stylesheet" href="/css/footer.css">
    <title>Actu</title>
</head>

<body>
    <!-- Nav -->
    <?php safeRequire('nav.php'); ?>
    <!-- Conteneur principal -->
    <div id="mainContent" class="main-content">
        <?php
        $posts = getAllPosts($pdo);
        if ($posts): ?>
            <div class="feed">
                <?php foreach ($posts as $row): ?>
                    <article class="post">
                        <h2><?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                        <p><?= nl2br(htmlspecialchars($row['content'], ENT_QUOTES, 'UTF-8')) ?></p>

                        <?php if (!empty($row['image_path'])):
                            $imageBase = pathinfo($row['image_path'], PATHINFO_FILENAME);
                            $imageExt = pathinfo($row['image_path'], PATHINFO_EXTENSION);
                            $imageUrl = htmlspecialchars($row['image_path'], ENT_QUOTES, 'UTF-8');
                            ?>
                            <figure>
                                <img src="<?= $imageUrl ?>" srcset="/img/gallery/<?= $imageBase ?>_320.<?= $imageExt ?> 320w,
                                        /img/gallery/<?= $imageBase ?>_768.<?= $imageExt ?> 768w"
                                    sizes="(max-width: 768px) 100vw, 768px"
                                    alt="<?= htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8') ?>"
                                    style="max-width:100%; height:auto;" loading="lazy">
                                <figcaption>Catégorie : <?= htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8') ?> | Publié le :
                                    <?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></figcaption>
                            </figure>
                        <?php else: ?>
                            <p><small>Catégorie : <?= htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8') ?> | Publié le :
                                    <?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></small></p>
                        <?php endif; ?>

                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Aucun article trouvé.</p>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php
    // Récupération de l'image de navigation
    includeFooter($contact, $partenaires);
    ?>

    <!-- Ajout de la variable images dans un attribut data -->
    <div id="myDiv" data-images='<?= htmlspecialchars(json_encode($images), ENT_QUOTES, "UTF-8") ?>'></div>

    <!-- Scripts -->
    <script src="/js/scroll.js" defer></script>
    <script src="/js/nav_img.js" defer></script>
    <script src="/js/modal_image_background_nav.js" defer></script>
    <script src="/js/menuburger.js" defer></script>
    <script src="/js/modal_gallery.js" defer></script>
    <script src="/js/slide-partenaire.js" defer></script>
</body>

</html>