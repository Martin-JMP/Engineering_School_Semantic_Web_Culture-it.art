<?php
$servername = "cultubq333.mysql.db";
$username = "cultubq333";
$password = "Semantic789";
$dbname = "cultubq333";

$query = $_GET['query'] ?? '';

if (empty($query)) {
    echo json_encode([]);
    exit;
}

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("
        SELECT title, description FROM artworks WHERE title LIKE ? OR description LIKE ?
        UNION
        SELECT comment AS title, comment AS description FROM comments WHERE comment LIKE ?
        LIMIT 5
    ");
    $likeQuery = '%' . $query . '%';
    $stmt->bind_param("sss", $likeQuery, $likeQuery, $likeQuery);
    $stmt->execute();
    $result = $stmt->get_result();

    $results = [];
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }

    echo json_encode($results);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
