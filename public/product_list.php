<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once './db.php';
require_once './functions.php';
require_once './includes/boutique_crud_functions.php';

if (!isset($_SESSION['user_id'])) {
    die("Accès non autorisé.");
}

// Assure-toi que le token CSRF est présent pour les actions POST
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];
$product = null;
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id > 0) {
    $product = getProductById($pdo, $id);
    if (!$product) {
        die("Produit non trouvé.");
    }
}

// Récupération des produits
$products = getAllProducts($pdo);

// Récupération des données pour la nav et le footer
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id > 0 ? "Modifier un produit" : "Créer un produit" ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/nav.css">
    <link rel="stylesheet" href="/public/css/footer.css">
</head>

<body>
    <?php safeRequire('nav.php'); ?>

   <main class="main-content">
    <h1>Liste des produits</h1>
    <p><a href="/public/boutique_crud.php">+ Ajouter un nouveau produit</a></p>

    <?php if (empty($products)): ?>
        <p>Aucun produit trouvé.</p>
    <?php else: ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $prod): ?>
                    <tr>
                        <td><?= htmlspecialchars($prod['id'] ?? '') ?></td>
                        <td><?= htmlspecialchars($prod['nom'] ?? '') ?></td>
                        <td><?= htmlspecialchars($prod['categorie'] ?? '') ?></td>
                        <td>
                            <a href="/public/boutique_crud_modif.php?id=<?= (int) $prod['id'] ?>">Modifier</a>
                            |
                            <form method="POST" action="/includes/product_delete.php" style="display:inline;"
                                onsubmit="return confirm('Confirmer la suppression ?');">
                                <input type="hidden" name="id" value="<?= (int) $prod['id'] ?>" />
                                <input type="hidden" name="csrf_token"
                                    value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>" />
                                <button type="submit"
                                    style="background:none; border:none; color:red; cursor:pointer;">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <p><a href="/">← Retour à l'accueil</a></p>
</main>


    <?php includeFooter($contact ?? null, $partenaires ?? null); ?>
    <script src="/public/js/scroll.js" defer></script>
    <script src="/public/js/nav_img.js" defer></script>
    <script src="/public/js/modal_image_background_nav.js" defer></script>
    <script src="/public/js/menuburger.js" defer></script>
    <script src="/public/js/modal_gallery.js" defer></script>
    <script src="/public/js/slide-partenaire.js" defer></script>
    <script>
        const availableImages = <?= json_encode(array_map('basename', $availableImages)) ?>;
        console.log("Images disponibles :", availableImages);
    </script>
</body>

</html>