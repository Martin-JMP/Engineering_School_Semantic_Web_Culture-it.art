<?php
$uploadDir = '../Upload/';
$response = ['success' => false, 'error' => 'Unknown error'];

if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        $response['error'] = 'Failed to create upload directory.';
        error_log('Failed to create upload directory.');
        echo json_encode($response);
        exit;
    }
}

if (!is_writable($uploadDir)) {
    $response['error'] = 'Upload directory is not writable.';
    error_log('Upload directory is not writable.');
    echo json_encode($response);
    exit;
}

if (!empty($_FILES['files']['name'][0])) {
    foreach ($_FILES['files']['name'] as $key => $name) {
        $tmpName = $_FILES['files']['tmp_name'][$key];
        $filePath = $uploadDir . basename($name);

        if (move_uploaded_file($tmpName, $filePath)) {
            $response['success'] = true;
        } else {
            $response['error'] = 'Failed to upload file: ' . $name;
            $lastError = error_get_last();
            if ($lastError) {
                error_log('Failed to upload file: ' . $name . ' - ' . $lastError['message']);
            } else {
                error_log('Failed to upload file: ' . $name . ' - Unknown error');
            }
            break;
        }
    }
} else {
    $response['success'] = true;
}

echo json_encode($response);
?>