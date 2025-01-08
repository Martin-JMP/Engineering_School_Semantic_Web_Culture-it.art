<?php
session_start();  // Démarre la session PHP

header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté
if (isset($_SESSION['pseudonyme'])) {
    echo json_encode([
        'logged_in' => true,
        'pseudonyme' => $_SESSION['pseudonyme'],
        'email' => $_SESSION['email']
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}
?>
