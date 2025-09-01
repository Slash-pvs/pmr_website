<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/functions.php';
$pdo = require __DIR__ . '/db.php';

$errorMessage = '';

// Générer ou récupérer le token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrfToken) {
        $errorMessage = 'Erreur CSRF.';
    }

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $errorMessage = 'Veuillez remplir tous les champs.';
    }

    if (empty($errorMessage)) {
        // Requête sécurisée
        $stmt = $pdo->prepare('SELECT id, email, password FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            header('Location: dashboard.php');
            exit;
        } else {
            $errorMessage = 'Email ou mot de passe incorrect.';
        }
    }
}

// Récupération des données pour la navigation et le footer
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
$partenaires = enrichPartnersWithVersions($pdo, $partenaires);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/login.css">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/nav.css">
    <link rel="stylesheet" href="/css/footer.css">
</head>
<body>

<?php safeRequire('nav.php'); ?>

<div id="login-wrapper">
    <div class="login-box">
        <h1>Connexion</h1>

        <?php if (!empty($errorMessage)): ?>
            <div class="login-error"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>

        <form method="POST" class="login-form" novalidate>
            <div class="login-group">
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="login-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>

            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <button type="submit" class="login-btn">Se connecter</button>
        </form>
    </div>
</div>

<script>
document.querySelector('form').addEventListener('submit', function(event) {
    var email = document.getElementById('email').value.trim();
    var password = document.getElementById('password').value.trim();
    if(email === '' || password === '') {
        alert('Veuillez remplir tous les champs.');
        event.preventDefault();
    }
});
</script>

<?php includeFooter($contact, $partenaires); ?>

<script src="/js/scroll.js" defer></script>
<script src="/js/nav_img.js" defer></script>
<script src="/js/modal_image_background_nav.js" defer></script>
<script src="/js/menuburger.js" defer></script>
<script src="/js/modal_gallery.js" defer></script>
<script src="/js/slide-partenaire.js" defer></script>
<script src="/js/regex_login_mdp_mail.js" defer></script>
</body>
</html>
