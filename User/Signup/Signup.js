document.querySelector('.submit-button').addEventListener('click', function(event) {
    event.preventDefault();  // Empêche le comportement par défaut du formulaire

    // Récupérer les données du formulaire
    const pseudonyme = document.querySelector('input[placeholder="Pseudonyme"]').value;
    const email = document.querySelector('input[placeholder="Email"]').value;
    const birth = document.querySelector('input[placeholder="Date of birth"]').value;
    const password = document.querySelector('input[placeholder="Password"]').value;
    const confirmPassword = document.querySelector('input[placeholder="Confirmation Password"]').value;
    const cguChecked = document.querySelector('#CGUBox').checked;

    // Vérifier si les CGU sont acceptées
    if (!cguChecked) {
        alert("You must accept the CGU to proceed.");
        return;
    }

    // Préparer les données à envoyer
    const formData = new FormData();
    formData.append('pseudonyme', pseudonyme);
    formData.append('email', email);
    formData.append('birth', birth);
    formData.append('password', password);
    formData.append('confirm_password', confirmPassword);

    // Envoyer les données avec fetch
    fetch('Signup.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);  // Affiche un message de succès
            // Redirection ou autre traitement après inscription
            window.location.href = "../Login/Login.html";
        } else {
            alert(data.message);  // Affiche un message d'erreur
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});
