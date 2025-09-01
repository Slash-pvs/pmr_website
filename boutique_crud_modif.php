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
$success = '';
$csrfToken = $_SESSION['csrf_token'] ?? $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) die("Produit non spécifié.");

// Données annexes
$contact = getContactInfo($pdo);
$partenaires = enrichPartnersWithVersions($pdo, getAllPartners($pdo));
$categories = getAllProductCategories($pdo);
$formats = ['320','768','1200'];
$availableImages = [];

foreach ($formats as $format) {
    $dir = __DIR__ . "/img/boutique/x$format";
    $availableImages[$format] = is_dir($dir) ? array_values(array_diff(scandir($dir), ['.', '..'])) : [];
}

// Récupération du produit
$product = getProductById($pdo, $id);
if (!$product) die("Produit introuvable.");
$productVersions = getProductVersionsByProductId($pdo, $id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrfToken) die("Token CSRF invalide.");

    $name = trim(filter_input(INPUT_POST,'name',FILTER_SANITIZE_STRING));
    $description = trim(filter_input(INPUT_POST,'description',FILTER_SANITIZE_STRING));
    $category = trim(filter_input(INPUT_POST,'category',FILTER_SANITIZE_STRING));
    $versions = $_POST['versions'] ?? [];

    if (!$name) $errors[] = "Le nom du produit est requis.";
    if (!$category) $errors[] = "La catégorie est requise.";

    foreach ($formats as $format) {
        if (empty($versions[$format])) {
            $errors[] = "L'image pour le format $format est requise.";
        } elseif (!in_array($versions[$format], $availableImages[$format])) {
            $errors[] = "Image invalide pour le format $format.";
        }
    }

    if (empty($errors)) {
        if (updateProduct($pdo,$id,$name,$description,$category)) {
            foreach ($formats as $format) {
                $imagePath = formatImagePath($versions[$format]);
                updateOrCreateProductVersion($pdo,$id,$format,$imagePath);
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un produit</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/nav.css">
    <link rel="stylesheet" href="/css/footer.css">
</head>
<body>
<?php safeRequire('nav.php'); ?>
<main class="main-content">
    <h1>Modifier un produit</h1>

    <?php if($errors): ?>
        <div class="error-messages">
            <ul>
                <?php foreach($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="success-message"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

        <label for="name">Nom du produit :</label><br>
        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($product['nom'] ?? '') ?>">

        <label for="description">Description :</label><br>
        <textarea id="description" name="description" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>

        <label for="category">Catégorie :</label><br>
        <select id="category" name="category" required>
            <option value="">-- Choisir une catégorie --</option>
            <?php foreach($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>" <?= (($product['categorie'] ?? '') === $cat) ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
            <?php endforeach; ?>
        </select>

        <fieldset>
            <legend>Versions du produit (formats)</legend>
            <?php foreach($formats as $format): ?>
                <div style="margin-bottom:1em;">
                    <label for="version_<?= $format ?>">Image <?= $format ?> px :</label><br>
                    <select name="versions[<?= $format ?>]" id="version_<?= $format ?>" required>
                        <option value="">-- Choisir une image dans x<?= $format ?> --</option>
                        <?php foreach($availableImages[$format] as $img): ?>
                            <option value="<?= htmlspecialchars($img) ?>" <?= (isset($productVersions[$format]) && $productVersions[$format]===$img) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($img) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="apply-to-all" data-format="<?= $format ?>">🔁 Appliquer à tous</button>
                    <br>
                    <img id="preview_<?= $format ?>" 
                         src="<?= isset($productVersions[$format]) ? "/img/boutique/x$format/" . htmlspecialchars($productVersions[$format]) : '' ?>" 
                         alt="Preview <?= $format ?>" 
                         style="max-width:150px; margin-top:5px; display:<?= isset($productVersions[$format]) ? 'inline-block' : 'none' ?>;">
                </div>
            <?php endforeach; ?>
        </fieldset>

        <button type="submit" style="margin-top:10px;">Modifier</button>
    </form>

    <p><a href="product_list.php">← Retour à la liste des produits</a></p>
</main>

<?php includeFooter($contact,$partenaires); ?>

<script src="/js/scroll.js" defer></script>
<script src="/js/nav_img.js" defer></script>
<script src="/js/modal_image_background_nav.js" defer></script>
<script src="/js/menuburger.js" defer></script>
<script src="/js/modal_gallery.js" defer></script>
<script src="/js/slide-partenaire.js" defer></script>
<script src="/js/chargement_edit_versions_image_produit.js" defer></script>
</body>
</html>
