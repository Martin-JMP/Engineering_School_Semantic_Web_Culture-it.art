document.addEventListener("DOMContentLoaded", () => {
    const ObjectId = 203; // ID de "La Joconde". Remplacez-le pour une autre œuvre.

    // Vérifier si l'URL contient déjà le paramètre
    const urlParams = new URLSearchParams(window.location.search);
    const currentObjectId = urlParams.get('ObjectId');

    // Ne mettre à jour l'URL que si nécessaire
    if (!currentObjectId) {
        // Utiliser history.pushState pour modifier l'URL sans recharger la page
        const newUrl = `https://www.culture-it.art/Post/Post.html?ObjectId=${ObjectId}`;
        history.pushState({ ObjectId: ObjectId }, '', newUrl);
    }

    fetchArtworkData(ObjectId);
    fetchComments(ObjectId);

    const btn = document.getElementById("btn");
    if (btn) {
        btn.addEventListener("click", async function() {
            const commentInput = document.getElementById("comment-input");
            if (!commentInput) return;

            const commentText = commentInput.value;
            if (!commentText) return;

            const userProfile = await fetchUserProfile();
            if (!userProfile) return;

            const userName = userProfile.pseudonyme;
            const commentDate = new Date().toISOString().slice(0, 19).replace('T', ' '); // Format MySQL DATETIME

            try {
                const response = await fetch('save_comment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        userName,
                        commentText,
                        commentDate,
                        objectId: ObjectId
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                if (result.success) {
                    fetchComments(ObjectId);
                } else {
                    console.error("Erreur lors de l'enregistrement du commentaire:", result.error);
                }
            } catch (error) {
                console.error("Erreur lors de l'enregistrement du commentaire:", error);
            }
        });
    }
});

async function fetchUserProfile() {
    try {
        const response = await fetch('check_session.php');
        const data = await response.json();
        if (data.logged_in) {
            return data;
        } else {
            window.location.href = '../../User/Login/Login.html';
        }
    } catch (error) {
        console.error('Error fetching user profile:', error);
        return null;
    }
}

async function fetchArtworkData(id) {
    try {
        const response = await fetch(`https://collectionapi.metmuseum.org/public/collection/v1/objects/${id}`);
        if (!response.ok) throw new Error("Erreur lors de la récupération des données");

        const data = await response.json();
        
        // Remplissage des informations sur l'artiste
        const artistNameElement = document.getElementById("artist-name");
        if (artistNameElement) artistNameElement.textContent = data.artistDisplayName || "Unknown Artist";

        const artistNationalityElement = document.getElementById("artist-nationality");
        if (artistNationalityElement) artistNationalityElement.textContent = data.artistNationality || "Unknown Nationality";

        const artistBirthDeathElement = document.getElementById("artist-birth-death");
        if (artistBirthDeathElement) artistBirthDeathElement.textContent = `${data.artistBeginDate} - ${data.artistEndDate}`;

        // Image principale de l'œuvre
        const artworkImage = document.getElementById("artwork-image");
        if (artworkImage) {
            artworkImage.src = data.primaryImage || data.primaryImageSmall;
            artworkImage.alt = data.title || "Picture of the artwork";
        }

        // Remplissage des informations sur l'œuvre
        const artworkTitleElement = document.getElementById("artwork-title");
        if (artworkTitleElement) artworkTitleElement.textContent = data.title || "Unknown Title";

        const artworkEndDateElement = document.getElementById("artwork-end-date");
        if (artworkEndDateElement) artworkEndDateElement.textContent += data.objectEndDate || "Unknown End Date";

        const artworkCountryElement = document.getElementById("artwork-country");
        if (artworkCountryElement) artworkCountryElement.textContent += data.country || "Unknown Country";

        const artworkStyleElement = document.getElementById("artwork-style");
        if (artworkStyleElement) artworkStyleElement.textContent += data.objectName;

        const artworkCultureElement = document.getElementById("artwork-culture");
        if (artworkCultureElement) artworkCultureElement.textContent += data.culture || "Unknown Culture";

        const artworkMediumElement = document.getElementById("artwork-medium");
        if (artworkMediumElement) artworkMediumElement.textContent += data.medium || "Unknown Medium";

        const artworkDimensionsElement = document.getElementById("artwork-dimensions");
        if (artworkDimensionsElement) artworkDimensionsElement.textContent += data.dimensions || "Unknown Dimensions";

        const artworkDepartmentElement = document.getElementById("artwork-department");
        if (artworkDepartmentElement) artworkDepartmentElement.textContent += data.department || "Unknown Department";

        const artworkMuseumLocationElement = document.getElementById("artwork-museum-location");
        if (artworkMuseumLocationElement) artworkMuseumLocationElement.textContent += data.repository;
    } catch (error) {
        console.error("Erreur:", error);
    }
}

async function fetchComments(objectId) {
    try {
        const response = await fetch(`get_comments.php?objectId=${objectId}`);
        if (!response.ok) throw new Error("Erreur lors de la récupération des commentaires");

        const comments = await response.json();
        const commentsContainer = document.getElementById("comments-container");
        if (!commentsContainer) return;

        commentsContainer.innerHTML = '';
        if (comments.length === 0) {
            const noComments = document.getElementById("no-comments");
            if (noComments) {
                noComments.style.display = 'block';
            } else {
                const noCommentsMessage = document.createElement("p");
                noCommentsMessage.id = "no-comments";
                noCommentsMessage.style.display = 'flex';
                noCommentsMessage.style.justifyContent = 'center';
                noCommentsMessage.textContent = "No comment yet";
                commentsContainer.appendChild(noCommentsMessage);
            }
        } else {
            // Trier les commentaires par nombre d'intérêts (du plus grand au plus petit)
            comments.sort((a, b) => b.interest - a.interest);

            comments.forEach(comment => {
                const commentWrapper = document.createElement("div");
                commentWrapper.className = "zone-comment";

                const commentUserElement = document.createElement("div");
                commentUserElement.className = "comment-user";
                commentUserElement.textContent = comment.pseudonyme;
                commentWrapper.appendChild(commentUserElement);

                const commentTextElement = document.createElement("div");
                commentTextElement.className = "comment";
                commentTextElement.textContent = comment.comment;
                commentWrapper.appendChild(commentTextElement);

                const commentDateElement = document.createElement("div");
                commentDateElement.className = "comment-date";
                commentDateElement.textContent = new Date(comment.created_at).toLocaleDateString();
                commentWrapper.appendChild(commentDateElement);

                // Conteneur pour les boutons et le nombre d'intérêts
                const interestContainer = document.createElement("div");
                interestContainer.className = "interest-container";

                // Bouton pour ajouter un point d'intérêt
                const addInterestButton = document.createElement("button");
                addInterestButton.textContent = "+";
                addInterestButton.addEventListener("click", async () => {
                    try {
                        const response = await fetch('update_interest.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams({
                                commentId: comment.id,
                                action: 'add'
                            })
                        });
                        if (response.ok) {
                            fetchComments(objectId);
                        } else {
                            console.error("Erreur lors de la mise à jour de l'intérêt:", response.statusText);
                        }
                    } catch (error) {
                        console.error("Erreur lors de la mise à jour de l'intérêt:", error);
                    }
                });
                interestContainer.appendChild(addInterestButton);

                // Affichage du nombre d'intérêts
                const interestCount = document.createElement("span");
                interestCount.className = "interest-count";
                interestCount.textContent = comment.interest;
                interestContainer.appendChild(interestCount);

                // Bouton pour supprimer un point d'intérêt
                const removeInterestButton = document.createElement("button");
                removeInterestButton.textContent = "-";
                removeInterestButton.addEventListener("click", async () => {
                    if (comment.interest > 0) {
                        try {
                            const response = await fetch('update_interest.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: new URLSearchParams({
                                    commentId: comment.id,
                                    action: 'remove'
                                })
                            });
                            if (response.ok) {
                                fetchComments(objectId);
                            } else {
                                console.error("Erreur lors de la mise à jour de l'intérêt:", response.statusText);
                            }
                        } catch (error) {
                            console.error("Erreur lors de la mise à jour de l'intérêt:", error);
                        }
                    }
                });
                interestContainer.appendChild(removeInterestButton);

                commentWrapper.appendChild(interestContainer);
                commentsContainer.appendChild(commentWrapper);
            });
        }
    } catch (error) {
        console.error("Erreur:", error);
    }
}