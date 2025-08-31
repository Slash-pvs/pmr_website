document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    if (!form) return;

    form.addEventListener('submit', function(event) {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;

        let errorMessages = [];

        if (!emailRegex.test(email)) {
            errorMessages.push("L'adresse e-mail n'est pas valide.");
        }

        if (!passwordRegex.test(password)) {
            errorMessages.push("Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre.");
        }

        if (errorMessages.length > 0) {
            alert(errorMessages.join("\n"));
            event.preventDefault();
        }
    });
});
