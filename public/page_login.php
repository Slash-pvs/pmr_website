<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Vérification de la session, si elle n'est pas démarrée, on la démarre
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inclure les fonctions et la base de données
require_once __DIR__ . '/functions.php';
$pdo = require __DIR__ . '/db.php';
handleRequest($pdo);
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errorMessage = 'Erreur CSRF.';
    }

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $errorMessage = 'Veuillez remplir tous les champs.';
    } else {
        // Requête avec l'email
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            // die("Redirection vers dashboard.php"); // <-- Supprimer ou commenter cette ligne
            header('Location: dashboard.php');
            exit;
        }
    }
}

// Récupération des données pour la navigation et le footer
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/public/css/login.css"> <!-- Style spécifique pour le formulaire de login -->
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/nav.css">
    <link rel="stylesheet" href="/public/css/footer.css">

</head>

<body>

    <!-- Navigation -->
    <?php safeRequire('nav.php'); ?>

    <div id="login-wrapper">
        <div class="login-box">
            <h1>Connexion</h1>

            <!-- Affichage du message d'erreur -->
            <?php if (!empty($errorMessage)): ?>
                <div class="login-error"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>

            <!-- Formulaire de connexion -->
            <form method="POST" class="login-form" novalidate>
                <!-- Champ e-mail -->
                <div class="login-group">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <!-- Champ mot de passe -->
                <div class="login-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <!-- Token CSRF -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()); ?>">

                <!-- Bouton de soumission -->
                <button type="submit" class="login-btn">Se connecter</button>
            </form>
        </div>
    </div>

    <!-- Validation côté client -->
    <script>
        document.querySelector('form').addEventListener('submit', function (event) {
            var email = document.getElementById('email').value;
            var password = document.getElementById('password').value;

            if (email === '' || password === '') {
                alert('Veuillez remplir tous les champs');
                event.preventDefault(); // Empêche la soumission du formulaire
            }
        });
    </script>

    <!-- Footer -->
    <?php
    includeFooter($contact, $partenaires);
    ?>

    <!-- Autres scripts -->
    <script src="/public/js/rewrite_url.js" defer></script>
    <script src="/public/js/scroll.js" defer></script>
    <script src="/public/js/nav_img.js" defer></script>
    <script src="/public/js/modal_image_background_nav.js" defer></script>
    <script src="/public/js/menuburger.js" defer></script>
    <script src="/public/js/modal_gallery.js" defer></script>
    <script src="/public/js/slide-partenaire.js" defer></script>
    <script src="/public/js/regex_login_mdp_mail.js" defer></script>
</body>

</html>