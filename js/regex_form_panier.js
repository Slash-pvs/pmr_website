const form = document.querySelector('form[action="/includes/validation_commande.php"]');

if (form) {
    form.addEventListener("submit", function (e) {
        const nom = document.querySelector('input[name="nom"]').value.trim();
        const prenom = document.querySelector('input[name="prenom"]').value.trim();
        const email = document.querySelector('input[name="email"]').value.trim();
        const telephone = document.querySelector('input[name="telephone"]').value.trim();
        const message = document.querySelector('textarea[name="message"]').value.trim();

        const nomPrenomRegex = /^[A-Za-zÀ-ÖØ-öø-ÿ' -]{2,}$/;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const telRegex = /^(\+33|0)[1-9](\d{2}){4}$/;

        let erreur = "";

        if (!nomPrenomRegex.test(nom)) {
            erreur += "Nom invalide.\n";
        }
        if (!nomPrenomRegex.test(prenom)) {
            erreur += "Prénom invalide.\n";
        }
        if (!emailRegex.test(email)) {
            erreur += "Email invalide.\n";
        }
        if (!telRegex.test(telephone)) {
            erreur += "Téléphone invalide. Format attendu : 06XXXXXXXX ou +336XXXXXXXX\n";
        }

        if (erreur) {
            alert(erreur);
            e.preventDefault();
        }
    });
}

// Fonction d’échappement XSS (utile pour affichage plus tard)
function escapeHTML(str) {
    return str.replace(/[&<>"']/g, function (m) {
        return ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        })[m];
    });
}

  // Nettoyer l'URL après affichage du message
    const url = new URL(window.location.href);
    if (url.searchParams.get("success") || url.searchParams.get("error")) {
        setTimeout(() => {
            url.searchParams.delete("success");
            url.searchParams.delete("error");
            window.history.replaceState({}, document.title, url.pathname);
        }, 5000); // 5 secondes
    }