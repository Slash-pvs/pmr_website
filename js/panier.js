document.addEventListener("DOMContentLoaded", () => {
    const forms = document.querySelectorAll(".form-ajout-panier");
    const countDisplay = document.getElementById("panier-count");

    if (!countDisplay) {
        console.error("Élément #panier-count introuvable.");
        return;
    }

    forms.forEach(form => {
        form.addEventListener("submit", async e => {
            e.preventDefault();

            const formData = new FormData(form);

            try {
                const response = await fetch("/includes/ajouter_panier.php", {
                    method: "POST",
                    body: formData
                });

                const result = await response.json();

                if (result.success && typeof result.total !== "undefined") {
                    countDisplay.textContent = result.total;
                } else {
                    console.error("Réponse invalide du serveur :", result);
                }
            } catch (err) {
                console.error("Erreur AJAX :", err);
            }
        });
    });
});
