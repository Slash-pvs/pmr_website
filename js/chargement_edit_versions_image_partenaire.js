console.log('Script chargé');

const buttons = document.querySelectorAll('button.apply-to-all');

buttons.forEach(button => {
  button.addEventListener('click', () => {
    console.log('Bouton cliqué pour format :', button.dataset.format);

    const formatClicked = button.dataset.format;
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

    const regex = /^(.*)_(\d+)(\.\w+)$/;
    const match = selectedValue.match(regex);
    if (!match) {
      alert("Le format du nom du fichier ne correspond pas au pattern attendu (ex: nom_320.webp).");
      console.warn('Regex non matché pour', selectedValue);
      return;
    }

    const baseName = match[1];
    const extension = match[3];

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
          const preview = document.getElementById(`preview_${currentFormat}`);
          if (preview) {
            preview.src = `/img/partenaire/x${currentFormat}/${newFileName}`;
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

    const previewClicked = document.getElementById(`preview_${formatClicked}`);
    if (previewClicked) {
      previewClicked.src = `/img/partenaire/x${formatClicked}/${selectedValue}`;
      previewClicked.style.display = 'inline-block';
    }
  });
});
