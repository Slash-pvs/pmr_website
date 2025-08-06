<!-- Liens pour charger les polices Google -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link
    href="https://fonts.googleapis.com/css2?family=Chivo+Mono:ital,wght@0,100..900;1,100..900&family=Rowdies:wght@700&display=swap"
    rel="stylesheet">

<!-- Lien vers le CSS du footer -->
<link rel="stylesheet" type="text/css" href="/public/css/footer.css">

<footer>
    <!-- Espace de séparation -->
    <div class="red_espace"></div>

    <!-- Contenu principal du footer -->
    <div class="carousel-container">
        <!-- Carrousel des partenaires -->
        <div class="carousel-container">
            <div class="slider-wrapper">
                <div class="slider-track">
                    <?php foreach ($partenaires as $partner): ?>
                        <div class="slide">
                            <?php if (!empty($partner['lien_site'])): ?>
                                <a href="<?= htmlspecialchars($partner['lien_site']) ?>" target="_blank"
                                    rel="noopener noreferrer">
                                <?php endif; ?>

                                <picture>
                                    <source media="(max-width: 480px)"
                                        srcset="<?= htmlspecialchars($partner['versions'][320] ?? $partner['versions'][1200]) ?>">
                                    <source media="(max-width: 1024px)"
                                        srcset="<?= htmlspecialchars($partner['versions'][768] ?? $partner['versions'][1200]) ?>">
                                    <img src="<?= htmlspecialchars($partner['versions'][1200]) ?>"
                                        alt="<?= htmlspecialchars($partner['nom_fichier']) ?>" loading="lazy">
                                </picture>

                                <?php if (!empty($partner['lien_site'])): ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Boutons de navigation -->
                <button class="prev">Précédent</button>
                <button class="next">Suivant</button>
            </div>
        </div>
    </div>


    <hr>
    <div class="div_footer">
        <!-- Logo PMR -->
        <picture>
            <source media="(max-width: 800px)" srcset="/public/img/x320/logo_pmr_rond_320.webp">
            <source media="(max-width: 1024px)" srcset="/public/img/x768/logo_pmr_rond_768.webp">
            <img class="img_footer" src="/public/img/x1200/logo_pmr_rond_1200.webp" alt="Logo Pays Médoc Rugby">
        </picture>
        <!-- Informations générales et RGPD -->
        <div>
            <h1>Pays Médoc Rugby</h1>
            <h2>Site officiel</h2>
            <div>
                <a class="no_shadow" href="/public/contact.php">Contactez-nous</a>
                <br>
                <a class="no_shadow" href="/public/politique_conf.php">Politique de confidentialité</a>
            </div>
        </div>

        <!-- Informations de contact -->
        <?php if ($contact): ?>
            <div class="contact-wrapper">
                <div class="contact-row">
                    <div class="contact-label">Adresse :</div>
                    <div class="contact-value"><?= htmlspecialchars($contact['lieu']) ?></div>
                </div>
                <div class="contact-row">
                    <div class="contact-label">Téléphone :</div>
                    <div class="contact-value"><?= htmlspecialchars($contact['numero_tel']) ?></div>
                </div>
                <div class="contact-row">
                    <div class="contact-label">Email :</div>
                    <div class="contact-value">
                        <a href="mailto:<?= htmlspecialchars($contact['email']) ?>">
                            <?= htmlspecialchars($contact['email']) ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <p>Informations de contact indisponibles.</p>
        <?php endif; ?>

    </div>
</footer>