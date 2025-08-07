<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/functions.php';
$pdo = require __DIR__ . '/db.php';

// Récupérer uniquement les images de la catégorie "senior"
$images = getImagesByCategory($pdo, 'senior');

// Récupération des données pour la navigation et les partenaires
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
// Récupérer la description de l'équipe Senior
$teamDescription = getTeamDescription($pdo, 'Senior');
?>

<!DOCTYPE html>

<html lang="fr">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="/public/css/style.css">
   <link rel="stylesheet" href="/public/css/nav.css">
   <link rel="stylesheet" href="/public/css/footer.css">
   <title>Senior</title>
</head>

<body>
   <!-- Nav -->
   <?php safeRequire('nav.php'); ?>

   <!-- Conteneur principal -->
   <div id="mainContent" class="main-content">
      <h1>Senior</h1>
      <p>
         <?= !empty($teamDescription) ? nl2br(htmlspecialchars($teamDescription)) : 'Aucune description trouvée pour l\'équipe Senior.'; ?>
      </p>
      <h2>Equipe A</h2>
      <div class="competitions_ffr_widget" data-competition-id="21974" data-club-id="1514" data-is-grouping="false">
      </div>

      <h2>Equipe B</h2>
      <div class="competitions_ffr_widget" data-competition-id="21982" data-club-id="1514" data-is-grouping="false">
      </div>

      <h2>Galerie des équipes Senior</h2>
      <div class="gallerie">
         <?php if (count($images) > 0): ?>
            <?php foreach ($images as $image): ?>
               <div>
                  <picture>
                     <source srcset="<?= htmlspecialchars($image['versions']['1200']); ?>" media="(min-width: 1200px)"
                        type="image/webp">
                     <source srcset="<?= htmlspecialchars($image['versions']['768']); ?>" media="(min-width: 768px)"
                        type="image/webp">
                     <source srcset="<?= htmlspecialchars($image['versions']['320']); ?>" media="(min-width: 320px)"
                        type="image/webp">
                     <img class="gallerie-img" src="<?= htmlspecialchars($image['original']); ?>" alt="Image">
                  </picture>
               </div>
            <?php endforeach; ?>
         <?php else: ?>
            <p>Aucune image trouvée pour cette catégorie.</p>
         <?php endif; ?>
      </div>


      <!-- Footer -->
      <?php
      includeFooter($contact, $partenaires);
      ?>

      <!-- Ajout de la variable images dans un attribut data -->
      <div id="myDiv" data-images='<?= htmlspecialchars(json_encode($images), ENT_QUOTES, "UTF-8") ?>'></div>

      <!-- Scripts -->
      <script src="/public/js/rewrite_url.js" defer></script>
      <script src="/public/js/scroll.js" defer></script>
      <script src="/public/js/nav_img.js" defer></script>
      <script src="/public/js/menuburger.js" defer></script>
      <script src="/public/js/modal_gallery.js" defer></script>
      <script src="/public/js/slide-partenaire.js" defer></script>
      <script src="/public/js/widget-ffr.js" defer></script>
</body>

</html>