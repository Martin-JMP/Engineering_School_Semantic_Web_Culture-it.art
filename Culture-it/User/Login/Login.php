<?php
// Affiche les erreurs PHP pour le développement (désactiver en production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// SPARQL endpoint
$sparqlEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/query";

// Récupérer l'email du POST
$email = isset($_POST['email']) ? $_POST['email'] : '';

if ($email) {
    // Préparer la requête SPARQL pour récupérer les données de l'utilisateur
    $sparqlQuery = "
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX ag: <http://example.org/artgallery/>

    SELECT ?id ?pseudonyme ?email ?password_hash ?birth ?created_at ?Artist
    WHERE {
        ?user rdf:type ag:User ;
              ag:id ?id ;
              ag:pseudonyme ?pseudonyme ;
              ag:email ?email ;
              ag:password_hash ?password_hash ;
              ag:birth ?birth ;
              ag:created_at ?created_at ;
              ag:Artist ?Artist .
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
        echo json_encode([
            'exists' => true,
            'id' => $user['id']['value'],
            'pseudonyme' => $user['pseudonyme']['value'],
            'email' => $user['email']['value'],
            'password_hash' => $user['password_hash']['value'],
            'birth' => $user['birth']['value'],
            'created_at' => $user['created_at']['value'],
            'Artist' => $user['Artist']['value']
        ]);
    } else {
        echo json_encode(['exists' => false]);
    }
} else {
    echo json_encode(['error' => 'Email is required']);
}
?>
