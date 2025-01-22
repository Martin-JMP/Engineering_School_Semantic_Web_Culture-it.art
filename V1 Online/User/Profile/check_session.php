<?php
session_start();  // Démarre la session PHP

header('Content-Type: application/json');

$servername = "NA";
$username = "NA";
$password = "NA";
$dbname = "NA";

// Vérifie si l'utilisateur est connecté
if (isset($_SESSION['pseudonyme'])) {
    echo json_encode([
        'logged_in' => true,
        'pseudonyme' => $_SESSION['pseudonyme']
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}
?>
