<?php
session_start();  // Démarre ou reprend la session

// Détruire la session
session_unset();  // Libère toutes les variables de session
session_destroy();  // Détruit la session

// Retourner une réponse JSON
echo json_encode(['success' => true]);
?>
