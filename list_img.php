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

// Récupération des contacts et partenaires
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
$partenaires = enrichPartnersWithVersions($pdo, $partenaires);

// Chemin racine des images (relatif au serveur)
$imgRoot = __DIR__ . '/img/';
$subSizes = ['x320', 'x768', 'x1200'];

// Récupère tous les dossiers d'images sauf les sous-dossiers de tailles
$directories = array_filter(scandir($imgRoot), function ($dir) use ($imgRoot, $subSizes) {
    return is_dir($imgRoot . $dir) && !in_array($dir, ['.', '..']) && !in_array($dir, $subSizes);
});
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Liste des images</title>
    <link rel="stylesheet" href="/css/style.css" />
    <link rel="stylesheet" href="/css/nav.css" />
    <link rel="stylesheet" href="/css/footer.css" />
    <link rel="stylesheet" href="/css/list_img.css" />
</head>
<body>
<?php safeRequire('nav.php'); ?>

<main class="main-content">
    <section class="image-list">
        <h2>Images par dossier</h2>

        <?php foreach ($directories as $dir):
            $folderPath = $imgRoot . $dir . '/';
            $x320Folder = $folderPath . 'x320/';
            $toDisplay = [];

            // Étape 1 : récupérer les images x320 si elles existent
            if (is_dir($x320Folder)) {
                foreach (scandir($x320Folder) as $file) {
                    if (preg_match('/^(.*)_320\.(jpg|jpeg|png|gif|webp)$/i', $file, $matches)) {
                        $baseName = $matches[1];
                        $ext = $matches[2];

                        $paths = [
                            'original' => $dir . '/' . $baseName . '.' . $ext,
                            'x1200' => $dir . '/x1200/' . $baseName . '_1200.' . $ext,
                            'x768' => $dir . '/x768/' . $baseName . '_768.' . $ext,
                            'x320' => $dir . '/x320/' . $file
                        ];

                        $existingPaths = array_filter($paths, fn($p) => file_exists($imgRoot . $p));

                        if (isset($existingPaths['x320'])) {
                            $toDisplay[$baseName] = [
                                'displayPath' => $paths['x320'],
                                'allPaths' => $existingPaths
                            ];
                        }
                    }
                }
            }

            // Étape 2 : récupérer les originales
            foreach (scandir($folderPath) as $file) {
                if (is_file($folderPath . $file) &&
                    preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file) &&
                    !preg_match('/_(1200|768|320)\./i', $file)
                ) {
                    $baseName = pathinfo($file, PATHINFO_FILENAME);
                    $ext = pathinfo($file, PATHINFO_EXTENSION);

                    if (isset($toDisplay[$baseName])) continue;

                    $paths = [
                        $dir . '/' . $file,
                        $dir . '/x1200/' . $baseName . '_1200.' . $ext,
                        $dir . '/x768/' . $baseName . '_768.' . $ext,
                        $dir . '/x320/' . $baseName . '_320.' . $ext
                    ];

                    $existingPaths = array_filter($paths, fn($p) => file_exists($imgRoot . $p));

                    $toDisplay[$baseName] = [
                        'displayPath' => $dir . '/' . $file,
                        'allPaths' => $existingPaths
                    ];
                }
            }
            ?>

            <h3>Dossier : <strong><?= htmlspecialchars($dir) ?></strong></h3>
            <div class="image-grid">
                <?php foreach ($toDisplay as $item): ?>
                    <div class="image-card" data-image-paths='<?= json_encode($item['allPaths']) ?>'>
                        <img src="/img/<?= htmlspecialchars($item['displayPath']) ?>" alt="" class="thumbnail">
                        <div class="delete-feedback"></div>
                        <button class="delete-btn">🗑 Supprimer</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <hr>
        <?php endforeach; ?>
    </section>

    <div id="image-message" class="image-message" style="display: none;"></div>
</main>

<?php includeFooter($contact, $partenaires); ?>

<!-- Scripts -->
<script src="/js/scroll.js" defer></script>
<script src="/js/nav_img.js" defer></script>
<script src="/js/modal_image_background_nav.js" defer></script>
<script src="/js/menuburger.js" defer></script>
<script src="/js/modal_gallery.js" defer></script>
<script src="/js/slide-partenaire.js" defer></script>
<script src="/js/delete_image_ajax.js" defer></script>
</body>
</html>
