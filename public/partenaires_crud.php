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

// Récupération des données pour la navigation et les partenaires
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);

// Récupérer tous les partenaires pour le formulaire
$stmt = $pdo->query("SELECT * FROM partenaires ORDER BY id DESC");
$partenaires_form = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des partenaires</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/nav.css">
    <link rel="stylesheet" href="/public/css/footer.css">
</head>

<body>
    <?php safeRequire('nav.php'); ?>
    <main class="main-content">
        <h1>Liste des partenaires</h1>
        <p><a href="partenaire_form.php">Ajouter un partenaire</a></p>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom fichier</th>
                    <th>Site URL</th>
                    <th>Description</th>
                    <th>Logo</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($partenaires_form as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['id']) ?></td>
                        <td><?= htmlspecialchars($p['nom_fichier']) ?></td>
                        <td>
                            <?php if (!empty($p['lien_site'])): ?>
                                <a href="<?= htmlspecialchars($p['lien_site']) ?>" target="_blank">
                                    <?= htmlspecialchars($p['lien_site']) ?>
                                </a>
                            <?php else: ?>
                                <em>Non fourni</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?= !empty($p['description']) ? nl2br(htmlspecialchars($p['description'])) : '<em>Non fournie</em>' ?>
                        </td>
                        <td>
                            <?php if (!empty($p['chemin'])): ?>
                                <img src="<?= htmlspecialchars($p['chemin']) ?>" alt="<?= htmlspecialchars($p['nom_fichier']) ?>" style="height:50px;" />
                            <?php else: ?>
                                <em>Pas d’image</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="partenaire_form.php?id=<?= $p['id'] ?>">Modifier</a> |
                            <form method="POST" action="/includes/partenaire_action.php" style="display:inline" onsubmit="return confirm('Confirmer la suppression ?');">
                                <input type="hidden" name="action" value="delete" />
                                <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>" />
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>" />
                                <button type="submit" style="background:none; border:none; color:#007BFF; cursor:pointer; padding:0; font-size:1em;">
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </main>
    <?php includeFooter($contact, $partenaires); ?>

    <script src="/public/js/scroll.js" defer></script>
    <script src="/public/js/nav_img.js" defer></script>
    <script src="/public/js/modal_image_background_nav.js" defer></script>
    <script src="/public/js/menuburger.js" defer></script>
    <script src="/public/js/modal_gallery.js" defer></script>
    <script src="/public/js/slide-partenaire.js" defer></script>
    <script src="/public/js/widget-ffr.js" defer></script>

    <script>
        const availableImages = <?= json_encode(array_map('basename', $availableImages ?? [])) ?>;
        console.log("Images disponibles :", availableImages);
    </script>
</body>

</html>
