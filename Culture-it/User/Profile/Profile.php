<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

header('Content-Type: application/json');

// SPARQL endpoint
$sparqlEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/query";

// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];

try {
    // Préparer la requête SPARQL pour récupérer les informations de l'utilisateur
    $sparqlQueryUser = "
    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>
    SELECT ?pseudonyme ?email ?birth ?created_at ?artist
    WHERE {
        ?user rdf:type ag:User ;
              ag:id \"$user_id\"^^<http://www.w3.org/2001/XMLSchema#integer> ;
              ag:pseudonyme ?pseudonyme ;
              ag:email ?email ;
              ag:birth ?birth ;
              ag:created_at ?created_at ;
              ag:Artist ?artist .
    }";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sparqlEndpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('query' => $sparqlQueryUser)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $responseUser = curl_exec($ch);
    $dataUser = json_decode($responseUser, true);

    if (empty($dataUser['results']['bindings'])) {
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    $user = $dataUser['results']['bindings'][0];
    $isArtist = $user['artist']['value'] == "1";

    // Préparer la requête SPARQL pour récupérer les œuvres d'art de l'utilisateur
    $sparqlQueryArtworks = "
    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>
    SELECT ?id ?title ?file ?object_id
    WHERE {
        ?artwork rdf:type ag:Artwork ;
                 ag:artist_display_name \"{$user['pseudonyme']['value']}\" ;
                 ag:id ?id ;
                 ag:object_id ?object_id ;
                 ag:title ?title ;
                 ag:file ?file .
    }";

    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('query' => $sparqlQueryArtworks)));
    $responseArtworks = curl_exec($ch);
    curl_close($ch);

    $dataArtworks = json_decode($responseArtworks, true);
    $artworks = [];

    foreach ($dataArtworks['results']['bindings'] as $artwork) {
        $artworks[] = [
            'id' => $artwork['id']['value'],
            'object_id' => $artwork['object_id']['value'],
            'title' => $artwork['title']['value'],
            'image_url' => $artwork['file']['value']
        ];
    }

    // Retourner les informations utilisateur et œuvres d'art au format JSON
    echo json_encode([
        'pseudonyme' => $user['pseudonyme']['value'],
        'email' => $user['email']['value'],
        'birth' => $user['birth']['value'],
        'created_at' => $user['created_at']['value'],
        'is_artist' => $isArtist,
        'artworks' => $artworks
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
