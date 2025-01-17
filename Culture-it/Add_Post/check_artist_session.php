<?php
session_start();
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['pseudonyme'])) {
    echo json_encode(['logged_in' => false]);
    exit;
}

// SPARQL endpoint
$sparqlEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/query";

$pseudonyme = $_SESSION['pseudonyme'];

try {
    // Prepare the SPARQL query to check if the user is an artist
    $sparqlQuery = "
    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>
    SELECT ?artist
    WHERE {
        ?user rdf:type ag:User ;
              ag:pseudonyme \"$pseudonyme\" ;
              ag:Artist ?artist .
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

    if (empty($data['results']['bindings'])) {
        echo json_encode(['logged_in' => true, 'is_artist' => false]);
    } else {
        $isArtist = $data['results']['bindings'][0]['artist']['value'] == "1";
        echo json_encode(['logged_in' => true, 'is_artist' => $isArtist]);
    }

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>