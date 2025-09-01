<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupération des données pour le footer et la navigation
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
$partenaires = enrichPartnersWithVersions($pdo, $partenaires);
$descriptionClub = getTeamDescription($pdo, 'Club');
// Récupérer les images selon la catégorie
$tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';
// Validation stricte du tag : lettres, chiffres, tirets et underscores uniquement
if ($tag !== '' && !preg_match('/^[a-zA-Z0-9_-]+$/', $tag)) {
    $tag = ''; // valeur par défaut si invalide
}
$images = ($tag === '') ? getAllImages($pdo) : getImagesByCategory($pdo, $tag);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/nav.css">
    <link rel="stylesheet" href="/css/footer.css">
    <title>Accueil</title>
</head>

<body>
    <!-- Nav -->
    <?php safeRequire('nav.php', ['pdo' => $pdo]); ?>

    <!-- Conteneur principal -->
    <main id="mainContent" class="main-content">
        <h1>Bienvenue sur le site du Club Pays Médoc Rugby</h1>
        <p><?= nl2br(htmlspecialchars($descriptionClub)) ?></p>

        <div class="competitions_ffr_widget" data-competition-id="21974" data-club-id="1514" data-is-grouping="false">
        </div>

        <div class="gallerie">
            <?php foreach ($images as $img): ?>
                <div class="gallery-item">
                    <picture>
                        <?php if (isset($img['versions']['320'])): ?>
                            <source srcset="<?= htmlspecialchars($img['versions']['320']) ?>" media="(max-width: 480px)">
                        <?php endif; ?>
                        <?php if (isset($img['versions']['768'])): ?>
                            <source srcset="<?= htmlspecialchars($img['versions']['768']) ?>" media="(max-width: 991px)">
                        <?php endif; ?>
                        <?php if (isset($img['versions']['1200'])): ?>
                            <source srcset="<?= htmlspecialchars($img['versions']['1200']) ?>" media="(min-width: 992px)">
                        <?php endif; ?>
                        <img class="gallerie-img" src="<?= htmlspecialchars($img['original']) ?>"
                            alt="Image catégorie <?= htmlspecialchars($img['category']) ?>" loading="lazy">
                    </picture>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php includeFooter($contact, $partenaires); ?>

    <!-- Ajout de la variable images dans un attribut data -->
    <div id="myDiv" data-images='<?= htmlspecialchars(json_encode($images), ENT_QUOTES, "UTF-8") ?>'></div>

    <!-- Scripts -->
    <script src="/js/scroll.js" defer></script>
    <script src="/js/nav_img.js" defer></script>
    <script src="/js/modal_image_background_nav.js" defer></script>
    <script src="/js/menuburger.js" defer></script>
    <script src="/js/modal_gallery.js" defer></script>
    <script src="/js/slide-partenaire.js" defer></script>
    <script src="/js/widget-ffr.js" defer></script>
</body>

</html>