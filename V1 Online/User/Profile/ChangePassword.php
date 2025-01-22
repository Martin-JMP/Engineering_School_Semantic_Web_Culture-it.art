<?php
session_start();

// Vérification que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Récupérer les données de la requête POST
$data = json_decode(file_get_contents("php://input"), true);

$current_password = $data['current_password'];
$new_password = $data['new_password'];

// Connexion à la base de données
$servername = "NA";
$username = "NA";
$password = "NA";
$dbname = "NA";

$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifiez la connexion
if ($conn->connect_error) {
    echo json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Vérifier si le mot de passe actuel est correct
$user_id = $_SESSION['user_id'];
$sql = "SELECT password_hash FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $hashed_password = $row['password_hash'];

    // Vérifier le mot de passe actuel
    if (password_verify($current_password, $hashed_password)) {
        // Hacher le nouveau mot de passe
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Mettre à jour le mot de passe dans la base de données
        $update_sql = "UPDATE users SET password_hash = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $new_hashed_password, $user_id);

        if ($update_stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to update password']);
        }

        $update_stmt->close();
    } else {
        echo json_encode(['error' => 'Current password is incorrect']);
    }
} else {
    echo json_encode(['error' => 'User not found']);
}

$stmt->close();
$conn->close();
?>
