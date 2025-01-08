<?php
session_start();  // Démarre la session PHP

ini_set('display_errors', 0);  // Désactiver l'affichage des erreurs
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
    echo json_encode(['error' => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Récupérer l'email et le mot de passe soumis
if (!empty($_POST['email']) && !empty($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Préparer la requête pour vérifier si l'email existe et récupérer les informations associées
    $stmt = $conn->prepare("SELECT id, pseudonyme, email, password_hash FROM users WHERE email = ?");
    if ($stmt === false) {
        echo json_encode(['error' => 'Failed to prepare statement']);
        exit;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Vérification si l'utilisateur existe
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $hashed_password = $row['password_hash'];

        // Vérification du mot de passe
        if (password_verify($password, $hashed_password)) {
            // Mot de passe correct, démarrer la session
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['pseudonyme'] = $row['pseudonyme'];
            $_SESSION['email'] = $row['email'];

            echo json_encode(['success' => true, 'pseudonyme' => $row['pseudonyme']]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid password']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Email not found']);
    }

    $stmt->close();
} else {
    echo json_encode(['error' => 'Email and password are required']);
}

$conn->close();
?>
