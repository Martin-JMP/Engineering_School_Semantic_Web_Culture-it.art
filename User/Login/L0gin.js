document.addEventListener("DOMContentLoaded", function() {
    const loginForm = document.getElementById('login-form');
    
    // Récupérer l'email de l'URL
    const params = new URLSearchParams(window.location.search);
    const email = params.get('email');

    if (!email) {
        alert("Email not found in URL");
        window.location.href = "../Signup/Signup.html";
        return;
    }

    loginForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const password = document.getElementById('password-input').value;

        if (password) {
            fetch('L0gin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
            })
            .then(response => response.text())  // On récupère la réponse en tant que texte pour debug
            .then(data => {
                console.log('Raw response from server:', data); // Afficher la réponse brute
                try {
                    const jsonData = JSON.parse(data); // On essaie de parser en JSON
                    if (jsonData.success) {
                        window.location.href = "../../index.html";
                    } else {
                        alert(jsonData.error || "Invalid password");
                    }
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    alert("There was an issue with the server response.");
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        } else {
            alert("Please enter your password");
        }
    });
});
