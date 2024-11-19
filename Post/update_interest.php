
<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');

$servername = "cultubq333.mysql.db";
$username = "cultubq333";
$password = "Semantic789";
$dbname = "cultubq333";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    exit;
}

$commentId = $_POST['commentId'];
$action = $_POST['action'];

if ($action === 'add') {
    $updateQuery = $conn->prepare("UPDATE Comments SET interest = interest + 1 WHERE id = ?");
} elseif ($action === 'remove') {
    $updateQuery = $conn->prepare("UPDATE Comments SET interest = interest - 1 WHERE id = ?");
} else {
    echo json_encode(['error' => "Invalid action"]);
    exit;
}

$updateQuery->bind_param("i", $commentId);
$updateQuery->execute();

if ($updateQuery->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => "Failed to update interest"]);
}

$updateQuery->close();
$conn->close();
?>