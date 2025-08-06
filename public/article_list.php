<?php
session_start();
require_once './db.php';
require_once './includes/article_functions.php';
require_once './functions.php';

if (!isset($_SESSION['user_id'])) {
    die("Accès non autorisé.");
}

$articles = getAllArticles($pdo);
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);

// Indexer les articles par image_path
$articlesByImage = [];
foreach ($articles as $article) {
    $articlesByImage[$article['image_path']][] = $article;
}

// Scanner uniquement le dossier /img/gallery pour les fichiers .webp valides
$galleryDir = __DIR__ . '/img/gallery';
$webImgPath = '/img/gallery';
$imagesInFolder = [];

if (is_dir($galleryDir)) {
    $files = scandir($galleryDir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $filePath = $galleryDir . '/' . $file;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

        // Vérifie que c'est un .webp, et qu'il ne se termine pas par _320.webp ou _768.webp
        if (
            is_file($filePath) &&
            $ext === 'webp' &&
            !preg_match('/(_320|_768)\.webp$/', $file)
        ) {
            $imagesInFolder[] = $file;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Liste des articles</title>
    <link rel="stylesheet" href="/public/css/style.css" />
    <link rel="stylesheet" href="/public/css/nav.css" />
    <link rel="stylesheet" href="/public/css/footer.css" />
</head>
<body>
    <?php safeRequire('nav.php'); ?>

    <main class="main-content">
        <h1>Articles publiés</h1>
        <p><a href="article_form.php">➕ Créer un nouvel article</a></p>

        <?php if (empty($articles)): ?>
            <p>Aucun article trouvé.</p>
        <?php else: ?>
            <table border="1" cellpadding="10" style="border-collapse: collapse; width: 100%;">
                <thead>
                    <tr>
                        <th>ID</th><th>Titre</th><th>Catégorie</th><th>Date</th><th>Image</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td><?= $article['id'] ?></td>
                            <td><?= htmlspecialchars($article['title']) ?></td>
                            <td><?= htmlspecialchars($article['category']) ?></td>
                            <td><?= htmlspecialchars($article['created_at']) ?></td>
                            <td>
                                <?php if (!empty($article['image_path'])): ?>
                                    <img src="<?= $webImgPath . '/' . htmlspecialchars($article['image_path']) ?>" width="80" alt="Image article">
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="article_form.php?id=<?= $article['id'] ?>">✏️ Modifier</a><br>
                                <form method="POST" action="/includes/article_delete.php" onsubmit="return confirm('Supprimer cet article ?');" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $article['id'] ?>" />
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>" />
                                    <button type="submit" style="background:red;color:white;border:none;">🗑️ Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <h2>Images disponibles dans /img/gallery</h2>
        <div style="display:flex; flex-wrap:wrap; gap:15px;">
            <?php foreach ($imagesInFolder as $img): ?>
                <div style="text-align:center; border:1px solid #ccc; padding:10px;">
                    <img src="<?= $webImgPath . '/' . htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($img) ?>" style="width:150px; height:auto;">
                    <p><?= htmlspecialchars($img) ?></p>

                    <?php if (isset($articlesByImage[$img])): ?>
                        <?php foreach ($articlesByImage[$img] as $art): ?>
                            <p>
                                <strong><?= htmlspecialchars($art['title']) ?></strong><br>
                                <a href="article_form.php?id=<?= $art['id'] ?>">Modifier</a>
                            </p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Aucun article lié</p>
                        <a href="article_form.php?image=<?= urlencode($img) ?>">Créer un article</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?php includeFooter($contact, $partenaires); ?>

    <!-- Scripts -->
    <script src="/public/js/scroll.js" defer></script>
    <script src="/public/js/nav_img.js" defer></script>
    <script src="/public/js/modal_image_background_nav.js" defer></script>
    <script src="/public/js/menuburger.js" defer></script>
    <script src="/public/js/modal_gallery.js" defer></script>
    <script src="/public/js/slide-partenaire.js" defer></script>
</body>
</html>
