<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once './db.php';
require_once __DIR__ . '/functions.php';

// Vérification de session
if (!isset($_SESSION['user_id'])) {
    die("Accès non autorisé.");
}

// Récupération navigation et partenaires
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
$partenaires = enrichPartnersWithVersions($pdo, $partenaires);

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Initialisation variables
$errors = [];
$success = '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$contactData = null;

// SUPPRESSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'], $_POST['csrf_token'])) {
    if ($_POST['csrf_token'] !== $csrfToken) die("Token CSRF invalide.");

    $deleteId = (int) $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = :id");
    if ($stmt->execute([':id' => $deleteId])) {
        header("Location: contacts_crud.php?success=1");
        exit;
    } else {
        $errors[] = "Erreur lors de la suppression.";
    }
}

// CHARGEMENT POUR MODIFICATION
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT id, lieu, numero_tel, email FROM contacts WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $contactData = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$contactData) die("Contact introuvable.");
}

// AJOUT / MODIFICATION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrfToken) {
        die("Token CSRF invalide.");
    }

    $lieu = trim($_POST['lieu'] ?? '');
    $numero_tel = trim($_POST['numero_tel'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $edit_id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($lieu === '') $errors[] = "Le lieu est requis.";
    if ($numero_tel === '') $errors[] = "Le numéro de téléphone est requis.";
    if ($email === '') $errors[] = "L'adresse email est requise.";

    if (empty($errors)) {
        if ($edit_id > 0) {
            // UPDATE
            $stmt = $pdo->prepare("UPDATE contacts SET lieu = :lieu, numero_tel = :tel, email = :email WHERE id = :id");
            if ($stmt->execute([
                ':lieu' => $lieu,
                ':tel' => $numero_tel,
                ':email' => $email,
                ':id' => $edit_id
            ])) {
                header("Location: contacts_crud.php?success=1");
                exit;
            } else {
                $errors[] = "Erreur lors de la modification.";
            }
        } else {
            // INSERT
            $stmt = $pdo->prepare("INSERT INTO contacts (lieu, numero_tel, email) VALUES (:lieu, :tel, :email)");
            if ($stmt->execute([
                ':lieu' => $lieu,
                ':tel' => $numero_tel,
                ':email' => $email
            ])) {
                header("Location: contacts_crud.php?success=1");
                exit;
            } else {
                $errors[] = "Erreur lors de l'ajout (email peut-être déjà utilisé).";
            }
        }
    }
}

// LECTURE DE TOUS LES CONTACTS
$stmt = $pdo->query("SELECT id, lieu, numero_tel, email FROM contacts ORDER BY id ASC");
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title><?= $id > 0 ? "Modifier un contact" : "Créer un contact" ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/nav.css">
    <link rel="stylesheet" href="/css/footer.css">
</head>
<body>
<?php safeRequire('nav.php'); ?>
<main class="main-content">

    <h1>Contacts</h1>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <p class="success">Action effectuée avec succès.</p>
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
                    <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce contact ?');">
                        <input type="hidden" name="delete_id" value="<?= $c['id'] ?>" />
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>" />
                        <button type="submit" class="delete-btn">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2><?= $id > 0 ? "Modifier un contact" : "Créer un contact" ?></h2>
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

<script src="/js/scroll.js" defer></script>
<script src="/js/nav_img.js" defer></script>
<script src="/js/modal_image_background_nav.js" defer></script>
<script src="/js/menuburger.js" defer></script>
<script src="/js/modal_gallery.js" defer></script>
<script src="/js/slide-partenaire.js" defer></script>
</body>
</html>
