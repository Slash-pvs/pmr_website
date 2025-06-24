document.addEventListener("DOMContentLoaded", () => {
    const modal = document.getElementById("imageModal");
    const modalImg = document.getElementById("modalImage");
    const captionText = document.getElementById("caption");
    const closeBtn = document.querySelector(".close");
    const navBar = document.getElementById("navBar");

    const jsonScript = document.getElementById("imageData");
    let imageVersions = [];

    try {
        imageVersions = JSON.parse(jsonScript.textContent);
    } catch (e) {
        console.error("Erreur lors du parsing JSON des versions d'image :", e);
    }

    function getBestImagePath() {
        const width = window.innerWidth;
        for (let i = imageVersions.length - 1; i >= 0; i--) {
            if (width >= imageVersions[i].size) {
                return imageVersions[i].path;
            }
        }
        return imageVersions[0]?.path || "";
    }

    function openModal() {
        modal.style.display = "flex";
        modal.style.zIndex = "999";
        modal.setAttribute("aria-hidden", "false");

        modalImg.src = getBestImagePath();
        captionText.textContent = "Aperçu de l'image";
    }

    function closeModal() {
        modal.style.display = "none";
        modalImg.src = "";
    }

    if (closeBtn) {
        closeBtn.addEventListener("click", closeModal);
    }

    modal.addEventListener("click", (e) => {
        if (e.target === modal) closeModal();
    });

    modalImg.addEventListener("click", (e) => e.stopPropagation());

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeModal();
    });

    if (navBar) {
        navBar.addEventListener("dblclick", openModal);
    }
});
