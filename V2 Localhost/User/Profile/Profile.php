<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Connexion à la base de données
$servername = "cultubq333.mysql.db";
$username = "cultubq333";
$password = "Semantic789";
$dbname = "cultubq333";

// Essayez de se connecter à la base de données
try {
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Vérifiez la connexion
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Endpoint to handle artwork deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_artwork'])) {
        $artwork_id = $_POST['artwork_id'];
        $image_url = $_POST['image_url'];

        // Delete artwork from database
        $sqlDelete = "DELETE FROM artworks WHERE id = ?";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bind_param("i", $artwork_id);
        $stmtDelete->execute();

        // Delete image file from server
        $filePath = '../../Upload/' . basename($image_url);
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        echo json_encode(['success' => true]);
        exit;
    }

    // Récupérer les informations de l'utilisateur connecté
    $user_id = $_SESSION['user_id'];
    
    // Requête pour récupérer les informations utilisateur
    $sql = "SELECT pseudonyme, email, birth, created_at FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Vérifier si l'utilisateur existe
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Requête pour récupérer les œuvres d'art de l'utilisateur
        $sqlArtworks = "SELECT id, title, file AS image_url FROM artworks WHERE artist_display_name = ?";
        $stmtArtworks = $conn->prepare($sqlArtworks);
        $stmtArtworks->bind_param("s", $user['pseudonyme']);
        $stmtArtworks->execute();
        $resultArtworks = $stmtArtworks->get_result();

        $artworks = [];
        while ($artwork = $resultArtworks->fetch_assoc()) {
            $artworks[] = $artwork;
        }

        // Retourner les informations utilisateur et souscription au format JSON
        echo json_encode([
            'pseudonyme' => $user['pseudonyme'],
            'email' => $user['email'],
            'birth' => $user['birth'],
            'created_at' => $user['created_at'],
            'artworks' => $artworks
        ]);

    } else {
        echo json_encode(['error' => 'User not found']);
    }

    // Fermer les requêtes et la connexion
    $stmt->close();
    $stmtArtworks->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
