document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("imageModal");
    const modalImg = document.getElementById("modalImage");
    const captionText = document.getElementById("caption");
    const closeBtn = document.querySelector(".close");
    const galleryImages = document.querySelectorAll(".gallerie-img");

    galleryImages.forEach(function (img) {
        img.addEventListener("click", function () {
            modal.style.display = "flex";
            modalImg.src = this.src;
            captionText.textContent = this.alt || "Aperçu de l'image";
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener("click", function () {
            modal.style.display = "none";
        });
    }

    modal.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    modal.style.display = "none";
});
