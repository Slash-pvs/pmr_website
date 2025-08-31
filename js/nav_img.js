document.addEventListener("DOMContentLoaded", function () {
    // Récupérer l'élément myDiv et ses images via l'attribut data-images
    const myDiv = document.getElementById("myDiv");
    if (!myDiv) {
        console.error("L'élément myDiv est introuvable !");
        return;
    }

    const images = JSON.parse(myDiv.getAttribute("data-images"));
    
    // Vérification si images est défini et non vide
    if (!images || images.length === 0) {
        console.error("Aucune image trouvée !");
        return;
    }

    // Variable pour l'index de l'image
    let index = 0;

    // Fonction pour changer l'image de fond
    function updateBackground() {
        if (images[index]) {
            myDiv.style.backgroundImage = url('${images[index]}');
        } else {
            console.error("L'image à l'index " + index + " n'existe pas");
        }
        index = (index + 1) % images.length;  // Passe à l'image suivante
    }

    // Appliquer immédiatement une image
    updateBackground();  

    // Changer l'image de fond toutes les 30 secondes
    setInterval(updateBackground, 30000);  
});