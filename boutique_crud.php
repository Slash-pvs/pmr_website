<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once './db.php';
require_once './functions.php';
require_once './includes/boutique_crud_functions.php';

if (!isset($_SESSION['user_id'])) die("Accès non autorisé.");

$userId = $_SESSION['user_id'];
$errors = [];
$productVersions = [];
$success = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Données annexes
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
$partenaires = enrichPartnersWithVersions($pdo, $partenaires);
$categories = getAllProductCategories($pdo);
$availableImages = getAvailableImages(); // Retourne tableau par format ['320'=>[], '768'=>[], '1200'=>[]]

if ($id > 0) {
    $product = getProductById($pdo, $id);
    $productVersions = getProductVersionsByProductId($pdo, $id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrfToken) {
        die("Token CSRF invalide.");
    }

    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $description = trim(filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING));
    $category = trim(filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING));
    $versions = $_POST['versions'] ?? [];
    $formats = ['320','768','1200'];

    if ($name === '') $errors[] = "Le nom du produit est requis.";
    if ($category === '') $errors[] = "La catégorie est requise.";

    foreach ($formats as $format) {
        if (empty($versions[$format])) {
            $errors[] = "L'image pour le format $format est requise.";
        } else {
            $imgName = basename($versions[$format]);
            if (!in_array($imgName, $availableImages[$format] ?? [])) {
                $errors[] = "Image invalide pour le format $format.";
            } else {
                $versions[$format] = $imgName;
            }
        }
    }

    if (empty($errors)) {
        if ($id > 0) {
            if (updateProduct($pdo, $id, $name, $description, $category)) {
                foreach ($versions as $format => $imgName) {
                    updateOrCreateProductVersion($pdo, $id, $format, $imgName);
                }
                $success = "Produit modifié avec succès.";
                $product = getProductById($pdo, $id);
                $productVersions = getProductVersionsByProductId($pdo, $id);
            } else $errors[] = "Erreur lors de la modification du produit.";
        } else {
            $newId = createProduct($pdo, $name, $description, $category, $userId);
            if ($newId) {
                foreach ($versions as $format => $imgName) {
                    createProductVersion($pdo, $newId, $format, $imgName);
                }
                $success = "Produit créé avec succès.";
                $name = $description = $category = '';
                $productVersions = [];
            } else $errors[] = "Erreur lors de la création du produit.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $id>0 ? "Modifier un produit" : "Créer un produit" ?></title>
<link rel="stylesheet" href="/css/style.css">
<link rel="stylesheet" href="/css/nav.css">
<link rel="stylesheet" href="/css/footer.css">
</head>
<body>
<?php safeRequire('nav.php'); ?>
<main class="main-content">
    <h1><?= $id>0 ? "Modifier un produit" : "Créer un produit" ?></h1>

    <?php if ($errors): ?>
        <div style="color:red;"><ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <div>
            <label for="name">Nom du produit :</label><br>
            <input type="text" id="name" name="name" required
                value="<?= htmlspecialchars($product['name'] ?? $name ?? '') ?>">
        </div>

        <div>
            <label for="description">Description :</label><br>
            <textarea id="description" name="description" rows="4"><?= htmlspecialchars($product['description'] ?? $description ?? '') ?></textarea>
        </div>

        <div>
            <label for="category">Catégorie :</label><br>
            <select id="category" name="category" required>
                <option value="">-- Choisir une catégorie --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= (isset($product['category']) && $product['category']==$cat)?'selected':'' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <fieldset>
            <legend>Versions du produit (formats)</legend>
            <?php foreach (['320','768','1200'] as $format): 
                $selectedImage = $versions[$format] ?? $productVersions[$format] ?? '';
            ?>
            <div>
                <label for="version_<?= $format ?>">Image <?= $format ?> px :</label><br>
                <select name="versions[<?= $format ?>]" id="version_<?= $format ?>" required>
                    <option value="">-- Choisir une image --</option>
                    <?php foreach ($availableImages[$format] as $imgName): ?>
                        <option value="<?= htmlspecialchars($imgName) ?>" <?= ($selectedImage === $imgName)?'selected':'' ?>>
                            <?= htmlspecialchars($imgName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="apply-to-all" data-format="<?= $format ?>" style="margin-left:10px;">🔁 Appliquer à tous les formats</button>
                <br>
                <img id="preview_<?= $format ?>" src="<?= $selectedImage ? "/img/boutique/x$format/$selectedImage" : '' ?>" style="max-width:150px; margin-top:5px; display:<?= $selectedImage ? 'inline-block' : 'none' ?>;">
            </div>
            <?php endforeach; ?>
        </fieldset>

        <div style="margin-top:10px;">
            <button type="submit"><?= $id>0 ? "Modifier" : "Créer" ?></button>
        </div>
    </form>

    <?php if ($id>0): ?>
        <form method="POST" action="/includes/product_delete.php" onsubmit="return confirm('Confirmer la suppression ?');" style="margin-top:20px;">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <button type="submit" style="background:red;color:#fff;padding:8px 12px;border:none;border-radius:4px;cursor:pointer;">🗑️ Supprimer ce produit</button>
        </form>
    <?php endif; ?>

    <p><a href="product_list.php">← Retour à la liste des produits</a></p>
</main>

<?php includeFooter($contact, $partenaires); ?>

<script src="/js/scroll.js" defer></script>
<script src="/js/nav_img.js" defer></script>
<script src="/js/modal_image_background_nav.js" defer></script>
<script src="/js/menuburger.js" defer></script>
<script src="/js/modal_gallery.js" defer></script>
<script src="/js/slide-partenaire.js" defer></script>
<script src="/js/apercu_img_boutique_crud.js" defer></script>