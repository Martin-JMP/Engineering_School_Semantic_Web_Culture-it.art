document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    const ObjectId = urlParams.get('ObjectId');
    const id = urlParams.get('id');

    if (ObjectId == 9999999 && id > 2489) {
        fetchLimitedArtworkData(ObjectId, id);
        fetchCommentsWithId(ObjectId, id);
    } else if (ObjectId == 9999999 && id) {
        fetchArtworkDataFromDB(ObjectId, id);
        fetchCommentsWithId(ObjectId, id);
    } else if (ObjectId) {
        fetchArtworkData(ObjectId);
        fetchComments(ObjectId);
    } else {
        console.error("ObjectId or id is missing in the URL parameters.");
    }

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
                // Retrieve the last registered comment ID
                const sparqlQueryMaxId = `
                PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
                PREFIX ag: <http://example.org/artgallery/>
                SELECT (MAX(?id) AS ?maxId)
                WHERE {
                    ?comment rdf:type ag:Comment ;
                             ag:id ?id .
                }`;

                const maxIdResponse = await fetch('http://localhost:3030/Test-artworks8OBJECT-USERS/query', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({ query: sparqlQueryMaxId })
                });

                const maxIdData = await maxIdResponse.json();
                let newId = 1;
                if (maxIdData.results.bindings.length > 0) {
                    newId = parseInt(maxIdData.results.bindings[0].maxId.value) + 1;
                }

                // Retrieve the artist display name from the session
                const sessionResponse = await fetch('check_session.php');
                const sessionData = await sessionResponse.json();
                const artistDisplayName = sessionData.pseudonyme ?? null;

                console.log({
                    userName,
                    commentText,
                    commentDate,
                    newId,
                    objectId: ObjectId
                });

                // SPARQL query to insert the comment
                const sparqlQuery = `
                PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
                PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
                PREFIX ag: <http://example.org/artgallery/>
                INSERT DATA {
                    _:commentNode rdf:type ag:Comment ;
                                  ag:id "${newId}"^^<http://www.w3.org/2001/XMLSchema#integer> ;
                                  ag:object_id "${ObjectId}"^^<http://www.w3.org/2001/XMLSchema#integer> ;
                                  ag:comment "${commentText}" ;
                                  ag:pseudonyme "${artistDisplayName}" ;
                                  ag:created_at "${commentDate}"^^<http://www.w3.org/2001/XMLSchema#date> ;
                                  ag:interest 0 .
                }`;

                const response = await fetch('http://localhost:3030/Test-artworks8OBJECT-USERS/update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({ update: sparqlQuery })
                });

                const responseText = await response.text(); // Get the full response text

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}, response: ${responseText}`);
                }

                console.log("Response Text:", responseText); // Log the full response text

                // Check if the response is HTML indicating success
                if (responseText.includes("<html>")) {
                    console.log("Comment successfully saved.");
                    if (ObjectId == 9999999 && id > 2489) {
                        fetchCommentsWithId(ObjectId, id);
                    } else if (ObjectId == 9999999 && id) {
                        fetchCommentsWithId(ObjectId, id);
                    } else {
                        fetchComments(ObjectId);
                    }
                } else {
                    const result = JSON.parse(responseText); // Parse the response text as JSON
                    if (result.success) {
                        if (ObjectId == 9999999 && id > 2489) {
                            fetchCommentsWithId(ObjectId, id);
                        } else if (ObjectId == 9999999 && id) {
                            fetchCommentsWithId(ObjectId, id);
                        } else {
                            fetchComments(ObjectId);
                        }
                    } else {
                        console.error("Erreur lors de l'enregistrement du commentaire:", result.error);
                    }
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

async function fetchArtworkData(objectId) {
    const sparqlEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/query";
    const sparqlQuery = `
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX ag: <http://example.org/artgallery/>

SELECT 
  ?objectId 
  ?id
  ?title 
  ?medium 
  ?dimensions 
  ?artistBeginDate 
  ?artistEndDate 
  ?artistDisplayName 
  ?artistNationality 
  ?repository 
  ?country
  ?culture
  ?department
  ?object_end_date
WHERE {
  # Récupérer les propriétés principales de l'œuvre
  ?artwork rdf:type ag:Artwork ;
           ag:object_id ${objectId} ;
           ag:id ?id ;
           ag:title ?title ;
           ag:medium ?medium ;
           ag:dimensions ?dimensions ;
           ag:artist_begin_date ?artistBeginDate ;
           ag:artist_end_date ?artistEndDate ;
           ag:artist_display_name ?artistDisplayName ;
           ag:artist_nationality ?artistNationality ;
           ag:repository ?repository ;
           ag:culture ?culture ;
           ag:department ?department ;
           ag:object_end_date ?object_end_date ;
           ag:country ?country .
}`;

    try {
        const response = await fetch(sparqlEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({ query: sparqlQuery })
        });

        if (!response.ok) throw new Error("Erreur lors de la récupération des données");

        const data = await response.json();
        const artworkData = data.results.bindings[0];

        // Remplissage des informations sur l'artiste
        const artistNameElement = document.getElementById("artist-name");
        if (artistNameElement) artistNameElement.textContent = artworkData.artistDisplayName.value || "Unknown Artist";
        else if (artistNameElement) artistNameElement.remove();

        const artistNationalityElement = document.getElementById("artist-nationality");
        if (artistNationalityElement) artistNationalityElement.textContent = artworkData.artistNationality.value || "Unknown Nationality";
        else if (artistNationalityElement) artistNationalityElement.remove();

        const artistBirthDeathElement = document.getElementById("artist-birth-death");
        if (artistBirthDeathElement) artistBirthDeathElement.textContent = `${artworkData.artistBeginDate.value} - ${artworkData.artistEndDate.value}`;
        else if (artistBirthDeathElement) artistBirthDeathElement.remove();

        // Fetch image from MetMuseum API
        const artworkImage = document.getElementById("artwork-image");
        if (artworkImage) {
            const imageResponse = await fetch(`https://collectionapi.metmuseum.org/public/collection/v1/objects/${objectId}`);
            if (!imageResponse.ok) throw new Error("Erreur lors de la récupération de l'image");

            const imageData = await imageResponse.json();
            artworkImage.src = imageData.primaryImage || imageData.primaryImageSmall;
            artworkImage.alt = artworkData.title.value || "Picture of the artwork";
        }

        // Remplissage des informations sur l'œuvre
        const artworkTitleElement = document.getElementById("artwork-title");
        if (artworkTitleElement) artworkTitleElement.textContent = artworkData.title.value || "Unknown Title";
        else if (artworkTitleElement) artworkTitleElement.remove();

        const artworkEndDateElement = document.getElementById("artwork-end-date");
        if (artworkEndDateElement) artworkEndDateElement.textContent += artworkData.object_end_date.value || "Unknown End Date";
        else if (artworkEndDateElement) artworkEndDateElement.remove();

        const artworkCountryElement = document.getElementById("artwork-country");
        if (artworkCountryElement) artworkCountryElement.textContent += artworkData.country.value || "Unknown Country";
        else if (artworkCountryElement) artworkCountryElement.remove();

        const artworkStyleElement = document.getElementById("artwork-style");
        if (artworkStyleElement) artworkStyleElement.textContent += artworkData.medium.value;
        else if (artworkStyleElement) artworkStyleElement.remove();

        const artworkCultureElement = document.getElementById("artwork-culture");
        if (artworkCultureElement) artworkCultureElement.textContent += artworkData.culture.value || "Unknown Culture";
        else if (artworkCultureElement) artworkCultureElement.remove();

        const artworkMediumElement = document.getElementById("artwork-medium");
        if (artworkMediumElement) artworkMediumElement.textContent += artworkData.medium.value || "Unknown Medium";
        else if (artworkMediumElement) artworkMediumElement.remove();

        const artworkDimensionsElement = document.getElementById("artwork-dimensions");
        if (artworkDimensionsElement) artworkDimensionsElement.textContent += artworkData.dimensions.value || "Unknown Dimensions";
        else if (artworkDimensionsElement) artworkDimensionsElement.remove();

        const artworkDepartmentElement = document.getElementById("artwork-department");
        if (artworkDepartmentElement) artworkDepartmentElement.textContent += artworkData.department.value || "Unknown Department";
        else if (artworkDepartmentElement) artworkDepartmentElement.remove();

        const artworkMuseumLocationElement = document.getElementById("artwork-museum-location");
        if (artworkMuseumLocationElement) artworkMuseumLocationElement.textContent += artworkData.repository.value;
        else if (artworkMuseumLocationElement) artworkMuseumLocationElement.remove();

        const artworkDescriptionElement = document.getElementById("artwork-description");
        if (artworkDescriptionElement && artworkData.description) artworkDescriptionElement.textContent = artworkData.description.value;
        else if (artworkDescriptionElement) artworkDescriptionElement.remove();
    } catch (error) {
        console.error("Erreur:", error);
    }
}

async function fetchArtworkDataFromDB(objectId, id) {
    const sparqlEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/query";
    const sparqlQuery = `
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX ag: <http://example.org/artgallery/>

SELECT 
  ?objectId 
  ?id
  ?title 
  ?dimensions 
  ?artistBeginDate 
  ?artistEndDate 
  ?artistDisplayName 
  ?artistNationality 
  ?country
  ?culture
  ?file 
  ?object_end_date
WHERE {
  ?artwork rdf:type ag:Artwork ;
           ag:object_id ${objectId};
           ag:id ${id} ;
           ag:title ?title ;
           ag:dimensions ?dimensions ;
           ag:artist_begin_date ?artistBeginDate ;
           ag:artist_end_date ?artistEndDate ;
           ag:artist_display_name ?artistDisplayName ;
           ag:artist_nationality ?artistNationality ;
           ag:file ?file ;
           ag:culture ?culture ;
           ag:object_end_date ?object_end_date ;
           ag:country ?country .
}`;
    try {
        const response = await fetch(sparqlEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({ query: sparqlQuery })
        });

        if (!response.ok) throw new Error("Erreur lors de la récupération des données");

        const data = await response.json();
        const artworkData = data.results.bindings[0];
        
        // Remplissage des informations sur l'artiste
        //const artistNameElement = document.getElementById("artist-name");
        //if (artistNameElement) artistNameElement.textContent = artworkData.artistDisplayName.value || "Unknown Artist";

        const artistNameElement = document.getElementById("artist-name");
        if (artistNameElement) artistNameElement.textContent = artworkData.artistDisplayName.value;
        else if (artistNameElement) artistNameElement.remove();

        const artistNationalityElement = document.getElementById("artist-nationality");
        if (artistNationalityElement) artistNationalityElement.textContent = artworkData.artistNationality.value;
        else if (artistNationalityElement) artistNationalityElement.remove();


        const artistBirthDeathElement = document.getElementById("artist-birth-death");
        if (artistBirthDeathElement && artworkData.artistBeginDate && artworkData.artistEndDate) artistBirthDeathElement.textContent = `${artworkData.artistBeginDate.value} - ${artworkData.artistEndDate.value}`;
        else if (artistBirthDeathElement) artistBirthDeathElement.remove();

        // Image principale de l'œuvre
        const artworkImage = document.getElementById("artwork-image");
        if (artworkImage && artworkData.file && artworkData.file.value) {
            artworkImage.src = `../Upload/${artworkData.file.value}`;
            artworkImage.alt = artworkData.title.value || "Picture of the artwork";
        } else if (artworkImage) artworkImage.remove();

        // Remplissage des informations sur l'œuvre
        const artworkTitleElement = document.getElementById("artwork-title");
        if (artworkTitleElement && artworkData.title) artworkTitleElement.textContent = artworkData.title.value;
        else if (artworkTitleElement) artworkTitleElement.remove();

        const artworkDescriptionElement = document.getElementById("artwork-description");
        if (artworkDescriptionElement && artworkData.description) artworkDescriptionElement.textContent = artworkData.description.value;
        else if (artworkDescriptionElement) artworkDescriptionElement.remove();

        const artworkDateElement = document.getElementById("artwork-end-date");
        if (artworkDateElement && artworkData.object_end_date) artworkDateElement.textContent += artworkData.object_end_date.value;
        else if (artworkDateElement) artworkDateElement.remove();

        const artworkMediumElement = document.getElementById("artwork-medium");
        if (artworkMediumElement && artworkData.medium) artworkMediumElement.textContent += artworkData.medium.value;
        else if (artworkMediumElement) artworkMediumElement.remove();

        const artworkCultureElement = document.getElementById("artwork-culture");
        if (artworkCultureElement && artworkData.culture) artworkCultureElement.textContent += artworkData.culture.value;
        else if (artworkCultureElement) artworkCultureElement.remove();

        const artworkDimensionsElement = document.getElementById("artwork-dimensions");
        if (artworkDimensionsElement && artworkData.dimensions) artworkDimensionsElement.textContent += artworkData.dimensions.value;
        else if (artworkDimensionsElement) artworkDimensionsElement.remove();

        const artworkCountryElement = document.getElementById("artwork-country");
        if (artworkCountryElement && artworkData.country) artworkCountryElement.textContent += artworkData.country.value;
        else if (artworkCountryElement) artworkCountryElement.remove();

        const artworkStyleElement = document.getElementById("artwork-style");
        if (artworkStyleElement && artworkData.medium) artworkStyleElement.textContent += artworkData.medium.value;
        else if (artworkStyleElement) artworkStyleElement.remove();

        const artworkDepartmentElement = document.getElementById("artwork-department");
        if (artworkDepartmentElement && artworkData.department) artworkDepartmentElement.textContent += artworkData.department.value;
        else if (artworkDepartmentElement) artworkDepartmentElement.remove();

        const artworkMuseumLocationElement = document.getElementById("artwork-museum-location");
        if (artworkMuseumLocationElement && artworkData.repository) artworkMuseumLocationElement.textContent += artworkData.repository.value;
        else if (artworkMuseumLocationElement) artworkMuseumLocationElement.remove();
        } catch (error) {
        console.error("Erreur:", error);
    }
}

async function fetchLimitedArtworkData(objectId, id) {
    const sparqlEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/query";
    const sparqlQuery = `
    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>

    SELECT 
      ?objectId 
      ?id
      ?title 
      ?dimensions 
      ?artistDisplayName 
      ?country
      ?culture
      ?file 
      ?object_end_date
      ?description
      ?medium
    WHERE {
      ?artwork rdf:type ag:Artwork ;
               ag:object_id ${objectId} ;
               ag:id ${id} ;
               ag:title ?title ;
               ag:dimensions ?dimensions ;
               ag:artist_display_name ?artistDisplayName ;
               ag:file ?file ;
               ag:culture ?culture ;
               ag:object_end_date ?object_end_date ;
               ag:description ?description ;
               ag:medium ?medium ;
               ag:country ?country .
    }`;

    try {
        const response = await fetch(sparqlEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({ query: sparqlQuery })
        });

        if (!response.ok) throw new Error("Erreur lors de la récupération des données");

        const data = await response.json();
        const artworkData = data.results.bindings[0];

        // Remplissage des informations sur l'artiste
        const artistNameElement = document.getElementById("artist-name");
        if (artistNameElement) artistNameElement.textContent = artworkData.artistDisplayName.value || "Unknown Artist";
        else if (artistNameElement) artistNameElement.remove();

        // Image principale de l'œuvre
        const artworkImage = document.getElementById("artwork-image");
        if (artworkImage && artworkData.file && artworkData.file.value) {
            artworkImage.src = `../Upload/${artworkData.file.value}`;
            artworkImage.alt = artworkData.title.value || "Picture of the artwork";
        } else if (artworkImage) artworkImage.remove();

        // Remplissage des informations sur l'œuvre
        const artworkTitleElement = document.getElementById("artwork-title");
        if (artworkTitleElement && artworkData.title) artworkTitleElement.textContent = artworkData.title.value;
        else if (artworkTitleElement) artworkTitleElement.remove();


        const artworkMuseumLocationElement = document.getElementById("artwork-museum-location");
        if (artworkMuseumLocationElement) artworkMuseumLocationElement.remove();

        const artworkDateElement = document.getElementById("artwork-end-date");
        if (artworkDateElement && artworkData.object_end_date) artworkDateElement.textContent += artworkData.object_end_date.value;
        else if (artworkDateElement) artworkDateElement.remove();

        const artworkCultureElement = document.getElementById("artwork-culture");
        if (artworkCultureElement && artworkData.culture) artworkCultureElement.textContent += artworkData.culture.value;
        else if (artworkCultureElement) artworkCultureElement.remove();

        const artworkDepartmentElement = document.getElementById("artwork-department");
        if (artworkDepartmentElement) artworkDepartmentElement.remove();

        const artworkStyleElement = document.getElementById("artwork-style");
        if (artworkStyleElement) artworkStyleElement.remove();

        const artworkMediumElement = document.getElementById("artwork-medium");
        if (artworkMediumElement) artworkMediumElement.textContent += artworkData.medium.value || "Unknown Medium";
        else if (artworkMediumElement) artworkMediumElement.remove();

        const artworkDimensionsElement = document.getElementById("artwork-dimensions");
        if (artworkDimensionsElement && artworkData.dimensions) artworkDimensionsElement.textContent += artworkData.dimensions.value;
        else if (artworkDimensionsElement) artworkDimensionsElement.remove();

        const artworkCountryElement = document.getElementById("artwork-country");
        if (artworkCountryElement && artworkData.country) artworkCountryElement.textContent += artworkData.country.value;
        else if (artworkCountryElement) artworkCountryElement.remove();

        const artworkDescriptionElement = document.getElementById("artwork-description");
        if (artworkDescriptionElement && artworkData.description) artworkDescriptionElement.textContent += artworkData.description.value;
        else if (artworkDescriptionElement) artworkDescriptionElement.remove();
    } catch (error) {
        console.error("Erreur:", error);
    }
}

async function fetchComments(objectId) {
    const sparqlEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/query";
    const sparqlQuery = `
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>

    SELECT ?comment ?pseudonyme ?created_at ?interest ?id
    WHERE {
    # Récupérer les commentaires liés à l'œuvre avec un object_id spécifique
    ?commentNode rdf:type ag:Comment ;
                ag:object_id ${objectId} ;
                ag:comment ?comment ;
                ag:pseudonyme ?pseudonyme ;
                ag:created_at ?created_at ;
                ag:interest ?interest ;
                ag:id ?id .
    
    }`;

    try {
        const response = await fetch(sparqlEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({ query: sparqlQuery })
        });

        if (!response.ok) throw new Error("Erreur lors de la récupération des commentaires");

        const data = await response.json();
        const comments = data.results.bindings;
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
            comments.sort((a, b) => b.interest.value - a.interest.value);

            comments.forEach(comment => {
                const commentWrapper = document.createElement("div");
                commentWrapper.className = "zone-comment";

                const commentUserElement = document.createElement("div");
                commentUserElement.className = "comment-user";
                commentUserElement.textContent = comment.pseudonyme.value;
                commentWrapper.appendChild(commentUserElement);

                const commentTextElement = document.createElement("div");
                commentTextElement.className = "comment";
                commentTextElement.textContent = comment.comment.value;
                commentWrapper.appendChild(commentTextElement);

                const commentDateElement = document.createElement("div");
                commentDateElement.className = "comment-date";
                commentDateElement.textContent = new Date(comment.created_at.value).toLocaleDateString();
                commentWrapper.appendChild(commentDateElement);

                // Conteneur pour les boutons et le nombre d'intérêts
                const interestContainer = document.createElement("div");
                interestContainer.className = "interest-container";

                // Bouton pour ajouter un point d'intérêt
                const addInterestButton = document.createElement("button");
                addInterestButton.textContent = "+";
                addInterestButton.addEventListener("click", async () => {
                    await updateInterest(comment.id.value, 'add');
                    fetchComments(objectId);
                });
                interestContainer.appendChild(addInterestButton);

                // Affichage du nombre d'intérêts
                const interestCount = document.createElement("span");
                interestCount.className = "interest-count";
                interestCount.textContent = comment.interest.value;
                interestContainer.appendChild(interestCount);

                // Bouton pour supprimer un point d'intérêt
                const removeInterestButton = document.createElement("button");
                removeInterestButton.textContent = "-";
                removeInterestButton.addEventListener("click", async () => {
                    if (comment.interest.value > 0) {
                        await updateInterest(comment.id.value, 'remove');
                        fetchComments(objectId);
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

async function fetchCommentsWithId(objectId, id) {
    const sparqlEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/query";
    const sparqlQuery = `
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>

    SELECT ?comment ?pseudonyme ?created_at ?interest ?id
    WHERE {
    # Récupérer les commentaires liés à l'œuvre avec un object_id spécifique et un id spécifique
    ?commentNode rdf:type ag:Comment ;
                ag:object_id ${objectId} ;
                ag:art_id ${id} ;
                ag:comment ?comment ;
                ag:pseudonyme ?pseudonyme ;
                ag:created_at ?created_at ;
                ag:interest ?interest ;
                ag:id ?id .
    
    }`;

    try {
        const response = await fetch(sparqlEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json'
            },
            body: new URLSearchParams({ query: sparqlQuery })
        });

        if (!response.ok) throw new Error("Erreur lors de la récupération des commentaires");

        const data = await response.json();
        const comments = data.results.bindings;
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
            comments.sort((a, b) => b.interest.value - a.interest.value);

            comments.forEach(comment => {
                const commentWrapper = document.createElement("div");
                commentWrapper.className = "zone-comment";

                const commentUserElement = document.createElement("div");
                commentUserElement.className = "comment-user";
                commentUserElement.textContent = comment.pseudonyme.value;
                commentWrapper.appendChild(commentUserElement);

                const commentTextElement = document.createElement("div");
                commentTextElement.className = "comment";
                commentTextElement.textContent = comment.comment.value;
                commentWrapper.appendChild(commentTextElement);

                const commentDateElement = document.createElement("div");
                commentDateElement.className = "comment-date";
                commentDateElement.textContent = new Date(comment.created_at.value).toLocaleDateString();
                commentWrapper.appendChild(commentDateElement);

                // Conteneur pour les boutons et le nombre d'intérêts
                const interestContainer = document.createElement("div");
                interestContainer.className = "interest-container";

                // Bouton pour ajouter un point d'intérêt
                const addInterestButton = document.createElement("button");
                addInterestButton.textContent = "+";
                addInterestButton.addEventListener("click", async () => {
                    await updateInterest(comment.id.value, 'add');
                    fetchCommentsWithId(objectId, id);
                });
                interestContainer.appendChild(addInterestButton);

                // Affichage du nombre d'intérêts
                const interestCount = document.createElement("span");
                interestCount.className = "interest-count";
                interestCount.textContent = comment.interest.value;
                interestContainer.appendChild(interestCount);

                // Bouton pour supprimer un point d'intérêt
                const removeInterestButton = document.createElement("button");
                removeInterestButton.textContent = "-";
                removeInterestButton.addEventListener("click", async () => {
                    if (comment.interest.value > 0) {
                        await updateInterest(comment.id.value, 'remove');
                        fetchCommentsWithId(objectId, id);
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

async function updateInterest(commentId, action) {
    const sparqlEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/update";
    const increment = action === 'add' ? 1 : -1;

    const sparqlUpdateQuery = `
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>

    DELETE {
        ?commentNode ag:interest ?currentInterest .
    }
    INSERT {
        ?commentNode ag:interest ?updatedInterest .
    }
    WHERE {
        ?commentNode rdf:type ag:Comment ;
                     ag:id "${commentId}"^^<http://www.w3.org/2001/XMLSchema#integer> ;
                     ag:interest ?currentInterest .
        BIND(?currentInterest + ${increment} AS ?updatedInterest)
        FILTER(?updatedInterest >= 0) # Assure que l'intérêt ne devient pas négatif
    }`;

    try {
        const response = await fetch("http://localhost:3030/Test-artworks8OBJECT-USERS/update", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({ update: sparqlUpdateQuery }),
        });
        
        if (!response.ok) {
            throw new Error(`Erreur lors de la mise à jour : ${response.statusText}`);
        }
        
        console.log(`Mise à jour de l'intérêt réussie pour le commentaire ${commentId}`);
        } catch (error) {
        console.error("Erreur:", error);
    }
}
