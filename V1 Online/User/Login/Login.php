<?php
// Affiche les erreurs PHP pour le développement (désactiver en production)
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Paramètres de connexion à la base de données
$servername = "NA";
$username = "NA";
$password = "NA";
$dbname = "NA";

// Créer une connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Récupérer l'email du POST
$email = isset($_POST['email']) ? $_POST['email'] : '';

if ($email) {
    // Préparer la requête pour vérifier si l'email existe
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Vérifier si l'email existe
    if ($result->num_rows > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }

    // Fermer la connexion
    $stmt->close();
} else {
    echo json_encode(['error' => 'Email is required']);
}

$conn->close();
?>
