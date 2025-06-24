document.addEventListener('DOMContentLoaded', function () {
    const burgerMenu = document.getElementById('burgerMenu');
    const mobileMenu = document.getElementById('mobileMenu');

    if (burgerMenu && mobileMenu) {
        burgerMenu.addEventListener('click', function () {
            mobileMenu.classList.toggle('hidden');
        });

        // Optionnel : fermer le menu si on clique en dehors
        document.addEventListener('click', function (e) {
            if (!burgerMenu.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.add('hidden');
            }
        });
    }
});
