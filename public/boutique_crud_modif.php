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

$userId = $_SESSION['user_id'];
$errors = [];
$productVersions = [];
$success = '';

// Token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    die("Produit non spécifié.");
}

// Données annexes
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
$categories = getAllProductCategories($pdo);
$availableImages = [];
$formats = ['320', '768', '1200'];

foreach ($formats as $format) {
    $dir = __DIR__ . "/img/boutique/x$format";
    if (is_dir($dir)) {
        $availableImages[$format] = array_values(array_diff(scandir($dir), ['.', '..']));
    } else {
        $availableImages[$format] = [];
    }
}
    

if ($id > 0) {
    $product = getProductById($pdo, $id);
    if (!$product) {
        die("Produit non trouvé.");
    }
}
// Récupération du produit
$product = getProductById($pdo, $id);
if (!$product) {
    die("Produit introuvable.");
}
$productVersions = getProductVersionsByProductId($pdo, $id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrfToken) {
        die("Token CSRF invalide.");
    }

    // Données filtrées
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
    $category = trim(filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING));
    $versions = $_POST['versions'] ?? [];

    $validFormats = ['320', '768', '1200'];

    if ($name === '') {
        $errors[] = "Le nom du produit est requis.";
    }
    if ($category === '') {
        $errors[] = "La catégorie est requise.";
    }

    // Validation des versions d'images
    foreach ($validFormats as $format) {
        if (empty($versions[$format])) {
            $errors[] = "L'image pour le format $format est requise.";
        } else {
            $imgName = basename($versions[$format]);
            $imagePath = formatImagePath($imgName);

            if (!in_array($imagePath, $availableImages[$format])) {
                $errors[] = "Image invalide pour le format $format.";
            } else {
                $versions[$format] = $imgName;
            }
        }
    }

    // Mise à jour en base
    if (empty($errors)) {
        if (updateProduct($pdo, $id, $name, $description, $category)) {
            foreach ($versions as $format => $imageName) {
                $imagePath = formatImagePath($imageName);
                updateOrCreateProductVersion($pdo, $id, $format, $imagePath);
            }
            $success = "Produit modifié avec succès.";
            $product = getProductById($pdo, $id);
            $productVersions = getProductVersionsByProductId($pdo, $id);
        } else {
            $errors[] = "Erreur lors de la modification du produit.";
        }
    }   
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un produit</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/nav.css">
    <link rel="stylesheet" href="/public/css/footer.css">
</head>

<body>
<?php safeRequire('nav.php'); ?>
<main class="main-content">
    <h1>Modifier un produit</h1>

    <form method="POST">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>" />

    <div>
        <label for="name">Nom du produit :</label><br />
        <input type="text" id="name" name="name" required
            value="<?= htmlspecialchars($product['nom'] ?? $name ?? '') ?>" />
    </div>

    <div>
        <label for="description">Description :</label><br />
        <textarea id="description" name="description" rows="4"><?= htmlspecialchars($product['description'] ?? $description ?? '') ?></textarea>
    </div>

    <div>
        <label for="category">Catégorie :</label><br />
        <select id="category" name="category" required>
            <option value="">-- Choisir une catégorie --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"
                    <?= (($product['categorie'] ?? $category ?? '') === $cat) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <fieldset>
        <legend>Versions du produit (formats)</legend>
        <?php
        $formats = ['320', '768', '1200'];
        foreach ($formats as $format):
            $imgDir = __DIR__ . "/img/boutique/x$format";
            $webDir = "/img/boutique/x$format";
            $images = is_dir($imgDir) ? array_diff(scandir($imgDir), ['.', '..']) : [];
        ?>
            <div>
                <label for="version_<?= $format ?>">Image <?= $format ?> px :</label><br />
                <select name="versions[<?= $format ?>]" id="version_<?= $format ?>" required>
                    <option value="">-- Choisir une image dans x<?= $format ?> --</option>
                    <?php foreach ($images as $img): ?>
                        <option value="<?= htmlspecialchars($img) ?>"><?= htmlspecialchars($img) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endforeach; ?>
    </fieldset>

    <div style="margin-top: 10px;">
        <button type="submit">Modifier</button>
    </div>
</form>

    <p><a href="product_list.php">← Retour à la liste des produits</a></p>
</main>


    
<?php 
    includeFooter($contact, $partenaires);
?>

<script src="/public/js/scroll.js" defer></script>
<script src="/public/js/nav_img.js" defer></script>
<script src="/public/js/modal_image_background_nav.js" defer></script>
<script src="/public/js/menuburger.js" defer></script>
<script src="/public/js/modal_gallery.js" defer></script>
<script src="/public/js/slide-partenaire.js" defer></script>
<script src="/public/js/widget-ffr.js" defer></script>
</body>
</html>
