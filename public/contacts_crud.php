<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once './db.php';
require_once __DIR__ . '/functions.php';

$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);

if (!isset($_SESSION['user_id'])) {
    die("Accès non autorisé.");
}

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$errors = [];
$success = '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$contactData = null;

// Suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'], $_POST['csrf_token'])) {
    if ($_POST['csrf_token'] !== $csrfToken) {
        die("Token CSRF invalide.");
    }
    $deleteId = (int) $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
    if ($stmt->execute([$deleteId])) {
        $success = "Contact supprimé avec succès.";
        header("Location: contacts_crud.php");
        exit;
    } else {
        $errors[] = "Erreur lors de la suppression.";
    }
}

// Chargement pour édition
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE id = ?");
    $stmt->execute([$id]);
    $contactData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$contactData) {
        die("Contact introuvable.");
    }
}

// Ajout / modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrfToken) {
        die("Token CSRF invalide.");
    }

    $lieu = trim($_POST['lieu'] ?? '');
    $numero_tel = trim($_POST['numero_tel'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $edit_id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($lieu === '')
        $errors[] = "Le lieu est requis.";
    if ($numero_tel === '')
        $errors[] = "Le numéro de téléphone est requis.";
    if ($email === '')
        $errors[] = "L'adresse email est requise.";

    if (empty($errors)) {
        if ($edit_id > 0) {
            // Update
            $stmt = $pdo->prepare("UPDATE contacts SET lieu = ?, numero_tel = ?, email = ? WHERE id = ?");
            if ($stmt->execute([$lieu, $numero_tel, $email, $edit_id])) {
                $success = "Contact modifié avec succès.";
                header("Location: contacts_crud.php");
                exit;
            } else {
                $errors[] = "Erreur lors de la modification.";
            }
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO contacts (lieu, numero_tel, email) VALUES (?, ?, ?)");
            if ($stmt->execute([$lieu, $numero_tel, $email])) {
                $success = "Contact ajouté avec succès.";
                header("Location: contacts_crud.php");
                exit;
            } else {
                $errors[] = "Erreur lors de l'ajout (email peut-être déjà utilisé).";
            }
        }
    }
}

// Lecture de tous les contacts
$stmt = $pdo->query("SELECT * FROM contacts ORDER BY id ASC");
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <title><?= $id > 0 ? "Modifier un contact" : "Créer un contact" ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/nav.css">
    <link rel="stylesheet" href="/public/css/footer.css">
</head>

<body>
    <?php safeRequire('nav.php'); ?>
    <main class="main-content">
        <h1>Modifier un contact</h1>

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
                    <th>Lieu</th>
                    <th>Téléphone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['id']) ?></td>
                        <td><?= htmlspecialchars($c['lieu']) ?></td>
                        <td><?= htmlspecialchars($c['numero_tel']) ?></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td>
                            <a href="?id=<?= $c['id'] ?>">Modifier</a> |
                            <form method="POST" style="display:inline;"
                                onsubmit="return confirm('Supprimer ce contact ?');">
                                <input type="hidden" name="delete_id" value="<?= $c['id'] ?>" />
                                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>" />
                                <button type="submit" class="delete-btn">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h1> Créer un contact</h1>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>" />
            <input type="hidden" name="id" value="<?= $contactData['id'] ?? '' ?>" />
            <div>
                <label for="lieu">Lieu :</label><br>
                <input type="text" id="lieu" name="lieu" required
                    value="<?= htmlspecialchars($contactData['lieu'] ?? '') ?>" />
            </div>
            <div>
                <label for="numero_tel">Numéro de téléphone :</label><br>
                <input type="text" id="numero_tel" name="numero_tel" required
                    value="<?= htmlspecialchars($contactData['numero_tel'] ?? '') ?>" />
            </div>
            <div>
                <label for="email">Email :</label><br>
                <input type="email" id="email" name="email" required
                    value="<?= htmlspecialchars($contactData['email'] ?? '') ?>" />
            </div>
            <div style="margin-top: 10px;">
                <button type="submit" name="submit"><?= $id > 0 ? "Modifier" : "Créer" ?></button>
                <?php if ($id > 0): ?>
                    <a href="contacts_crud.php" style="margin-left: 10px;">Annuler</a>
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
</body>

</html>