<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/functions.php';  
$pdo = require __DIR__ . '/db.php';
if (empty($_SESSION['csrf_token'])) {
   $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Honeypot : si le champ caché est rempli, c'est un bot
if (!empty($_POST['website'])) {
   // On ignore discrètement sans indiquer d'erreur
   header("Location: /public/contact.php?success=1");
   exit();
}

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
   <link rel="stylesheet" href="/public/css/style.css">
   <link rel="stylesheet" href="/public/css/nav.css">
   <link rel="stylesheet" href="/public/css/footer.css">
   <title>Contact</title>
</head>

<body>
   <!-- Nav -->
   <?php safeRequire('nav.php'); ?>

   <!-- Conteneur principal -->
   <div id="mainContent" class="main-content">
      <h1>Contactez-nous</h1>
      <?php if (isset($_GET['success'])): ?>
         <p class="success-msg">Votre message a bien été envoyé. Merci !</p>
      <?php elseif (isset($_GET['error'])): ?>
         <p class="error-msg">Une erreur est survenue lors de l'envoi. Veuillez réessayer.</p>
      <?php endif; ?>

      <form action="/public/includes/traitement_contact.php" method="post">
         <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

         <!-- Champ Honeypot caché -->
         <div style="display: none;">
            <label for="website"></label>
            <input type="text" id="website" name="website" autocomplete="off">
         </div>

         <label for="nom">Nom :</label><br>
         <input type="text" id="nom" name="nom" required><br><br>

         <label for="email">Email :</label><br>
         <input type="email" id="email" name="email" required><br><br>

         <label for="message">Message :</label><br>
         <textarea id="message" name="message" rows="5" required></textarea><br><br>

         <input type="submit" value="Envoyer">
      </form>

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
</body>

</html>