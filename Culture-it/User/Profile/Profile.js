// Fonction pour récupérer les informations utilisateur via AJAX
function fetchUserProfile() {
    fetch('Profile.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
            } else {
                // Vérification des éléments DOM avant d'y insérer des données
                const pseudonymeElement = document.getElementById('pseudonyme');
                const emailElement = document.getElementById('email');
                const birthElement = document.getElementById('birth');
                const createdAtElement = document.getElementById('created_at');
                const artworksContainer = document.getElementById('artworks_container');
                const artworksMainSection = document.getElementById('artworks_main_section');

                // Vérifier si les éléments existent dans le DOM
                if (pseudonymeElement && emailElement && createdAtElement && artworksContainer && artworksMainSection) {
                    // Affichage des informations utilisateur
                    pseudonymeElement.textContent = data.pseudonyme;
                    emailElement.textContent = data.email;
                    birthElement.textContent = data.birth;
                    createdAtElement.textContent = data.created_at;

                    // Afficher ou cacher la section des œuvres d'art en fonction du statut d'artiste
                    if (data.is_artist) {
                        artworksMainSection.classList.remove('hidden');
                        // Affichage des œuvres d'art de l'utilisateur avec bouton de suppression
                        if (data.artworks.length > 0) {
                            document.querySelector('.artworks-title').classList.remove('hidden');
                            data.artworks.forEach(artwork => {
                                const artworkElement = document.createElement('div');
                                artworkElement.classList.add('artwork');
                                artworkElement.id = `artwork_${artwork.id}`;

                                const deleteButton = document.createElement('button');
                                deleteButton.textContent = '✖';
                                deleteButton.classList.add('delete-button');
                                deleteButton.onclick = () => deleteArtwork(artwork.id, artwork.image_url);

                                const artworkTitle = document.createElement('h4');
                                artworkTitle.textContent = artwork.title;

                                const artworkImage = document.createElement('img');
                                artworkImage.src = `../../Upload/${artwork.image_url}`;
                                artworkImage.alt = artwork.title;
                                artworkImage.onclick = () => {
                                    window.location.href = `../../Post/Post.html?ObjectId=${artwork.object_id}&id=${artwork.id}`;
                                };

                                artworkElement.appendChild(deleteButton);
                                artworkElement.appendChild(artworkTitle);
                                artworkElement.appendChild(artworkImage);
                                artworksContainer.appendChild(artworkElement);
                            });
                        } else {
                            document.querySelector('.artworks-title').classList.add('hidden');
                        }
                    } else {
                        artworksMainSection.classList.add('hidden');
                    }
                } else {
                    console.error('Required DOM elements are missing.');
                }
            }
        })
        .catch(error => console.error('Error fetching user profile:', error));
}

// Fonction pour supprimer une œuvre d'art
function deleteArtwork(artworkId, imageUrl) {
    fetch('DeleteArtwork.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            artwork_id: artworkId,
            image_url: imageUrl
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Supprimer l'élément de l'œuvre d'art du DOM
            document.getElementById(`artwork_${artworkId}`).remove();
            // Hide the "Your Artworks" title if no artworks remain
            if (document.getElementById('artworks_container').children.length === 0) {
                document.querySelector('.artworks-title').classList.add('hidden');
            }
        } else {
            alert("Error deleting artwork.");
        }
    })
    .catch(error => console.error('Error deleting artwork:', error));
}

// Charger les informations utilisateur et gérer les événements après le chargement complet du DOM
window.onload = function() {
    // Charger les informations utilisateur
    fetchUserProfile();

    // Gérer l'affichage des champs de changement de mot de passe après le chargement de la page
    const passwordButton = document.getElementById('change_password_btn');
    const passwordForm = document.getElementById('password_form_hide');
    const submitPasswordButton = document.getElementById('submit_password_change');

    if (passwordButton && passwordForm && submitPasswordButton) {
        // Ajouter l'événement click pour afficher le formulaire de changement de mot de passe
        passwordButton.addEventListener('click', function() {
            passwordForm.classList.remove('hidden');
            passwordForm.classList.add('visible');

            // Désactiver le bouton une fois cliqué
            passwordButton.textContent = "Change Password";
            passwordButton.style.cursor = "default";
            passwordButton.setAttribute("id", "change_password_btn_title"); // Désactiver les clics
        });

        // Gérer le changement de mot de passe
        submitPasswordButton.addEventListener('click', function() {
            const currentPassword = document.getElementById('current_password').value.trim();
            const newPassword = document.getElementById('new_password').value.trim();
            const confirmNewPassword = document.getElementById('confirm_new_password').value.trim();

            // Vérifier que tous les champs sont remplis
            if (!currentPassword || !newPassword || !confirmNewPassword) {
                alert("Please fill in all the password fields.");
                return;
            }

            // Vérifier que le nouveau mot de passe et la confirmation correspondent
            if (newPassword !== confirmNewPassword) {
                alert("New password and confirmation do not match.");
                return;
            }

            // Envoyer la requête pour changer le mot de passe
            fetch('ChangePassword.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    current_password: currentPassword,
                    new_password: newPassword
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Password changed successfully.");
                } else {
                    alert("Error: " + data.error);
                }
            })
            .catch(error => console.error('Error changing password:', error));
        });
    } else {
        console.error("Password elements are missing in the DOM.");
    }

    // Gérer la déconnexion
    const logoutButton = document.getElementById('logout_btn');
    if (logoutButton) {
        logoutButton.addEventListener('click', function() {
            fetch('Logout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Rediriger vers la page de connexion après la déconnexion
                    window.location.href = '../Login/Login.html';
                } else {
                    alert("Error logging out.");
                }
            })
            .catch(error => console.error('Error logging out:', error));
        });
    } else {
        console.error("Logout button is missing in the DOM.");
    }
};
