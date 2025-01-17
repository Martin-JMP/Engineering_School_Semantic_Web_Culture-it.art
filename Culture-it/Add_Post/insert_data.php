<?php
header('Content-Type: application/json');
session_start();

// SPARQL Endpoint URL
$sparqlEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/update";
$sparqlQueryEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/query";

// Retrieve POST data
$object_id = 9999999;
$title = $_POST['artwork-title'] ?? null;
$object_end_date = $_POST['artwork-end-date'] ?? null;
$medium = $_POST['artwork-medium'] ?? null;
$culture = $_POST['artwork-culture'] ?? null;
$dimensions = $_POST['artwork-dimensions'] ?? null;
$country = $_POST['artwork-country'] ?? null;
$description = $_POST['artwork-description'] ?? null;
$exact_file_name = $_POST['exact-file-name'] ?? null;
$artist_display_name = $_SESSION['pseudonyme'] ?? null;  // Retrieve artist display name from session

// Log received POST data for debugging
error_log("Received POST data: " . json_encode($_POST));

// Log each individual POST data field for debugging
error_log("artwork-title: " . $title);
error_log("artwork-end-date: " . $object_end_date);
error_log("artwork-medium: " . $medium);
error_log("artwork-culture: " . $culture);
error_log("artwork-dimensions: " . $dimensions);
error_log("artwork-country: " . $country);
error_log("artwork-description: " . $description);
error_log("exact-file-name: " . $exact_file_name);
error_log("artist_display_name: " . $artist_display_name);

if (!$title) {
    echo json_encode(['error' => "Missing required data: artwork-title"]);
    exit;
}
if (!$object_end_date) {
    echo json_encode(['error' => "Missing required data: artwork-end-date"]);
    exit;
}
if (!$medium) {
    echo json_encode(['error' => "Missing required data: artwork-medium"]);
    exit;
}
if (!$culture) {
    echo json_encode(['error' => "Missing required data: artwork-culture"]);
    exit;
}
if (!$dimensions) {
    echo json_encode(['error' => "Missing required data: artwork-dimensions"]);
    exit;
}
if (!$country) {
    echo json_encode(['error' => "Missing required data: artwork-country"]);
    exit;
}
if (!$description) {
    echo json_encode(['error' => "Missing required data: artwork-description"]);
    exit;
}
if (!$exact_file_name) {
    echo json_encode(['error' => "Missing required data: exact-file-name"]);
    exit;
}
if (!$artist_display_name) {
    echo json_encode(['error' => "Missing required data: artist_display_name"]);
    exit;
}

try {
    // Retrieve the last registered ID
    $sparqlQueryMaxId = "
    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>
    SELECT (MAX(?id) AS ?maxId)
    WHERE {
        ?artwork rdf:type ag:Artwork ;
                 ag:id ?id .
    }";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sparqlQueryEndpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('query' => $sparqlQueryMaxId)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $responseMaxId = curl_exec($ch);
    $dataMaxId = json_decode($responseMaxId, true);
    curl_close($ch);

    $newId = 1;
    if (!empty($dataMaxId['results']['bindings'])) {
        $newId = intval($dataMaxId['results']['bindings'][0]['maxId']['value']) + 1;
    }

    // SPARQL query to insert the artwork data
    $sparqlQuery = "
    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>
    INSERT DATA {
      _:artwork rdf:type art:Artwork ;
                ag:id \"$newId\"^^<http://www.w3.org/2001/XMLSchema#integer> ;
                ag:object_id $object_id ;
                ag:title \"$title\" ;
                ag:object_end_date \"$object_end_date\" ;
                ag:medium \"$medium\" ;
                ag:culture \"$culture\" ;
                ag:dimensions \"$dimensions\" ;
                ag:country \"$country\" ;
                ag:description \"$description\" ;
                ag:file \"$exact_file_name\" ;
                ag:artist_display_name \"$artist_display_name\" .
    }";

    // Send the query to the SPARQL endpoint
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sparqlEndpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['update' => $sparqlQuery]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

    // Execute the query
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo json_encode(['error' => "cURL error: " . curl_error($ch)]);
        exit;
    }

    curl_close($ch);

    // Check the response
    if ($response === false) {
        echo json_encode(['error' => "Failed to insert data"]);
    } else {
        echo json_encode(['success' => true, 'id' => $newId]);
    }

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>