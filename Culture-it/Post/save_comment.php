<?php
session_start();
header('Content-Type: application/json');

// SPARQL Endpoint URL
$sparqlEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/update";
$sparqlQueryEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/query";

// Get the data from the POST request
$userName = $_POST['userName'] ?? null;
$commentText = $_POST['commentText'] ?? null;
$commentDate = $_POST['commentDate'] ?? null;
$objectId = $_POST['objectId'] ?? null;

// Log received POST data for debugging
error_log("Received POST data: " . json_encode($_POST));

// Log each individual POST data field for debugging
error_log("userName: " . $userName);
error_log("commentText: " . $commentText);
error_log("commentDate: " . $commentDate);
error_log("objectId: " . $objectId);

if (!$userName || !$commentText || !$commentDate || !$objectId) {
    echo json_encode(['error' => "Missing required data"]);
    exit;
}

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    echo json_encode(['error' => "User not logged in"]);
    exit;
}

try {
    // Retrieve the last registered comment ID
    $sparqlQueryMaxId = "
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>
    SELECT (MAX(?id) AS ?maxId)
    WHERE {
        ?comment rdf:type ag:Comment ;
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

    // SPARQL query to insert the comment
    $sparqlQuery = "
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>
    INSERT DATA {
        _:newComment rdf:type ag:Comment ;
                     ag:object_id \"$objectId\" ;
                     ag:id \"$newId\"^^<http://www.w3.org/2001/XMLSchema#integer> ;
                     ag:comment \"$commentText\" ;
                     ag:pseudonyme \"$userName\" ;
                     ag:created_at \"$commentDate\" ;
                     ag:interest 0 .
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
        echo json_encode(['error' => "Failed to save comment"]);
    } else {
        echo json_encode(['success' => true, 'id' => $newId], JSON_UNESCAPED_UNICODE);
    }

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>