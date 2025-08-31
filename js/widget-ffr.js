window.addEventListener('load', function () {
  // Ajouter ton propre fichier CSS (pour personnaliser les widgets)
  const link = document.createElement('link');
  link.rel = 'stylesheet';
  link.href = '/css/widget-ffr.css'; // Ton fichier CSS local
  document.head.appendChild(link);

  // Charger le script du widget FFR
  const script = document.createElement('script');
  script.src = 'https://widget.club.ffr.fr/static/js/main.js'; // Script officiel du widget
  script.defer = true;

  script.onload = function () {
    console.log('Script du widget FFR chargé');

    // Attendre que le widget s'affiche pour ajuster .slick-slider
    setTimeout(() => {
      const sliders = document.querySelectorAll('.slick-slider');
      if (sliders.length > 0) {
        sliders.forEach(slider => {
          slider.style.width = '100%';
          slider.style.maxWidth = '100%';
          slider.style.boxSizing = 'border-box';
        });
        console.log(`${sliders.length} .slick-slider(s) ajusté(s) avec succès.`);
      } else {
        console.warn('Aucun .slick-slider trouvé après le chargement du widget.');
      }

      // Recalcule du slider si Slick (jQuery) est dispo
      if (window.jQuery && typeof jQuery('.slick-slider').slick === 'function') {
        jQuery('.slick-slider').slick('setPosition');
        console.log('Slick repositionné via jQuery.');
      }

    }, 500); // Attendre 500ms pour garantir que le DOM du widget est prêt
  };

  script.onerror = function (error) {
    console.error('Erreur lors du chargement du script du widget FFR', error);
  };

  document.body.appendChild(script);
});
