document.addEventListener("DOMContentLoaded", function() {
    // Effectuer une requête AJAX pour vérifier si l'utilisateur est connecté
    fetch('check_session.php')
    .then(response => response.json())
    .then(data => {
        const userSection = document.getElementById('user-section');
        if (data.logged_in) {
            // Vérifier si l'utilisateur est sur la page de profil
            const currentPage = window.location.pathname.split('/').pop();  // Obtient le nom du fichier actuel

            if (currentPage === "Profile.html") {
                // Si on est sur la page du profil, afficher le prénom sans lien cliquable
                userSection.innerHTML = `<a class="nav-link" data-name="profile">${data.first_name}</a>`;
            } else {
                // Pour les autres pages, afficher le prénom avec le lien cliquable vers le profil
                userSection.innerHTML = `<a href="../User/Profile/Profile.html" class="nav-link" data-name="profile">${data.first_name}</a>`;
            }
        }
    })
    .catch(error => {
        console.error('Erreur lors de la vérification de la session:', error);
    });
});
