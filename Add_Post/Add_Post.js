document.addEventListener("DOMContentLoaded", function() {
    const depositBox = document.getElementById('deposit-box');
    const fileInput = document.getElementById('file-input');

    depositBox.addEventListener('click', function() {
        fileInput.click();
    });

    fileInput.addEventListener('change', function() {
        const files = fileInput.files;
        if (files.length > 0) {
            saveFiles(files);
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
            saveFiles(files);
        }
    });

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