<?php
header('Content-Type: application/json');

// SPARQL Endpoint URL
$sparqlEndpoint = "http://localhost:3030/Test-artworks5OBJECT/query";

// Get the object ID from the GET request
$objectId = $_GET['objectId'];

// SPARQL query to fetch comments
$sparqlQuery = "
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX art: <http://www.culture-it.art/ontology#>
SELECT ?id ?pseudonyme ?comment ?created_at ?interest
WHERE {
  ?commentNode rdf:type art:Comment ;
               art:objectId $objectId ;
               art:commentId ?id ;
               art:comment ?comment ;
               art:pseudonyme ?pseudonyme ;
               art:created_at ?created_at ;
               art:interest ?interest .
}";

// Send the query to the SPARQL endpoint
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $sparqlEndpoint);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['query' => $sparqlQuery]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);

// Execute the query
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo json_encode(['error' => "cURL error: " . curl_error($ch)]);
    exit;
}

curl_close($ch);

// Parse the response
$data = json_decode($response, true);

// Check for valid response
if (!isset($data['results']['bindings'])) {
    echo json_encode(['error' => "Invalid SPARQL query results"]);
    exit;
}

// Extract comments from the response
$comments = [];
foreach ($data['results']['bindings'] as $row) {
    $comments[] = [
        'id' => $row['id']['value'],
        'pseudonyme' => $row['pseudonyme']['value'],
        'comment' => $row['comment']['value'],
        'created_at' => $row['created_at']['value'],
        'interest' => $row['interest']['value']
    ];
}

echo json_encode($comments);
?>