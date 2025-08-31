<footer>
    <div class="red_espace"></div>
    <?php
    // $partenaires doit être passé depuis le fichier principal
    if (!isset($partenaires))
        $partenaires = [];
    ?>

    <div class="carousel-container">
        <div class="slider-wrapper">
            <div class="partner-slider">
                <?php foreach ($partenaires as $p):
                    $imgSrc = $p['versions'][1200] ?? $p['logo'] ?? '/img/placeholder-partenaire.png';
                    if (!file_exists(__DIR__ . $imgSrc)) {
                        $imgSrc = '/img/placeholder-partenaire.png';
                    }
                    $hasLink = !empty($p['site']);
                    ?>
                    <div class="slide">
                        <?php if ($hasLink): ?>
                            <a href="<?= htmlspecialchars($p['site']) ?>" target="_blank" rel="noopener noreferrer">
                            <?php endif; ?>
                            <picture>
                                <?php if (!empty($p['versions'][320])): ?>
                                    <source media="(max-width:480px)" srcset="<?= htmlspecialchars($p['versions'][320]) ?>">
                                <?php endif; ?>
                                <?php if (!empty($p['versions'][768])): ?>
                                    <source media="(max-width:1024px)" srcset="<?= htmlspecialchars($p['versions'][768]) ?>">
                                <?php endif; ?>
                                <img src="<?= htmlspecialchars($imgSrc) ?>"
                                    alt="<?= htmlspecialchars($p['nom'] ?? 'Logo partenaire') ?>">
                            </picture>
                            <?php if ($hasLink): ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <button class="prev" aria-label="Précédent">‹</button>
        <button class="next" aria-label="Suivant">›</button>
    </div>

    <hr>
    <div class="div_footer">
        <!-- Logo PMR -->
        <picture>
            <source media="(max-width: 800px)" srcset="/img/x320/logo_pmr_rond_320.webp">
            <source media="(max-width: 1024px)" srcset="/img/x768/logo_pmr_rond_768.webp">
            <img class="img_footer" src="/img/x1200/logo_pmr_rond_1200.webp" alt="Logo Pays Médoc Rugby" loading="lazy"
                decoding="async">
        </picture>

        <div>
            <h1>Pays Médoc Rugby</h1>
            <h2>Site officiel</h2>
            <div>
                <a class="no_shadow" href="/contact.php">Contactez-nous</a><br>
                <a class="no_shadow" href="/politique_conf.php">Politique de confidentialité</a>
            </div>
        </div>

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