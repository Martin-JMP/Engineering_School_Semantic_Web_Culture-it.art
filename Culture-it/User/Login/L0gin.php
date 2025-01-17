<?php
session_start();  // Démarre la session PHP

ini_set('display_errors', 0);  // Désactiver l'affichage des erreurs
error_reporting(E_ALL);  // Enregistrer toutes les erreurs

header('Content-Type: application/json');

// SPARQL endpoint
$sparqlEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/query";

// Récupérer l'email et le mot de passe soumis
if (!empty($_POST['email']) && !empty($_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Préparer la requête SPARQL pour vérifier si l'email existe et récupérer les informations associées
    $sparqlQuery = "
    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>
    SELECT ?id ?pseudonyme ?email ?password_hash
    WHERE {
        ?user rdf:type ag:User ;
              ag:id ?id ;
              ag:pseudonyme ?pseudonyme ;
              ag:email ?email ;
              ag:password_hash ?password_hash .
        FILTER(?email = \"$email\")
    }";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sparqlEndpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('query' => $sparqlQuery)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);

    // Vérifier si l'email existe et retourner les données de l'utilisateur
    if (!empty($data['results']['bindings'])) {
        $user = $data['results']['bindings'][0];
        $hashed_password = $user['password_hash']['value'];

        // Vérification du mot de passe
        if (password_verify($password, $hashed_password)) {
            // Mot de passe correct, démarrer la session
            $_SESSION['user_id'] = $user['id']['value'];
            $_SESSION['pseudonyme'] = $user['pseudonyme']['value'];
            $_SESSION['email'] = $user['email']['value'];

            echo json_encode(['success' => true, 'pseudonyme' => $user['pseudonyme']['value']]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid password']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Email not found']);
    }
} else {
    echo json_encode(['error' => 'Email and password are required']);
}
?>
