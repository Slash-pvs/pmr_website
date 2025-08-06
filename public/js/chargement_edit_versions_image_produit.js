document.addEventListener('DOMContentLoaded', () => {
  console.log('Script chargement_edit_versions_image_produit.js chargé');

  const buttons = document.querySelectorAll('button.apply-to-all');

  if (!buttons.length) {
    console.warn('Aucun bouton apply-to-all trouvé');
    return;
  }

  buttons.forEach(button => {
    button.addEventListener('click', () => {
      const formatClicked = button.dataset.format;
      console.log('Bouton cliqué pour format :', formatClicked);

      const selectClicked = document.getElementById(`version_${formatClicked}`);
      if (!selectClicked) {
        console.warn(`Select non trouvé pour format ${formatClicked}`);
        return;
      }

      const selectedValue = selectClicked.value;
      console.log('Image sélectionnée dans ce format :', selectedValue);

      if (!selectedValue) {
        alert("Veuillez choisir une image avant d'appliquer à tous les formats.");
        return;
      }

      // Extraction base nom et extension
      const regex = /^(.*)_(\d+)(\.\w+)$/;
      const match = selectedValue.match(regex);

      if (!match) {
        alert("Le format du nom du fichier ne correspond pas au pattern attendu (ex: nom_320.webp).");
        console.warn('Regex non matché pour', selectedValue);
        return;
      }

      const baseName = match[1];
      const extension = match[3];

      // Parcourir tous les selects versions
      document.querySelectorAll('select[name^="versions"]').forEach(select => {
        const currentFormat = select.id.replace('version_', '');
        if (select.id !== `version_${formatClicked}`) {
          const newFileName = `${baseName}_${currentFormat}${extension}`;
          let optionExists = false;

          for (let i = 0; i < select.options.length; i++) {
            if (select.options[i].value === newFileName) {
              optionExists = true;
              break;
            }
          }

          if (optionExists) {
            select.value = newFileName;
            // Mettre à jour l'aperçu si tu en as un, sinon ignorer
            const preview = document.getElementById(`preview_${currentFormat}`);
            if (preview) {
              preview.src = `/img/boutique/x${currentFormat}/${newFileName}`;
              preview.style.display = 'inline-block';
            }
          } else {
            select.value = "";
            const preview = document.getElementById(`preview_${currentFormat}`);
            if (preview) {
              preview.style.display = 'none';
            }
          }
        }
      });

      // Mettre à jour le preview du format cliqué aussi (au cas où)
      const previewClicked = document.getElementById(`preview_${formatClicked}`);
      if (previewClicked) {
        previewClicked.src = `/img/boutique/x${formatClicked}/${selectedValue}`;
        previewClicked.style.display = 'inline-block';
      }
    });
  });
});
