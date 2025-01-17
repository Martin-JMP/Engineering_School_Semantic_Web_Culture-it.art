document.addEventListener("DOMContentLoaded", function() {
    const depositBox = document.getElementById('deposit-box');
    const fileInput = document.getElementById('file-input');
    const fileNameLabel = document.getElementById('file-name-label');
    const removeFileButton = document.getElementById('remove-file-button');

    depositBox.addEventListener('click', function() {
        fileInput.click();
    });

    fileInput.addEventListener('change', function() {
        const files = fileInput.files;
        if (files.length > 0) {
            if (isImageFile(files[0])) {
                fileNameLabel.textContent = `Uploaded File: ${files[0].name}`;
                removeFileButton.classList.remove('hidden-button');
                saveFiles(files);
            } else {
                alert('Only image files (png, jpeg, jpg) are allowed.');
                fileInput.value = '';
            }
        } else {
            fileNameLabel.textContent = 'Uploaded File:';
            removeFileButton.classList.add('hidden-button');
        }
    });

    removeFileButton.addEventListener('click', function() {
        const fileName = fileNameLabel.textContent.replace('Uploaded File: ', '');
        if (fileName) {
            fetch('delete_file.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ fileName: fileName })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    fileInput.value = '';
                    fileNameLabel.textContent = 'Uploaded File:';
                    removeFileButton.classList.add('hidden-button');
                    console.log('File removed successfully');
                } else {
                    console.error('Error removing file:', data.error);
                }
            })
            .catch(error => console.error('Error removing file:', error));
        }
    });

    document.addEventListener('dragover', function(event) {
        event.preventDefault();
        depositBox.classList.add('dragover');
        document.body.classList.add('dragging');
    });

    document.addEventListener('dragleave', function() {
        depositBox.classList.remove('dragover');
        document.body.classList.remove('dragging');
    });

    document.addEventListener('drop', function(event) {
        event.preventDefault();
        depositBox.classList.remove('dragover');
        document.body.classList.remove('dragging');

        const files = event.dataTransfer.files;
        if (files.length > 0) {
            if (isImageFile(files[0])) {
                fileNameLabel.textContent = `Uploaded File: ${files[0].name}`;
                removeFileButton.classList.remove('hidden-button');
                saveFiles(files);
            } else {
                alert('Only image files (png, jpeg, jpg) are allowed.');
            }
        } else {
            fileNameLabel.textContent = 'Uploaded File:';
            removeFileButton.classList.add('hidden-button');
        }
    });

    const form = document.querySelector('form');
    form.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(form);  // Ensure formData is defined here

        // Log the form data
        for (let [key, value] of formData.entries()) {
            console.log(`${key}: ${value}`);
        }

        // Validate form data
        const requiredFields = [
            'artwork-title',
            'artwork-end-date',
            'artwork-medium',
            'artwork-culture',
            'artwork-dimensions',
            'artwork-country',
            'artwork-description',
            'exact-file-name'
        ];

        for (const field of requiredFields) {
            if (!formData.get(field)) {
                alert(`Missing required data: ${field}`);
                return;
            }
        }

        // Define the object_id variable
        const object_id = 9999999;

        // Retrieve the last registered ID
        const sparqlQueryMaxId = `
        PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
        PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
        PREFIX ag: <http://example.org/artgallery/>
        SELECT (MAX(?id) AS ?maxId)
        WHERE {
            ?artwork rdf:type ag:Artwork ;
                     ag:id ?id .
        }`;

        fetch('http://localhost:3030/Test-artworks8OBJECT-USERS/query', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({ query: sparqlQueryMaxId })
        })
        .then(response => response.json())
        .then(dataMaxId => {
            let newId = 1;
            if (dataMaxId.results.bindings.length > 0) {
                newId = parseInt(dataMaxId.results.bindings[0].maxId.value) + 1;
            }

            // Retrieve the artist display name from the session
            fetch('check_session.php')
                .then(response => response.json())
                .then(sessionData => {
                    const artist_display_name = sessionData.pseudonyme ?? null;  // Retrieve artist display name from session

                    // SPARQL query to insert the artwork data
                    const sparqlQuery = `
                    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
                    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
                    PREFIX ag: <http://example.org/artgallery/>
                    INSERT DATA {
                      _:artwork rdf:type ag:Artwork ;
                                ag:id "${newId}"^^<http://www.w3.org/2001/XMLSchema#integer> ;
                                ag:object_id ${object_id} ;
                                ag:title "${formData.get('artwork-title')}" ;
                                ag:object_end_date "${formData.get('artwork-end-date')}" ;
                                ag:medium "${formData.get('artwork-medium')}" ;
                                ag:culture "${formData.get('artwork-culture')}" ;
                                ag:dimensions "${formData.get('artwork-dimensions')}" ;
                                ag:country "${formData.get('artwork-country')}" ;
                                ag:description "${formData.get('artwork-description')}" ;
                                ag:file "${formData.get('exact-file-name')}" ;
                                ag:artist_display_name "${artist_display_name}" .
                    }`;

                    // Send the query to the SPARQL endpoint
                    fetch('http://localhost:3030/Test-artworks8OBJECT-USERS/update', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({ update: sparqlQuery })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data);  // Print the json_encode response in the console
                        if (data.success) {
                            alert('Data inserted successfully');
                        } else {
                            console.error('Error inserting data:', data.error);
                        }
                    })
                    .catch(error => console.error('Error inserting data:', error));
                })
                .catch(error => console.error('Error retrieving session data:', error));
        })
        .catch(error => console.error('Error retrieving max ID:', error));
    });

    function isImageFile(file) {
        const acceptedImageTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        return acceptedImageTypes.includes(file.type);
    }

    function saveFiles(files) {
        const formData = new FormData();
        for (const file of files) {
            if (file) {
                formData.append('files[]', file);
            }
        }

        fetch('upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Files uploaded successfully');
            } else {
                console.error('Error uploading files:', data.error);
            }
        })
        .catch(error => console.error('Error uploading files:', error));
    }
});