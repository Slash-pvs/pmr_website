<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/functions.php';
$pdo = require __DIR__ . '/db.php';

// Vérifie si l'utilisateur est connecté, sinon redirige vers la page de connexion
if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit;
}
//fonction getAvailableImages pour récupere les images qui doivent être propose par le select
// Récupère la liste des images disponibles dans /img pour sélectionner
function getAvailableImages(): array {
    $baseDir = '/home/fvhvyig/www/public/img/image_nav/';
    $formats = ['320', '768', '1200'];
    $images = [];

    foreach ($formats as $format) {
        $dir = $baseDir . 'x' . $format . '/';
        $images[$format] = [];

        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if (preg_match('/\.webp$/i', $file)) {
                    // Chemin relatif web pour afficher l'image
                    $images[$format][] = '/img/image_nav/x' . $format . '/' . $file;
                }
            }
        }
    }

    return $images;
}
// Récupère les informations de l'utilisateur connecté
$userInfo = getUserInfo($pdo);
// Récupération des données pour la navigation et les partenaires
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
// Récupération des données actuelles
$id = $_GET['id'] ?? 1;
$availableImages = getAvailableImages();
$imageNavSql = "SELECT * FROM image_nav WHERE id = :id";
$stmt = $pdo->prepare($imageNavSql);
$stmt->execute(['id' => $id]);
$imageNav = $stmt->fetch(PDO::FETCH_ASSOC);

$versionsSql = "SELECT * FROM image_nav_versions WHERE image_nav_id = :id";
$stmt = $pdo->prepare($versionsSql);
$stmt->execute(['id' => $id]);
$versions = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        <h2>Modifier l'image nav</h2>
        <form action="/includes/update_image_nav.php" method="post">
            <input type="hidden" name="id" value="<?= htmlspecialchars($imageNav['id']) ?>">

            <div>
                <label for="image_path">Image :</label><br>
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
                    $previewImage = str_replace('/public', '', $imageNav['image_path']);
                }
                ?>
                <?php if ($previewImage): ?>
                    <br><img src="<?= htmlspecialchars($previewImage) ?>" style="max-width:200px; margin-top:10px;">
                <?php endif; ?>
            </div>

            <h3>Versions</h3>
            <?php foreach ($versions as $i => $version): ?>
                <input type="hidden" name="versions[<?= $i ?>][id]" value="<?= $version['id'] ?>">
                Format: <input type="text" name="versions[<?= $i ?>][format]"
                    value="<?= htmlspecialchars($version['format']) ?>">
                Taille: <input type="number" name="versions[<?= $i ?>][size]"
                    value="<?= htmlspecialchars($version['size']) ?>">
                Chemin: <input type="text" name="versions[<?= $i ?>][path]"
                    value="<?= htmlspecialchars($version['path']) ?>"><br><br>
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
    <script>
        const availableImages = <?= json_encode(array_map('basename', $availableImages)) ?>;
        console.log("Images disponibles :", availableImages);
    </script>
</body>

</html>