<?php
session_start();
ini_set('display_errors', 1);  // Activer l'affichage des erreurs pour le débogage
error_reporting(E_ALL);  // Enregistrer toutes les erreurs

header('Content-Type: application/json');

// Paramètres de connexion à la base de données
$servername = "cultubq333.mysql.db";
$username = "cultubq333";
$password = "Semantic789";
$dbname = "cultubq333";

// Créer une connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion à la base de données
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    echo json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Récupérer les données POST envoyées
$userName = $_POST['userName'] ?? null;
$commentText = $_POST['commentText'] ?? null;
$commentDate = $_POST['commentDate'] ?? null;
$objectId = $_POST['objectId'] ?? null;

if (!$userName || !$commentText || !$commentDate || !$objectId) {
    error_log("Missing required data");
    echo json_encode(['error' => "Missing required data"]);
    exit;
}

$artQuery = $conn->prepare("SELECT id FROM artworks WHERE object_id = ?");
if (!$artQuery) {
    error_log("Failed to prepare artQuery: " . $conn->error);
    echo json_encode(['error' => "Failed to prepare artQuery: " . $conn->error]);
    exit;
}

$artQuery->bind_param("i", $objectId);
$artQuery->execute();
$artQuery->bind_result($artId);
$artQuery->fetch();
$artQuery->close();

if (!$artId) {
    error_log("Art ID not found for object ID: " . $objectId);
    echo json_encode(['error' => "Art ID not found for object ID: " . $objectId]);
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    error_log("User not logged in");
    echo json_encode(['error' => "User not logged in"]);
    exit;
}

$commentQuery = $conn->prepare("INSERT INTO Comments (object_id, art_id, user_id, pseudonyme, comment, created_at) VALUES (?, ?, ?, ?, ?, ?)");
if (!$commentQuery) {
    error_log("Failed to prepare commentQuery: " . $conn->error);
    echo json_encode(['error' => "Failed to prepare commentQuery: " . $conn->error]);
    exit;
}

$commentQuery->bind_param("iiisss", $objectId, $artId, $userId, $userName, $commentText, $commentDate);

if ($commentQuery->execute()) {
    echo json_encode(['success' => true]);
} else {
    error_log("Failed to execute commentQuery: " . $commentQuery->error);
    echo json_encode(['success' => false, 'error' => $commentQuery->error]);
}

$commentQuery->close();
$conn->close();
?>