(function() {
    const title = document.title || 'accueil';

    // Fonction pour créer un slug sûr et propre
    function slugify(text) {
        return text.toString().toLowerCase()
            .normalize('NFD')                   // Sépare les accents
            .replace(/[\u0300-\u036f]/g, '')    // Enlève les accents
            .replace(/[^a-z0-9]+/g, '-')        // Remplace les groupes de non-alphanum par "-"
            .replace(/^-+|-+$/g, '')             // Enlève tirets début/fin
            .replace(/-{2,}/g, '-');             // Remplace multiples tirets par un seul
    }

    const slug = slugify(title);

    if (slug && window.history && history.replaceState) {
        // Change l'URL sans recharger la page
        history.replaceState(null, '', '/' + slug);
    }
})();
