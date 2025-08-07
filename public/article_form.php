<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once './db.php';
require_once './includes/article_functions.php';
require_once './functions.php';

if (!isset($_SESSION['user_id'])) {
    die("Accès non autorisé.");
}

$userId = $_SESSION['user_id'];
$errors = [];
$success = '';
$article = null;
$imagePath = $imagePath ?? '';
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$availableImages = getAvailableImages();
$categories = getAllCategories($pdo);

if ($id === 0 && isset($_GET['image'])) {
    $imgName = basename($_GET['image']);
    $fullPath = '/img/gallery/' . $imgName;
    if (in_array($fullPath, $availableImages)) {
        $imagePath = $fullPath;
    }
}

if ($id > 0) {
    $article = getArticleById($pdo, $id);
    if (!$article) {
        die("Article introuvable.");
    }
    $imagePath = $article['image_path'];
} elseif (isset($_GET['image'])) {
    $imgName = basename($_GET['image']);
    $fullPath = '/img/gallery/' . $imgName;
    if (in_array($fullPath, $availableImages)) {
        $imagePath = $fullPath;
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrfToken) {
        die("Token CSRF invalide.");
    }

    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if (!empty($_POST['category_new']) && ($_POST['category_select'] ?? '') === '__new__') {
        $category = trim($_POST['category_new']);
    } else {
        $category = trim($_POST['category_select'] ?? '');
    }

    $imageName = basename(trim($_POST['image_path'] ?? ''));
    $imagePath = '/img/gallery/' . $imageName;

    $errors = [];
    if ($title === '') {
        $errors[] = "Le titre est requis.";
    }
    if ($content === '') {
        $errors[] = "Le contenu est requis.";
    }
    if ($category === '') {
        $errors[] = "La catégorie est requise.";
    }
    if ($imageName === '') {
        $errors[] = "L'image est requise.";
    }

    // Ici on **ne fait plus la validation** de l'image dans $availableImages
    // Suppression de la validation "Image invalide sélectionnée."

    if (empty($errors)) {
        if ($id > 0) {
            if (updateArticle($pdo, $id, $title, $content, $category, $imagePath)) {
                $success = "Article modifié avec succès.";
                $article = getArticleById($pdo, $id);
            } else {
                $errors[] = "Erreur lors de la modification.";
            }
        } else {
            if (createArticle($pdo, $title, $content, $category, $userId, $imagePath)) {
                $success = "Article créé avec succès.";
                $title = $content = $category = $imagePath = '';
            } else {
                $errors[] = "Erreur lors de la création.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id > 0 ? "Modifier un article" : "Créer un article" ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/nav.css">
    <link rel="stylesheet" href="/public/css/footer.css">
</head>

<body>
    <?php safeRequire('nav.php'); ?>
    <main class="main-content">
        <h1><?= $id > 0 ? "Modifier un article" : "Créer un article" ?></h1>

        <?php if (!empty($errors)): ?>
            <div style="color:red;">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <p style="color:green;"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>" />
            <div>
                <label for="title">Titre :</label><br>
                <input type="text" id="title" name="title" required
                    value="<?= htmlspecialchars($article['title'] ?? $title ?? '') ?>" />
            </div>
            <div>
                <label for="content">Contenu :</label><br>
                <textarea id="content" name="content" rows="6"
                    required><?= htmlspecialchars($article['content'] ?? $content ?? '') ?></textarea>
            </div>
            <label for="category_select">Catégorie :</label>
            <select name="category_select" id="category_select" required onchange="handleCategoryChange()">
                <option value="">-- Choisir une catégorie --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= (isset($article['category']) && $article['category'] === $cat) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat) ?>
                    </option>
                <?php endforeach; ?>
                <option value="__new__">➕ Ajouter une nouvelle catégorie</option>
            </select>

            <div id="new_category_container" style="display:none; margin-top:8px;">
                <label for="category_new">Nouvelle catégorie :</label>
                <input type="text" name="category_new" id="category_new" placeholder="Entrez une nouvelle catégorie" />
            </div>

            <div>
                <label for="image_path">Image :</label><br>
                <select id="image_path" name="image_path" required>
                    <option value="">-- Choisir une image --</option>
                    <?php foreach ($availableImages as $img):
                        $imgName = basename($img);
                        $selected = (basename($imagePath) === $imgName || basename($article['image_path'] ?? '') === $imgName) ? 'selected' : '';
                        ?>
                        <option value="<?= htmlspecialchars($imgName) ?>" <?= $selected ?>>
                            <?= htmlspecialchars($imgName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php
                $previewImage = '';
                if (!empty($imagePath)) {
                    $previewImage = $imagePath;
                } elseif (!empty($article['image_path'])) {
                    $previewImage = $article['image_path'];
                }
                ?>
                <?php if ($previewImage): ?>
                    <br><img src="<?= htmlspecialchars($previewImage) ?>" style="max-width:200px; margin-top:10px;">
                <?php endif; ?>
            </div>

            <div style="margin-top:10px;">
                <button type="submit"><?= $id > 0 ? "Modifier" : "Créer" ?></button>
            </div>
        </form>

        <?php if ($id > 0): ?>
            <form method="POST" action="/includes/article_delete.php"
                onsubmit="return confirm('❗ Confirmer la suppression de cet article ?');" style="margin-top: 20px;">

                <input type="hidden" name="id" value="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" />
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />

                <button type="submit"
                    style="background-color: red; color: white; padding: 8px 12px; border: none; border-radius: 4px; cursor: pointer;">
                    🗑️ Supprimer cet article
                </button>
            </form>
        <?php endif; ?>

        <p><a href="article_list.php">← Retour à la liste des articles</a></p>
    </main>

    <?php includeFooter($contact, $partenaires); ?>

    <script src="/public/js/rewrite_url.js" defer></script>
    <script src="/public/js/scroll.js" defer></script>
    <script src="/public/js/nav_img.js" defer></script>
    <script src="/public/js/modal_image_background_nav.js" defer></script>
    <script src="/public/js/menuburger.js" defer></script>
    <script src="/public/js/modal_gallery.js" defer></script>
    <script src="/public/js/slide-partenaire.js" defer></script>
</body>

</html>