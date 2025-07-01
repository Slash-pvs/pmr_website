document.addEventListener('DOMContentLoaded', () => {
    const showMessage = (text) => {
        const messageBox = document.getElementById('image-message');
        messageBox.textContent = text;
        messageBox.classList.add('show');
        messageBox.style.display = 'block';

        setTimeout(() => {
            messageBox.classList.remove('show');
            setTimeout(() => messageBox.style.display = 'none', 400);
        }, 2000);
    };

    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', async (e) => {
            const card = e.target.closest('.image-card');
            const imagePaths = JSON.parse(card.dataset.imagePaths); // tableau des chemins relatifs

            if (!confirm('Supprimer cette image ?')) return;

            try {
                const response = await fetch('/includes/delete_image.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ image_paths: imagePaths })  // envoyer un tableau
                });

                const result = await response.json();

                if (result.success) {
                    card.classList.add('removing');
                    setTimeout(() => card.remove(), 300);
                    showMessage("✅ Image supprimée");
                } else {
                    alert('Erreur : ' + result.error);
                }
            } catch (err) {
                console.error('Erreur AJAX', err);
                alert('Erreur AJAX');
            }
        });
    });
});
