    document.addEventListener('DOMContentLoaded', () => {
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
                        document.querySelector(`tr[data-index="${index}"]`).remove();

                        // Si le panier est vide
                        if (data.panier.length === 0) {
                            document.getElementById('panier-content').innerHTML = '<p class="empty">Votre panier est vide.</p>';
                        }
                    }
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
                    }
                });
            });
        }
    });
