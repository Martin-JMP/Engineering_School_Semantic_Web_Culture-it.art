<?php
ini_set('display_errors', 0);  // Désactiver l'affichage des erreurs
error_reporting(E_ALL);  // Enregistrer toutes les erreurs

header('Content-Type: application/json');

// Paramètres de connexion à la base de données
$servername = "NA";
$username = "NA";
$password = "NA";
$dbname = "NA";

// Créer une connexion à la base de données
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion à la base de données
if ($conn->connect_error) {
    echo json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    exit;
}

$objectId = $_GET['objectId'];

$commentQuery = $conn->prepare("SELECT id, pseudonyme, comment, created_at, interest FROM Comments WHERE object_id = ?");
$commentQuery->bind_param("i", $objectId);
$commentQuery->execute();
$commentResult = $commentQuery->get_result();

$comments = [];
while ($row = $commentResult->fetch_assoc()) {
    $comments[] = $row;
}

echo json_encode($comments);

$commentQuery->close();

$conn->close();
?>