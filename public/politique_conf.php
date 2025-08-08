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
  <link rel="stylesheet" href="/public/css/rgpd.css">
  <title>Politique de confidentialité</title>
</head>

<body>
  <!-- Nav -->
  <?php safeRequire('nav.php'); ?>
  <!-- Conteneur principal -->
  <section class="main-content">
    <h1>🔒 Politique de confidentialité & RGPD</h1>
    <p><strong>Dernière mise à jour :</strong> 22 avril 2025</p>

    <h2>1. Responsable du traitement</h2>
    <p>Le présent site est édité par :</p>
    <ul>
      <li>Nom / Raison sociale : Pays Médoc rugby</li>
      <li>Adresse : Rue Du Maréchal Joffre 33250 Pauillac</li>
      <li>Email : pays.medoc.rugby@orange.fr</li>
      <li>Téléphone : +33556591027</li>
    </ul>
    <p>Le responsable du traitement est joignable via le formulaire de contact du site.</p>

    <h2>2. Données collectées</h2>
    <p>Les données personnelles collectées via ce site peuvent inclure :</p>
    <ul>
      <li>Nom, prénom</li>
      <li>Adresse e-mail</li>
      <li>Numéro de téléphone</li>
      <li>Adresse IP</li>
      <li>Données de navigation via cookies</li>
    </ul>

    <h2>3. Finalité des traitements</h2>
    <p>Les données collectées sont utilisées pour :</p>
    <ul>
      <li>Répondre à vos demandes via le formulaire de contact</li>
      <li>Améliorer l’expérience utilisateur</li>
      <li>Réaliser des statistiques de fréquentation</li>
      <li>Gérer les abonnements (si applicable)</li>
    </ul>

    <h2>4. Consentement</h2>
    <p>Avant toute collecte de données, un consentement explicite est demandé à l’utilisateur via un formulaire ou un
      bandeau cookie. Ce consentement peut être retiré à tout moment.</p>

    <h2>5. Durée de conservation</h2>
    <p>Les données sont conservées :</p>
    <ul>
      <li>3 ans pour les contacts inactifs</li>
      <li>13 mois pour les cookies</li>
    </ul>

    <h2>6. Droits des utilisateurs</h2>
    <p>Conformément au RGPD, vous disposez des droits suivants :</p>
    <ul>
      <li>Droit d’accès</li>
      <li>Droit de rectification</li>
      <li>Droit à l’effacement</li>
      <li>Droit à la limitation du traitement</li>
      <li>Droit à la portabilité</li>
      <li>Droit d’opposition</li>
    </ul>
    <p>Pour exercer ces droits, contactez : [votre mail]</p>

    <h2>7. Cookies</h2>
    <p>Des cookies sont utilisés pour :</p>
    <ul>
      <li>Assurer le bon fonctionnement du site</li>
      <li>Mesurer l’audience</li>
    </ul>
    <p>Un bandeau s’affiche lors de votre première visite pour recueillir votre consentement.</p>

    <h2>8. Hébergement</h2>
    <p>Le site est hébergé par :</p>
    <ul>
      <li>Hébergeur : [Nom de l’hébergeur, ex : o2switch, OVH]</li>
      <li>Adresse : [Adresse de l’hébergeur]</li>
    </ul>

    <h2>9. Sécurité</h2>
    <p>Les données personnelles sont hébergées sur des serveurs sécurisés. Le site utilise le protocole HTTPS. Des
      mesures techniques et organisationnelles sont mises en place pour éviter tout accès non autorisé.</p>

    <h1>📄 Mentions légales</h1>

    <h2>Éditeur du site</h2>
    <ul>
      <li>Nom / Raison sociale : Pays Médoc rugby</li>
      <li>Adresse : Rue Du Maréchal Joffre 33250 Pauillac</li>
      <li>Email : pays.medoc.rugby@orange.fr</li>
      <li>Téléphone : +33556591027</li>
    </ul>

    <h2>Directeur de la publication</h2>
    <p>Pierre Vergnes</p>

    <h2>Hébergeur</h2>
    <ul>
      <li>Nom : [Nom de l’hébergeur]</li>
      <li>Rue Du Maréchal Joffre 33250 Pauillac</li>
      <li>Téléphone : +33556591027</li>
    </ul>
  </section>
  <!-- Footer -->
  <?php
  // Récupération de l'image de navigation
  includeFooter($contact, $partenaires);
  ?>
  
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