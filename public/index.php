<?php
// Affichage d'erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Inclusion des fichiers nécessaires
require_once __DIR__ . '/functions.php';
$pdo = require __DIR__ . '/db.php';
// Récupérer les images selon la catégorie
$tag = isset($_GET['tag']) ? trim($_GET['tag']) : '';
$images = ($tag === '') ? getAllImages($pdo) : getImagesByCategory($pdo, $tag);

if (empty($images)) {
   echo "<p>Aucune image n'a été trouvée.</p>";
}

// Récupération des données pour la navigation et les partenaires
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
$descriptionClub = getTeamDescription($pdo, 'Club');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="/public/css/style.css">
   <link rel="stylesheet" href="/public/css/nav.css">
   <link rel="stylesheet" href="/public/css/footer.css">
   <title>Index</title>
</head>

<body>
   <!-- Nav -->
   <?php safeRequire('nav.php'); ?>

   <!-- Conteneur principal -->
   <main id="mainContent" class="main-content">
      <h1>Bienvenu sur le site du CLub Pays Médoc Rugby </h1>
      <p><?= nl2br(htmlspecialchars($descriptionClub)) ?></p>
      <div class="competitions_ffr_widget" data-competition-id="21974" data-club-id="1514" data-is-grouping="false">
      </div>
    <div class="gallerie">
<?php foreach ($images as $img): ?>
    <div class="gallery-item">
        <picture>
            <!-- Version mobile (max-width: 480px) -->
            <?php if (isset($img['versions']['320'])): ?>
                <source 
                    srcset="<?= htmlspecialchars($img['versions']['320']) ?>" 
                    media="(max-width: 480px)"
                >
            <?php endif; ?>

            <!-- Version tablette (481px à 991px) -->
            <?php if (isset($img['versions']['768'])): ?>
                <source 
                    srcset="<?= htmlspecialchars($img['versions']['768']) ?>" 
                    media="(max-width: 991px)"
                >
            <?php endif; ?>

            <!-- Version desktop (au-delà de 991px) -->
            <?php if (isset($img['versions']['1200'])): ?>
                <source 
                    srcset="<?= htmlspecialchars($img['versions']['1200']) ?>" 
                    media="(min-width: 992px)"
                >
            <?php endif; ?>

            <!-- Fallback : image originale -->
            <img 
                class="gallerie-img" 
                src="<?= htmlspecialchars($img['original']) ?>" 
                alt="Image catégorie <?= htmlspecialchars($img['category']) ?>" 
                loading="lazy"
            >
        </picture>
    </div>
   
<?php endforeach; ?>
</div>
   </main>
   <!-- Footer -->
   <?php
   // Récupération de l'image de navigation
   includeFooter($contact, $partenaires);
   ?>

   <!-- Ajout de la variable images dans un attribut data -->
   <div id="myDiv" data-images='<?= htmlspecialchars(json_encode($images), ENT_QUOTES, "UTF-8") ?>'></div>

   <!-- Scripts -->
   <script src="/public/js/scroll.js" defer></script>
   <script src="/public/js/nav_img.js" defer></script>
   <script src="/public/js/modal_image_background_nav.js" defer></script>
   <script src="/public/js/menuburger.js" defer></script>
   <script src="/public/js/modal_gallery.js" defer></script>
   <script src="/public/js/slide-partenaire.js" defer></script>
   <script src="/public/js/widget-ffr.js" defer></script>
</body>

</html>