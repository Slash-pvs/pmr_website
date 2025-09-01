<?php
// Affiche les erreurs pour debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mode debug
$debug = true;

// Répertoire réel et URL pour les images accessibles publiquement
$publicImgDir = realpath(__DIR__ . '/../img');     // ← Corrigé ici
$webImgUrl = '/img';                               // ← URL publique correspondante

$categories = ['boutique', 'image_nav', 'partenaire', 'gallery'];
$log = [];
$message = "";
$success = false;
$imagePreviewUrl = "";

function resizeAndConvertToWebP($srcPath, $destPath, $newWidth, &$log) {
    list($width, $height) = getimagesize($srcPath);
    if (!$width || !$height) {
        $log[] = "❌ Erreur : getimagesize a échoué pour $srcPath";
        return false;
    }

    $ratio = $height / $width;
    $newHeight = (int) round($newWidth * $ratio);
    $newWidth = (int) $newWidth;

    $srcImage = imagecreatefromstring(file_get_contents($srcPath));
    if (!$srcImage) {
        $log[] = "❌ Erreur : imagecreatefromstring a échoué.";
        return false;
    }

    $dstImage = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    $success = imagewebp($dstImage, $destPath, 80);
    if ($success) {
        $log[] = "✅ Image redimensionnée et sauvegardée : $destPath";
    } else {
        $log[] = "❌ imagewebp a échoué pour : $destPath";
    }

    imagedestroy($srcImage);
    imagedestroy($dstImage);
    return $success;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'], $_POST['category']) && in_array($_POST['category'], $categories)) {
    $category = $_POST['category'];
    $originalName = pathinfo($_FILES['image']['name'], PATHINFO_FILENAME);
    $tmpPath = $_FILES['image']['tmp_name'];

    $log[] = "📁 Catégorie : $category";
    $log[] = "📂 Nom original : $originalName";
    $log[] = "📄 Fichier temporaire : $tmpPath";

    $uploadDir = "$publicImgDir/$category";
    $webCategoryUrl = "$webImgUrl/$category";

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
        $log[] = "📁 Dossier créé : $uploadDir";
    }

    $originalWebPName = "{$originalName}.webp";
    $originalWebPPath = "$uploadDir/$originalWebPName";
    $image = imagecreatefromstring(file_get_contents($tmpPath));

    if ($image) {
        $successOriginal = imagewebp($image, $originalWebPPath, 90);
        imagedestroy($image);

        if ($successOriginal) {
            $imagePreviewUrl = "$webCategoryUrl/$originalWebPName";
            $log[] = "✅ Image originale convertie en WebP : $originalWebPPath";
        } else {
            $log[] = "❌ imagewebp a échoué pour l’image originale";
        }

        // Tailles redimensionnées
        $sizes = [1200, 768, 320];
        foreach ($sizes as $size) {
            $sizeDir = "$uploadDir/x$size";
            if (!file_exists($sizeDir)) {
                mkdir($sizeDir, 0755, true);
                $log[] = "📁 Dossier créé : $sizeDir";
            }

            $resizedName = "{$originalName}_x{$size}.webp";
            $resizedPath = "$sizeDir/$resizedName";
            resizeAndConvertToWebP($tmpPath, $resizedPath, $size, $log);
        }

        $message = "✅ Upload et conversion réussis pour la catégorie : <strong>$category</strong>.";
        $success = true;
    } else {
        $log[] = "❌ imagecreatefromstring a échoué pour l'image uploadée.";
        $message = "❌ Erreur lors du traitement de l'image.";
    }
} else {
    $log[] = "❌ Requête invalide ou catégorie non reconnue.";
    $message = "❌ Erreur : Catégorie invalide ou fichier manquant.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Résultat de l'upload</title>
    <link rel="stylesheet" href="/css/upload_message.css" />
    <script>
        setTimeout(() => window.location.href = 'dashboard.php', 5000);
    </script>
</head>
<body>

<div class="message <?= $success ? 'success' : 'error' ?>">
    <?= $message ?>
</div>

<?php if ($success && $imagePreviewUrl): ?>
    <div class="preview">
        <p>Aperçu de l’image originale :</p>
        <img src="<?= htmlspecialchars($imagePreviewUrl) ?>" alt="Image uploadée" style="max-width:400px; max-height:300px; border:1px solid #ccc;">
    </div>
<?php endif; ?>

<?php if ($debug): ?>
    <div class="debug" style="background:#f9f9f9; border:1px solid #ccc; padding:10px; margin-top:20px;">
        <h3>🔧 Debug info</h3>
        <ul>
            <?php foreach ($log as $line): ?>
                <li><?= htmlspecialchars($line) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="info">Redirection automatique dans 5 secondes...</div>
<a href="dashboard.php" class="button">Retour immédiat au Dashboard</a>

</body>
</html>
