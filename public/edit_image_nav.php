<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once __DIR__ . '/functions.php';
$pdo = require __DIR__ . '/db.php';

// Vérifie si l'utilisateur est connecté
if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Récupère uniquement les images originales dans /img/image_nav/ (pas les sous-dossiers)
function getAvailableImages(): array
{
    $baseDir = '/home/fvhvyig/www/public/img/image_nav/';
    $excludeDirs = ['x320', 'x768', 'x1200'];
    $images = [];

    if (is_dir($baseDir)) {
        $files = scandir($baseDir);
        foreach ($files as $file) {
            $fullPath = $baseDir . $file;

            // Ignorer les sous-dossiers x320, x768, x1200
            if (is_dir($fullPath) && in_array($file, $excludeDirs)) {
                continue;
            }

            // Ajouter uniquement les fichiers .webp
            if (is_file($fullPath) && preg_match('/\.webp$/i', $file)) {
                $images[] = '/img/image_nav/' . $file;
            }
        }
    }

    return $images;
}

// Récupération des données
$userInfo = getUserInfo($pdo);
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);

$id = $_GET['id'] ?? 1;
$availableImages = getAvailableImages();

// Récupération des données de l'image_nav
$imageNavSql = "SELECT id, image_path FROM image_nav WHERE id = :id;";
$stmt = $pdo->prepare($imageNavSql);
$stmt->execute(['id' => $id]);
$imageNav = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupération des versions de l'image
$versionsSql = "SELECT * FROM image_nav_versions WHERE image_nav_id = :id";
$stmt = $pdo->prepare($versionsSql);
$stmt->execute(['id' => $id]);
$versions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Génère dynamiquement les versions si elles n'existent pas en base
if (empty($versions) && !empty($imageNav['image_path'])) {
    $formats = [
        'x320' => 320,
        'x768' => 768,
        'x1200' => 1200
    ];
    $imageFilename = basename($imageNav['image_path']);

    foreach ($formats as $format => $size) {
        $versions[] = [
            'id' => null,
            'format' => $format,
            'size' => $size,
            'path' => "/img/image_nav/$format/$imageFilename"
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id > 0 ? "Modifier l'image de navigation" : "Créer l'image de navigation" ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/nav.css">
    <link rel="stylesheet" href="/public/css/footer.css">
</head>

<body>
    <?php safeRequire('nav.php'); ?>

    <main class="main-content">
        <h2><?= $id > 0 ? "Modifier l'image de navigation" : "Créer une image de navigation" ?></h2>
        <form action="/includes/update_image_nav.php" method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($imageNav['id'] ?? '') ?>">

            <div>
                <label for="image_path">Image originale :</label><br>
                <select id="image_path" name="image_path" required>
                    <option value="">-- Choisir une image --</option>
                    <?php foreach ($availableImages as $img):
                        $imgName = basename($img);
                        $currentImage = basename($imageNav['image_path'] ?? '');
                        $selected = ($currentImage === $imgName) ? 'selected' : '';
                        ?>
                        <option value="<?= htmlspecialchars($imgName) ?>" <?= $selected ?>>
                            <?= htmlspecialchars($imgName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php
                $previewImage = '';
                if (!empty($imageNav['image_path'])) {
                    $previewImage = '/img/image_nav/' . basename($imageNav['image_path']);
                }
                ?>
                <?php if ($previewImage): ?>
                    <br>
                    <img id="previewImage" src="<?= htmlspecialchars($previewImage) ?>"
                        style="max-width:200px; margin-top:10px; <?= $previewImage ? '' : 'display:none;' ?>">
                <?php endif; ?>
            </div>

            <h3>Versions (formats)</h3>
            <?php foreach ($versions as $i => $version): ?>
                <?php if ($version['id']): ?>
                    <input type="hidden" name="versions[<?= $i ?>][id]" value="<?= $version['id'] ?>">
                <?php endif; ?>
                Format: <input type="text" name="versions[<?= $i ?>][format]"
                    value="<?= htmlspecialchars($version['format']) ?>" readonly>
                Taille: <input type="number" name="versions[<?= $i ?>][size]"
                    value="<?= htmlspecialchars($version['size']) ?>" readonly>
                Chemin: <input type="text" name="versions[<?= $i ?>][path]"
                    value="<?= htmlspecialchars($version['path']) ?>" readonly class="version-path"><br><br>
            <?php endforeach; ?>

            <div style="margin-top:10px;">
                <button type="submit"><?= $id > 0 ? "Modifier" : "Créer" ?></button>
            </div>
        </form>
    </main>

    <?php includeFooter($contact ?? null, $partenaires ?? null); ?>

    <script src="/public/js/scroll.js" defer></script>
    <script src="/public/js/nav_img.js" defer></script>
    <script src="/public/js/modal_image_background_nav.js" defer></script>
    <script src="/public/js/menuburger.js" defer></script>
    <script src="/public/js/modal_gallery.js" defer></script>
    <script src="/public/js/slide-partenaire.js" defer></script>
    <script src="/public/js/chargement_edit_versions_image_nav.js" defer></script>
</body>

</html>