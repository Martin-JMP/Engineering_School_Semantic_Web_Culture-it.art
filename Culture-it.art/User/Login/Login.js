document.addEventListener("DOMContentLoaded", function() {
    const loginForm = document.getElementById('login-form');
    
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();  // Empêche la soumission par défaut du formulaire
            
            const email = document.getElementById('email-input').value;
            
            // Vérification si un email est entré
            if (email) {
                // Envoyer l'email via une requête POST à un script PHP
                fetch('Login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `email=${encodeURIComponent(email)}`
                })
                .then(response => response.json())
                .then(data => {
                    // Vérifie si une réponse existe
                    console.log('Response:', data);
                    if (data.exists) {
                        // Rediriger vers la page de saisie du mot de passe
                        window.location.href = "L0gin.html?email=" + encodeURIComponent(email);
                    } else {
                        // Rediriger vers la page d'inscription
                        window.location.href = "../Signup/Signup.html";
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la requête:', error);
                });
            } else {
                alert("Veuillez entrer un email valide");
            }
        });
    }
});
