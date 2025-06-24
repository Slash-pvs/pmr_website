document.addEventListener('DOMContentLoaded', function () {
    const slider = document.querySelector('.partner-slider');
    const track = document.querySelector('.slider-track');
    const slides = Array.from(track.children);
    const prevButton = document.querySelector('.prev');
    const nextButton = document.querySelector('.next');

    let slideWidth = slides[0].getBoundingClientRect().width;
    let currentIndex = 0;
    let autoSlideInterval;

    // Fonction pour mettre à jour la position du carrousel
    function updateSliderPosition() {
        track.style.transition = "transform 0.5s ease";
        track.style.transform = `translateX(${-slideWidth * currentIndex}px)`;
    }

    // Fonction pour aller à la slide suivante
    function goToNextSlide() {
        currentIndex++;
        if (currentIndex >= slides.length) {
            currentIndex = 0;
        }
        updateSliderPosition();
    }

    // Fonction pour aller à la slide précédente
    function goToPrevSlide() {
        currentIndex--;
        if (currentIndex < 0) {
            currentIndex = slides.length - 1;
        }
        updateSliderPosition();
    }

    // Désactivation temporaire des boutons pour éviter les clics répétés
    function disableButtonTemporarily(button) {
        button.disabled = true;
        setTimeout(() => button.disabled = false, 500);
    }

    // Arrêter l'intervalle automatique
    function stopAutoSlide() {
        clearInterval(autoSlideInterval);
    }

    // Lancer l'intervalle automatique
    function startAutoSlide() {
        autoSlideInterval = setInterval(goToNextSlide, 3000);
    }

    // Clic sur le bouton "Précédent"
    prevButton.addEventListener('click', () => {
        disableButtonTemporarily(prevButton);
        stopAutoSlide();
        goToPrevSlide();
        startAutoSlide();
    });

    // Clic sur le bouton "Suivant"
    nextButton.addEventListener('click', () => {
        disableButtonTemporarily(nextButton);
        stopAutoSlide();
        goToNextSlide();
        startAutoSlide();
    });

    // Réajuster la taille du carrousel lors du redimensionnement de la fenêtre
    window.addEventListener('resize', () => {
        slideWidth = slides[0].getBoundingClientRect().width;
        updateSliderPosition();
    });

    // Lancer l'intervalle de défilement automatique
    startAutoSlide();

    // 🔗 Gestion des clics sur les images cliquables
    const images = document.querySelectorAll(".partner-slider img[data-lien]");

    images.forEach(img => {
        img.addEventListener("click", function () {
            const url = img.getAttribute("data-lien");
            if (url) {
                window.open(url, "_blank", "noopener");
            }
        });
    });
});
