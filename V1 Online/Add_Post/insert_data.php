<?php
header('Content-Type: application/json');

// Enable error logging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection parameters
$servername = "NA";
$username = "NA";
$password = "NA";
$dbname = "NA";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the database connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    echo json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    exit;
}

session_start();  // Start the session to access session variables

// Retrieve POST data
$object_id = 9999999;
$title = $_POST['artwork-title'] ?? null;
$object_end_date = $_POST['artwork-end-date'] ?? null;
$medium = $_POST['artwork-medium'] ?? null;
$culture = $_POST['artwork-culture'] ?? null;
$dimensions = $_POST['artwork-dimensions'] ?? null;
$country = $_POST['artwork-country'] ?? null;
$description = $_POST['artwork-description'] ?? null;
$exact_file_name = $_POST['exact-file-name'] ?? null;
$artist_display_name = $_SESSION['pseudonyme'] ?? null;  // Retrieve artist display name from session

// Log the data being inserted
$log_message = "Data to be inserted: object_id=$object_id, title=$title, artist_display_name=$artist_display_name, object_end_date=$object_end_date, medium=$medium, culture=$culture, dimensions=$dimensions, country=$country, description=$description, file=$exact_file_name";
error_log($log_message);

// Prepare the SQL statement
$stmt = $conn->prepare("INSERT INTO artworks (object_id, title, artist_display_name, object_end_date, medium, culture, dimensions, country, description, file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['error' => "Prepare failed: " . $conn->error]);
    exit;
}

// Ensure all variables are defined and match the placeholders
$stmt->bind_param("ississssss", $object_id, $title, $artist_display_name, $object_end_date, $medium, $culture, $dimensions, $country, $description, $exact_file_name);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'log' => $log_message]);
} else {
    error_log("Execute failed: " . $stmt->error);
    echo json_encode(['error' => $stmt->error, 'log' => $log_message]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>