<?php
// Connexion à la base de données
$servername = "cultubq333.mysql.db";
$username = "cultubq333";
$password = "Semantic789";
$dbname = "cultubq333";

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Vérifier si les données sont envoyées via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données depuis la requête POST
    $pseudonyme = $_POST['pseudonyme'] ?? '';
    $email = $_POST['email'] ?? '';
    $birth = $_POST['birth'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Vérifier que les mots de passe correspondent
    if ($password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'Passwords do not match!']);
        exit();
    }

    // Vérifier que l'email n'existe pas déjà dans la base de données
    $emailCheckQuery = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $emailCheckQuery->bind_param("s", $email);
    $emailCheckQuery->execute();
    $emailCheckQuery->store_result();

    if ($emailCheckQuery->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists!']);
        $emailCheckQuery->close();
        exit();
    }
    $emailCheckQuery->close();

    // Vérifier que le pseudo n'existe pas déjà dans la base de données
    $pseudonymeCheckQuery = $conn->prepare("SELECT pseudonyme FROM users WHERE pseudonyme = ?");
    $pseudonymeCheckQuery->bind_param("s", $email);
    $pseudonymeCheckQuery->execute();
    $pseudonymeCheckQuery->store_result();
    
    if ($pseudonymeCheckQuery->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Pseudonyme already exists!']);
        $pseudonymeCheckQuery->close();
        exit();
    }
    $pseudonymeCheckQuery->close();

    // Hacher le mot de passe avec bcrypt
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insérer l'utilisateur dans la base de données
    $insertQuery = $conn->prepare("INSERT INTO users (pseudonyme, email, password_hash, birth, created_at) VALUES (?, ?, ?, ?, NOW())");
    $insertQuery->bind_param("ssss", $pseudonyme, $email, $hashed_password, $birth);

    if ($insertQuery->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'User registered successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error registering user!']);
    }

    // Fermer les connexions
    $insertQuery->close();
    $conn->close();
}
?>
