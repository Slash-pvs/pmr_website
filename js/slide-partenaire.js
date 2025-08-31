document.addEventListener("DOMContentLoaded", function() {
    const slider = document.querySelector(".partner-slider");
    const prev = document.querySelector(".prev");
    const next = document.querySelector(".next");
    let index = 0;

    function updateSlider() {
        const slideWidth = slider.querySelector(".slide").offsetWidth + 20; // slide width + margin
        slider.style.transform = `translateX(${-index * slideWidth}px)`;
    }

    next.addEventListener("click", () => {
        if (index < slider.children.length - 1) {
            index++;
            updateSlider();
        }
    });

    prev.addEventListener("click", () => {
        if (index > 0) {
            index--;
            updateSlider();
        }
    });

    // Optionnel : auto-scroll toutes les 5 secondes
    setInterval(() => {
        index = (index + 1) % slider.children.length;
        updateSlider();
    }, 5000);
});
