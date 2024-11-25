document.addEventListener("DOMContentLoaded", function() {
    const depositBox = document.getElementById('deposit-box');

    depositBox.addEventListener('dragover', function(event) {
        event.preventDefault();
        depositBox.classList.add('dragover');
        document.body.classList.add('dragging');
    });

    depositBox.addEventListener('dragleave', function() {
        depositBox.classList.remove('dragover');
        document.body.classList.remove('dragging');
    });

    depositBox.addEventListener('drop', function(event) {
        event.preventDefault();
        depositBox.classList.remove('dragover');
        document.body.classList.remove('dragging');

        const files = event.dataTransfer.files;
        if (files.length > 0) {
            // Handle the dropped files
            console.log('File(s) dropped', files);
            saveFiles(files);
        }
    });

    document.addEventListener('dragover', function(event) {
        event.preventDefault();
        document.body.classList.add('dragging');
    });

    document.addEventListener('dragleave', function() {
        document.body.classList.remove('dragging');
    });

    document.addEventListener('drop', function(event) {
        event.preventDefault();
        document.body.classList.remove('dragging');
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