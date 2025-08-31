// Récupère l'élément de la navbar
const navbar = document.getElementById('navContainer');

// Fonction pour ajouter ou supprimer la classe 'fixed-nav' lors du scroll
window.onscroll = function() {
    if (window.scrollY > 0) {  // Si l'utilisateur a fait défiler la page
        navbar.classList.add('fixed-nav');  // Ajoute la classe 'fixed-nav' pour la fixer en haut
    } else {
        navbar.classList.remove('fixed-nav');  // Retire la classe si l'utilisateur est en haut de la page
    }
};
