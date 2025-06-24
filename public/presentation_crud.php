<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once './db.php';
require_once __DIR__ . '/functions.php';
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
if (!isset($_SESSION['user_id'])) {
    die("Accès non autorisé.");
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$errors = [];
$success = '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$presentation = null;

// Traitement suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'], $_POST['csrf_token'])) {
    if ($_POST['csrf_token'] !== $csrfToken) {
        die("Token CSRF invalide.");
    }
    $deleteId = (int) $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM presentation WHERE id = ?");
    if ($stmt->execute([$deleteId])) {
        $success = "Présentation supprimée avec succès.";
        header("Location: presentation_crud.php");
        exit;
    } else {
        $errors[] = "Erreur lors de la suppression.";
    }
}

// Chargement présentation pour édition
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM presentation WHERE id = ?");
    $stmt->execute([$id]);
    $presentation = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$presentation) {
        die("Présentation introuvable.");
    }
}

// Traitement ajout / modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrfToken) {
        die("Token CSRF invalide.");
    }

    $team_name = trim($_POST['team_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $edit_id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($team_name === '') {
        $errors[] = "Le nom de l'équipe est requis.";
    }
    if ($description === '') {
        $errors[] = "La description est requise.";
    }

    if (empty($errors)) {
        if ($edit_id > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE presentation SET team_name = ?, description = ? WHERE id = ?");
            if ($stmt->execute([$team_name, $description, $edit_id])) {
                $success = "Présentation modifiée avec succès.";
                header("Location: presentation_crud.php");
                exit;
            } else {
                $errors[] = "Erreur lors de la modification.";
            }
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO presentation (team_name, description) VALUES (?, ?)");
            if ($stmt->execute([$team_name, $description])) {
                $success = "Présentation créée avec succès.";
                header("Location: presentation_crud.php");
                exit;
            } else {
                $errors[] = "Erreur lors de la création.";
            }
        }
    }
}

// Récupérer toutes les présentations
$stmt = $pdo->query("SELECT * FROM presentation ORDER BY id ASC");
$presentations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta charset="UTF-8" />
    <title><?= $id > 0 ? "Modifier une présentation" : "Créer une présentation" ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/nav.css">
    <link rel="stylesheet" href="/public/css/footer.css">
</head>

<body>
    <?php safeRequire('nav.php'); ?>
    <main class="main-content">
        <h1><?= $id > 0 ? "Modifier une présentation" : "Créer une présentation" ?></h1>

        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom de l'équipe</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($presentations as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['id']) ?></td>
                        <td><?= htmlspecialchars($p['team_name']) ?></td>
                        <td><?= nl2br(htmlspecialchars($p['description'])) ?></td>
                        <td>
                            <a href="?id=<?= $p['id'] ?>">Modifier</a> |
                            <form method="POST" style="display:inline;"
                                onsubmit="return confirm('Confirmer la suppression ?');">
                                <input type="hidden" name="delete_id" value="<?= $p['id'] ?>" />
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>" />
                                <button type="submit" class="delete-btn">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>" />
            <input type="hidden" name="id" value="<?= $presentation['id'] ?? '' ?>" />
            <div>
                <label for="team_name">Nom de l'équipe :</label><br>
                <input type="text" id="team_name" name="team_name" required
                    value="<?= htmlspecialchars($presentation['team_name'] ?? '') ?>" />
            </div>
            <div>
                <label for="description">Description :</label><br>
                <textarea id="description" name="description"
                    required><?= htmlspecialchars($presentation['description'] ?? '') ?></textarea>
            </div>
            <div style="margin-top: 10px;">
                <button type="submit" name="submit"><?= $id > 0 ? "Modifier" : "Créer" ?></button>
                <?php if ($id > 0): ?>
                    <a href="presentation_crud.php" style="margin-left: 10px;">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
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