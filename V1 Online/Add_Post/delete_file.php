<?php
header('Content-Type: application/json');

$servername = "NA";
$username = "NA";
$password = "NA";
$dbname = "NA";

$data = json_decode(file_get_contents("php://input"), true);
$fileName = $data['fileName'] ?? '';

if ($fileName) {
    $filePath = '../Upload/' . basename($fileName);
    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to delete file']);
        }
    } else {
        echo json_encode(['error' => 'File not found']);
    }
} else {
    echo json_encode(['error' => 'No file specified']);
}
?>