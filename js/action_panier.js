document.addEventListener('DOMContentLoaded', () => {

    const updateTotal = (panier) => {
        let total = 0;
        panier.forEach(article => {
            total += article.quantite * article.prix;
        });

        const totalCell = document.querySelector('tfoot td[colspan="2"]');
        if (totalCell) {
            totalCell.textContent = total.toFixed(2).replace('.', ',') + ' €';
        }
    };

    // Supprimer un article
    document.querySelectorAll('.remove-item-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const index = this.dataset.index;

            fetch('/includes/panier_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=remove&index=${index}`
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Supprimer la ligne du tableau
                    const row = document.querySelector(`tr[data-index="${index}"]`);
                    if (row) row.remove();

                    // Mettre à jour le total
                    updateTotal(data.panier);

                    // Si le panier est vide
                    if (data.panier.length === 0) {
                        document.getElementById('panier-content').innerHTML = '<p class="empty">Votre panier est vide.</p>';
                    }
                } else {
                    alert(data.error || 'Erreur lors de la suppression de l\'article.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erreur AJAX.');
            });
        });
    });

    // Vider le panier
    const clearForm = document.getElementById('clear-cart-form');
    if (clearForm) {
        clearForm.addEventListener('submit', function (e) {
            e.preventDefault();

            fetch('/includes/panier_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=clear'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('panier-content').innerHTML = '<p class="empty">Votre panier est vide.</p>';
                } else {
                    alert(data.error || 'Erreur lors du vidage du panier.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Erreur AJAX.');
            });
        });
    }
});
