<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require_once './db.php';
require_once './functions.php';

if (!isset($_SESSION['user_id'])) {
    die("Accès non autorisé.");
}

$userId = $_SESSION['user_id'];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$partenaire = [
    'nom_fichier' => '',
    'lien_site' => '',
];

$responsiveImages = [];

if ($id > 0) {
    // Récupération du partenaire
    $stmt = $pdo->prepare("SELECT * FROM partenaires WHERE id = ?");
    $stmt->execute([$id]);
    $partenaire = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$partenaire) {
        die("Partenaire non trouvé.");
    }

    // Récupération des versions d’image liées
    $stmt = $pdo->prepare("SELECT * FROM partenaire_versions WHERE partenaire_id = ? ORDER BY size ASC");
    $stmt->execute([$id]);
    $responsiveImages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Formats d’image gérés
$formats = ['320', '768', '1200'];
$availableImages = [];

// Récupération des images disponibles dans chaque dossier x320, x768, x1200
foreach ($formats as $format) {
    $dir = __DIR__ . "/img/partenaire/x$format";
    if (is_dir($dir)) {
        $files = array_filter(scandir($dir), function ($file) use ($dir) {
            return !in_array($file, ['.', '..']) && is_file("$dir/$file");
        });
        $availableImages[$format] = array_values($files);
    } else {
        $availableImages[$format] = [];
    }
}

// Préparation des images sélectionnées pour pré-remplir le formulaire (par taille)
$selectedImages = [];
foreach ($responsiveImages as $img) {
    $selectedImages[(string) $img['size']] = basename($img['path']);
}

/*
// Debug : voir ce qui est récupéré
echo '<pre>responsiveImages = ';
print_r($responsiveImages);
echo '</pre>';
*/
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title><?= $id ? "Modifier un partenaire" : "Ajouter un partenaire" ?></title>
    <link rel="stylesheet" href="/public/css/style.css" />
    <link rel="stylesheet" href="/public/css/nav.css" />
    <link rel="stylesheet" href="/public/css/footer.css" />
</head>

<body>
    <?php safeRequire('nav.php'); ?>

    <main class="main-content">
        <h1><?= $id ? "Modifier" : "Ajouter" ?> un partenaire</h1>

        <form action="/includes/partenaire_action.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <label for="nom_fichier">Nom fichier :</label><br />
            <input type="text" id="nom_fichier" name="nom_fichier" required
                value="<?= htmlspecialchars($partenaire['nom_fichier'] ?? '') ?>"><br><br>

            <label for="lien_site">Site URL :</label><br />
            <input type="url" id="lien_site" name="lien_site"
                value="<?= htmlspecialchars($partenaire['lien_site'] ?? '') ?>"><br><br>
            <fieldset>
                <legend>Versions du logo (formats d’image)</legend>
                <?php foreach ($formats as $format):
                    $images = $availableImages[$format];
                    $selected = $selectedImages[$format] ?? '';
                    // Chemin complet de l'image sélectionnée pour l'affichage
                    $imagePath = $selected ? "/img/partenaire/x$format/$selected" : null;
                    ?>
                    <div style="margin-bottom: 1.5em;">
                        <label for="version_<?= $format ?>">Image <?= $format ?> px :</label><br>
                        <select name="versions[<?= $format ?>]" id="version_<?= $format ?>" required>
                            <option value="">-- Choisir une image dans x<?= $format ?> --</option>
                            <?php foreach ($images as $img): ?>
                                <option value="<?= htmlspecialchars($img) ?>" <?= ($img === $selected) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($img) ?>
                                </option>
                            <?php endforeach; ?>
                        </select><br />
                        <?php if ($imagePath): ?>
                            <img src="<?= htmlspecialchars($imagePath) ?>" alt="Logo <?= $format ?> px"
                                style="max-height: 60px; margin-top: 0.5em; border: 1px solid #ccc;">
                        <?php else: ?>
                            <small>Aucune image sélectionnée</small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </fieldset>
            <br>

            <?php if (!empty($responsiveImages)): ?>
                <label>Logo actuel :</label><br />
                <picture>
                    <?php foreach ($responsiveImages as $img):
                        $media = (int) $img['size'];
                        $src = htmlspecialchars($img['path']);
                        ?>
                        <source media="(max-width: <?= $media ?>px)" srcset="<?= $src ?>">
                    <?php endforeach; ?>
                    <img src="<?= htmlspecialchars(end($responsiveImages)['path']) ?>" alt="Logo partenaire"
                        style="height: 50px;">
                </picture><br><br>
            <?php endif; ?>

            <label for="logo">Nouveau logo (facultatif) :</label><br />
            <input type="file" id="logo" name="logo" accept="image/*"><br><br>

            <button type="submit" name="action" value="<?= $id ? 'update' : 'create' ?>">
                <?= $id ? 'Modifier' : 'Ajouter' ?>
            </button>
        </form>

        <p><a href="partenaires_crud.php">← Retour à la liste</a></p>
    </main>

    <?php includeFooter($contact, $partenaires); ?>

    <script src="/public/js/scroll.js" defer></script>
    <script src="/public/js/nav_img.js" defer></script>
    <script src="/public/js/modal_image_background_nav.js" defer></script>
    <script src="/public/js/menuburger.js" defer></script>
    <script src="/public/js/modal_gallery.js" defer></script>
    <script src="/public/js/slide-partenaire.js" defer></script>
    <script src="/public/js/widget-ffr.js" defer></script>
</body>

</html>