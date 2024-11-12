<?php
session_start();  // Démarre la session PHP

header('Content-Type: application/json');

// Vérifie si l'utilisateur est connecté
if (isset($_SESSION['first_name'])) {
    echo json_encode([
        'logged_in' => true,
        'first_name' => $_SESSION['first_name'],
        'email' => $_SESSION['email']
    ]);
} else {
    echo json_encode(['logged_in' => false]);
}
?>
