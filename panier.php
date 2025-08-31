<?php
// Affichage d'erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
// Inclusion des fichiers nécessaires
require_once __DIR__ . '/functions.php';
$pdo = require __DIR__ . '/db.php';

// Vérification du panier
$panier = isset($_SESSION['panier']) ? $_SESSION['panier'] : [];
$total = 0;

// Récupération des données pour la navigation et les partenaires
$contact = getContactInfo($pdo);
$partenaires = getAllPartners($pdo);
$partenaires = enrichPartnersWithVersions($pdo, $partenaires);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Votre Panier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/nav.css">
    <link rel="stylesheet" href="/css/footer.css">
    <link rel="stylesheet" href="/css/panier.css">
</head>

<body>
    <!-- Nav -->
    <?php safeRequire('nav.php'); ?>

    <!-- Conteneur principal -->
    <main id="mainContent" class="main-content">
        <div class="box_panier">
            <h1>🛒 Votre panier</h1>
            <?php if (isset($_GET['success'])): ?>
                <div class="alert success">✅ Votre commande a été envoyée avec succès !</div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert error">❌ Une erreur est survenue. Veuillez vérifier les informations et réessayer.</div>
            <?php endif; ?>
            <div id="panier-content">
                <?php if (empty($panier)): ?>
                    <p class="empty">Votre panier est vide.</p>
                <?php else: ?>
                    <form id="clear-cart-form" style="text-align: center;">
                        <button type="submit" class="btn btn-clear">🧹 Vider le panier</button>
                    </form>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Produit</th>
                                    <th>Quantité</th>
                                    <th>Prix Unitaire</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($panier as $index => $article):
                                    $total += $article['quantite'] * $article['prix'];
                                    ?>
                                    <tr data-index="<?= $index ?>">
                                        <td data-label="Produit"><?= htmlspecialchars($article['nom']) ?></td>
                                        <td data-label="Quantité"><?= $article['quantite'] ?></td>
                                        <td data-label="Prix Unitaire"><?= number_format($article['prix'], 2, ',', ' ') ?> €
                                        </td>
                                        <td data-label="Total">
                                            <?= number_format($article['quantite'] * $article['prix'], 2, ',', ' ') ?> €</td>
                                        <td data-label="Action">
                                            <form class="remove-item-form" data-index="<?= $index ?>">
                                                <button type="submit" class="btn btn-delete">🗑️ Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3">Total à payer</td>
                                    <td colspan="2"><?= number_format($total, 2, ',', ' ') ?> €</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <h2 style="text-align: center; margin-top: 3rem;">📩 Vos informations pour valider la commande</h2>
                    <form action="/includes/validation_commande.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <input type="text" name="website" style="display:none">
                        <input type="text" name="nom" placeholder="Votre nom" required>
                        <input type="text" name="prenom" placeholder="Votre prénom" required>
                        <input type="email" name="email" placeholder="Votre email" required>
                        <input type="tel" name="telephone" placeholder="Votre numéro de téléphone" required>
                        <textarea name="message" placeholder="Informations complémentaires (facultatif)"
                            rows="4"></textarea>
                        <button type="submit">Valider la commande</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <!-- Footer -->
    <?php
    includeFooter($contact, $partenaires);
    ?>
    <!-- Scripts -->
    <script src="/js/scroll.js" defer></script>
    <script src="/js/nav_img.js" defer></script>
    <script src="/js/modal_image_background_nav.js" defer></script>
    <script src="/js/menuburger.js" defer></script>
    <script src="/js/modal_gallery.js" defer></script>
    <script src="/js/slide-partenaire.js" defer></script>
    <script src="js/regex_form_panier.js" defer></script>
    <script type="module" src="/js/action_panier.js" defer></script>

</body>

</html>