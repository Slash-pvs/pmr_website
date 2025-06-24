<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/functions.php';
$pdo = require __DIR__ . '/db.php';

// Récupération des produits depuis la base
$produits = AllProducts($pdo);
// Récupération des données pour la navigation et les partenaires
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="/public/css/style.css">
   <link rel="stylesheet" href="/public/css/nav.css">
   <link rel="stylesheet" href="/public/css/footer.css">
   <link rel="stylesheet" href="/public/css/boutique.css">
   <title>Index</title>
</head>

<body>
   <!-- Nav -->
   <?php safeRequire('nav.php'); ?>
   <!-- Conteneur principal -->
   <div id="mainContent" class="main-content">
      <h1>Boutique</h1>
      <div class="product-grid">
         <?php foreach ($produits as $produit): ?>
            <div class="product-card">
               <picture>
                  <?php if (isset($produit['versions'][1200])): ?>
                     <source srcset="/img/<?= htmlspecialchars($produit['versions'][1200]) ?>" media="(min-width: 1024px)">
                  <?php endif; ?>

                  <?php if (isset($produit['versions'][768])): ?>
                     <source srcset="/img/<?= htmlspecialchars($produit['versions'][768]) ?>" media="(min-width: 600px)">
                  <?php endif; ?>

                  <img
                     src="/img/<?= htmlspecialchars($produit['versions'][320] ?? $produit['versions'][768] ?? $produit['versions'][1200]) ?>"
                     alt="<?= htmlspecialchars($produit['nom']) ?>" style="width: 100%; height: auto;" loading="lazy">
               </picture>

               <h3><?= htmlspecialchars($produit['nom']) ?></h3>
               <p>Catégorie : <?= htmlspecialchars($produit['categorie']) ?></p>
               <p>Prix : <?= number_format($produit['prix'], 2) ?> €</p>

               <?php if (!is_null($produit['stock'])): ?>
                  <p>Stock : <?= (int) $produit['stock'] ?></p>
               <?php else: ?>
                  <p>Stock : N.C.</p>
               <?php endif; ?>

               <?php if (!is_null($produit['stock']) && $produit['stock'] == 0): ?>
                  <p class="out-of-stock">Rupture de stock</p>
               <?php else: ?>
                  <form class="form-ajout-panier" data-id="<?= $produit['id'] ?>" method="post">
                     <input type="hidden" name="id" value="<?= $produit['id'] ?>">
                     <input type="hidden" name="nom" value="<?= htmlspecialchars($produit['nom']) ?>">
                     <input type="hidden" name="prix" value="<?= $produit['prix'] ?>">

                     <label for="quantite-<?= $produit['id'] ?>">Quantité :</label>
                     <input type="number" id="quantite-<?= $produit['id'] ?>" name="quantite" value="1" min="1" required>

                     <button type="submit">Ajouter au panier</button>
                  </form>
               <?php endif; ?>
            </div>
         <?php endforeach; ?>
      </div>
   </div>
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
   <script src="/public/js/panier.js" type="module" defer></script>
</body>

</html>