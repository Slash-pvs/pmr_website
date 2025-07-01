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

$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Liste des images</title>
    <link rel="stylesheet" href="/public/css/style.css" />
    <link rel="stylesheet" href="/public/css/nav.css" />
    <link rel="stylesheet" href="/public/css/footer.css" />
    <link rel="stylesheet" href="/public/css/list_img.css" />
</head>

<body>
    <?php safeRequire('nav.php'); ?>

    <main class="main-content">
        <section class="image-list">
            <h2>Images par dossier</h2>
            <?php
            $imgRoot = './img/';
            $subSizes = ['x1200', 'x768', 'x320'];

            $directories = array_filter(scandir($imgRoot), function ($dir) use ($imgRoot, $subSizes) {
                return is_dir($imgRoot . $dir) && !in_array($dir, ['.', '..']) && !in_array($dir, $subSizes);
            });

            foreach ($directories as $dir) {
                $folderPath = $imgRoot . $dir . '/';
                $x320Folder = $folderPath . 'x320/';
                $toDisplay = [];

                // Étape 1 : Ajouter les images x320 si pas d'original
                if (is_dir($x320Folder)) {
                    foreach (scandir($x320Folder) as $file) {
                        if (preg_match('/^(.*)_320\.(jpg|jpeg|png|gif|webp)$/i', $file, $matches)) {
                            $baseName = $matches[1];
                            $ext = $matches[2];

                            $relativePaths = [
                                'original' => $dir . '/' . $baseName . '.' . $ext,
                                'x1200' => $dir . '/x1200/' . $baseName . '_1200.' . $ext,
                                'x768' => $dir . '/x768/' . $baseName . '_768.' . $ext,
                                'x320' => $dir . '/x320/' . $file
                            ];

                            $allPaths = [];
                            foreach ($relativePaths as $relPath) {
                                if (file_exists($imgRoot . $relPath)) {
                                    $allPaths[] = $relPath;
                                }
                            }

                            if (in_array($relativePaths['x320'], $allPaths)) {
                                $toDisplay[$baseName] = [
                                    'displayPath' => $relativePaths['x320'],
                                    'allPaths' => $allPaths
                                ];
                            }
                        }
                    }
                }

                // Étape 2 : Ajouter les originales (si pas déjà listées via x320)
                foreach (scandir($folderPath) as $file) {
                    if (
                        is_file($folderPath . $file) &&
                        preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file) &&
                        !preg_match('/_(1200|768|320)\./i', $file)
                    ) {
                        $baseName = pathinfo($file, PATHINFO_FILENAME);
                        $ext = pathinfo($file, PATHINFO_EXTENSION);

                        // Ne pas ajouter si déjà ajouté via x320
                        if (isset($toDisplay[$baseName]))
                            continue;

                        $relativePaths = [
                            $dir . '/' . $file,
                            $dir . '/x1200/' . $baseName . '_1200.' . $ext,
                            $dir . '/x768/' . $baseName . '_768.' . $ext,
                            $dir . '/x320/' . $baseName . '_320.' . $ext
                        ];

                        // Ne garder que les fichiers qui existent
                        $allPaths = array_filter($relativePaths, function ($relPath) use ($imgRoot) {
                            return file_exists($imgRoot . $relPath);
                        });

                        $toDisplay[$baseName] = [
                            'displayPath' => $dir . '/' . $file,
                            'allPaths' => $allPaths
                        ];
                    }
                }

                // === Affichage HTML ===
                echo "<h3>Dossier : <strong>" . htmlspecialchars($dir) . "</strong></h3>";
                echo '<div class="image-grid">';

                foreach ($toDisplay as $item) {
                    echo '
        <div class="image-card" data-image-paths=\'' . json_encode($item['allPaths']) . '\'>
            <img src="/img/' . htmlspecialchars($item['displayPath']) . '" alt="" class="thumbnail">
            <div class="delete-feedback"></div>
            <button class="delete-btn">🗑 Supprimer</button>
        </div>';
                }

                echo '</div><hr>';
            }
            ?>
        </section>
        <div id="image-message" class="image-message" style="display: none;"></div>
    </main>

    <?php includeFooter($contact, $partenaires); ?>

    <!-- Scripts -->
    <script src="/public/js/scroll.js" defer></script>
    <script src="/public/js/nav_img.js" defer></script>
    <script src="/public/js/modal_image_background_nav.js" defer></script>
    <script src="/public/js/menuburger.js" defer></script>
    <script src="/public/js/modal_gallery.js" defer></script>
    <script src="/public/js/slide-partenaire.js" defer></script>
    <script src="/public/js/delete_image_ajax.js" defer></script>
</body>

</html>