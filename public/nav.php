<?php
$pdo = require __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
$navImageData = getNavImageWithVersions($pdo);
$imageVersions = json_decode(getImageNavVersions($pdo, 1), true);
?>

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<div class="navbar" id="navContainer">
    <div class="nav">
        <!-- Logo -->
        <picture id="logoPMR">
            <source srcset="/img/x1200/logo_pmr_rond_1200.webp" media="(min-width: 1200px)">
            <source srcset="/img/x768/logo_pmr_rond_768.webp" media="(min-width: 768px)">
            <img class="img_nav" src="/img/x320/logo_pmr_rond_320.webp" alt="Logo PMR">
        </picture>
        <!-- Bouton Burger -->
        <div class="burger-menu" id="burgerMenu">
            <div></div>
            <div></div>
            <div></div>
        </div>
        <?php require_once __DIR__ . '/includes/menu_mobile.php'; ?>
    </div>

    <!-- Liens réseaux sociaux -->
    <div class="nav-left">
        <?php
        $links = [
            'instagram' => [
                'url' => 'https://www.instagram.com/paysmedocrugbyoff/?api=1/',
                'base_name' => 'icone_insta_wh',
                'alt' => 'Instagram',
                'class' => 'insta-icon'
            ],
            'facebook' => [
                'url' => 'https://www.facebook.com/p/Pays-M%C3%A9doc-Rugby-Page-Officielle-100063770131469/',
                'base_name' => 'facebook-icon',
                'alt' => 'Facebook',
                'class' => 'facebook-icon'
            ]
        ];

        foreach ($links as $platform => $data): ?>
            <a href="<?= htmlspecialchars($data['url']) ?>" target="_blank" rel="noopener noreferrer"
                class="<?= $platform ?>-link">
                <picture>
                    <source srcset="/img/x1200/<?= $data['base_name'] ?>_1200.webp" media="(min-width: 1200px)">
                    <source srcset="/img/x768/<?= $data['base_name'] ?>_768.webp" media="(min-width: 768px)">
                    <img src="/img/x320/<?= $data['base_name'] ?>_320.webp" alt="<?= htmlspecialchars($data['alt']) ?>"
                        class="<?= $data['class'] ?>">
                </picture>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Boutique -->
    <div class="nav-right">
        <a class="boutique_nav" href="boutique.php">Notre boutique</a>
        <div class="panier-wrapper">
            <a href="/panier.php" class="panier-link">
                <i class="fas fa-shopping-cart"></i>
                <span
                    id="panier-count"><?= isset($_SESSION['panier']) ? array_sum(array_column($_SESSION['panier'], 'quantite')) : 0 ?></span>
            </a>
        </div>
    </div>
</div>

<!-- Conteneur supplémentaire pour le fond -->
<div id="navBar">
    <div id="navDiv" class="nav_div">
        <div class="dropdown">
            <a class="page_pmr" href="#">Nos équipes</a>
            <div class="dropdown-content">
                <a class="no_shadow" href="senior.php">Senior</a>
                <a class="no_shadow" href="pole_jeunes.php">Pôle Jeunes</a>
                <a class="no_shadow" href="ecole_rugby.php">Ecole de Rugby</a>
            </div>
        </div>
        <a class="page_pmr" href="index.php">Accueil</a>
        <a class="page_pmr" href="article.php">L'actualité</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a class="page_pmr" href="logout.php">Se déconnecter</a>
            <a class="page_pmr" href="dashboard.php">Dashboard</a>
        <?php else: ?>
            <a class="page_pmr" href="page_login.php">Se connecter</a>
        <?php endif; ?>
    </div>
</div>

<hr class="hr">

<!-- Modal -->
<div id="imageModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <img id="modalImage" class="modal-content" src="" alt="image">
        <div id="caption"></div>
    </div>
</div>

<!-- Script JS dynamique pour fond -->
<script id="imageData" type="application/json"><?= json_encode($imageVersions, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
<script src="/public/js/modal_image_background_nav.js" defer></script>