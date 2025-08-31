document.addEventListener('DOMContentLoaded', () => {
    const selectImage = document.getElementById('image_path');
    const previewImage = document.getElementById('previewImage');

    // Formats et tailles utilisées (doivent correspondre aux inputs)
    const formats = [
        {format: 'x320', size: 320},
        {format: 'x768', size: 768},
        {format: 'x1200', size: 1200}
    ];

    // Récupère tous les inputs chemin des versions
    const pathInputs = document.querySelectorAll('input.version-path');

    function updateVersions(selectedImage) {
        if (!selectedImage) {
            // Cacher l'aperçu si rien sélectionné
            previewImage.style.display = 'none';
            previewImage.src = '';
            // Vide les chemins versions
            pathInputs.forEach(input => input.value = '');
            return;
        }

        // Affiche l'aperçu de l'image originale
        previewImage.style.display = 'block';
        previewImage.src = `/img/image_nav/${selectedImage}`;

        // Met à jour les chemins pour chaque version
        formats.forEach((fmt, idx) => {
            if (pathInputs[idx]) {
                pathInputs[idx].value = `/img/image_nav/${fmt.format}/${selectedImage}`;
            }
        });
    }

    // Initialisation à la charge si une image est déjà sélectionnée
    updateVersions(selectImage.value);

    // Écoute le changement de sélection
    selectImage.addEventListener('change', (e) => {
        updateVersions(e.target.value);
    });
});