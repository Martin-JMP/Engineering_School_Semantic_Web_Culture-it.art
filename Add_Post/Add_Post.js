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