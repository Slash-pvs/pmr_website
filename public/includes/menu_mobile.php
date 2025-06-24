<div class="mobile-menu hidden" id="mobileMenu">
    <a href="index.php">Accueil</a>
    <a href="article.php">L'actualité</a>

    <!-- Nos équipes ajoutées ici -->
    <a href="senior.php">Nos équipes - Senior</a>
    <a href="pole_jeunes.php">Nos équipes - Pôle Jeunes</a>
    <a href="ecole_rugby.php">Nos équipes - École de Rugby</a>

    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="dashboard.php">Dashboard</a>
        <a href="logout.php">Se déconnecter</a>
    <?php else: ?>
        <a href="page_login.php">Se connecter</a>
    <?php endif; ?>
    <a class="boutique_nav" href="boutique.php">Notre boutique</a>
    <a href="panier.php">
        🛒 Mon panier <span id="panier-count-mobile">0</span>
    </a>
</div>