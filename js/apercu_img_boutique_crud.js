document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('button.apply-to-all');

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const formatClicked = btn.dataset.format;
            const selectClicked = document.getElementById(`version_${formatClicked}`);
            const selectedValue = selectClicked.value;

            if (!selectedValue) { alert("Veuillez choisir une image."); return; }

            const regex = /^(.*)_(\d+)(\.\w+)$/;
            const match = selectedValue.match(regex);
            if (!match) { alert("Nom de fichier invalide."); return; }

            const base = match[1], ext = match[3];

            document.querySelectorAll('select[name^="versions"]').forEach(sel => {
                const f = sel.id.replace('version_','');
                if (f !== formatClicked) {
                    const newFile = `${base}_${f}${ext}`;
                    let exists = Array.from(sel.options).some(o => o.value === newFile);
                    sel.value = exists ? newFile : "";
                    const preview = document.getElementById(`preview_${f}`);
                    if(preview) { 
                        preview.src = exists ? `/img/boutique/x${f}/${newFile}` : '';
                        preview.style.display = exists ? 'inline-block':'none';
                    }
                }
            });

            const previewClicked = document.getElementById(`preview_${formatClicked}`);
            if (previewClicked) {
                previewClicked.src = `/img/boutique/x${formatClicked}/${selectedValue}`;
                previewClicked.style.display = 'inline-block';
            }
        });
    });

    // Mettre à jour l'aperçu à chaque changement de select
    document.querySelectorAll('select[name^="versions"]').forEach(sel => {
        sel.addEventListener('change', () => {
            const f = sel.id.replace('version_','');
            const preview = document.getElementById(`preview_${f}`);
            if(preview) {
                if(sel.value) {
                    preview.src = `/img/boutique/x${f}/${sel.value}`;
                    preview.style.display='inline-block';
                } else preview.style.display='none';
            }
        });
    });
});