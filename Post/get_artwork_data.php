<?php
header('Content-Type: application/json');

// Enable error logging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database connection parameters
$servername = "cultubq333.mysql.db";
$username = "cultubq333";
$password = "Semantic789";
$dbname = "cultubq333";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the database connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    echo json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Retrieve the ObjectId and id from the query parameters
$object_id = $_GET['objectId'] ?? null;
$id = $_GET['id'] ?? null;

if ($object_id != 9999999 || !$id) {
    echo json_encode(['error' => "Invalid ObjectId or id"]);
    exit;
}

// Prepare the SQL statement
$stmt = $conn->prepare("SELECT title, artist_display_name, artist_nationality, artist_begin_date, artist_end_date, object_end_date, dimensions, country, description, file FROM artworks WHERE object_id = ? AND id = ?");
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['error' => "Prepare failed: " . $conn->error]);
    exit;
}

// Bind the parameters and execute the statement
$stmt->bind_param("ii", $object_id, $id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the data
if ($row = $result->fetch_assoc()) {
    // Remove keys with "NULL" values or the string "NULL"
    $filtered_row = array_filter($row, function($value) {
        return $value !== null && $value !== "NULL";
    });
    echo json_encode($filtered_row);
} else {
    echo json_encode(['error' => "No data found for ObjectId: $object_id and id: $id"]);
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
