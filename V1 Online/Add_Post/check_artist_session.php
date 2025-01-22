<?php
session_start();
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['pseudonyme'])) {
    echo json_encode(['logged_in' => false]);
    exit;
}


// Database connection
$servername = "NA";
$username = "NA";
$password = "NA";
$dbname = "NA";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    exit;
}

$pseudonyme = $_SESSION['pseudonyme'];
$query = $conn->prepare("SELECT Artist FROM users WHERE pseudonyme = ?");
$query->bind_param("s", $pseudonyme);
$query->execute();
$query->bind_result($isArtist);
$query->fetch();
$query->close();
$conn->close();

if ($isArtist) {
    echo json_encode(['logged_in' => true, 'is_artist' => true]);
} else {
    echo json_encode(['logged_in' => true, 'is_artist' => false]);
}
?>