<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $artworkId = $input['artwork_id'];
    $imageUrl = $input['image_url'];

    // SPARQL Endpoint URL
    $sparqlEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/update";

    // SPARQL query to delete the artwork data
    $sparqlQuery = "
    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>
    DELETE WHERE {
      ?artwork rdf:type ag:Artwork ;
               ag:id \"$artworkId\"^^<http://www.w3.org/2001/XMLSchema#integer> ;
               ag:file \"$imageUrl\" .
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
        echo json_encode(['success' => false, 'error' => "cURL error: " . curl_error($ch)]);
        exit;
    }

    curl_close($ch);

    // Check the response
    if ($response === false) {
        echo json_encode(['success' => false, 'error' => "Failed to delete data"]);
    } else {
        // Delete artwork image from filesystem
        $imagePath = '../../Upload/' . $imageUrl;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        echo json_encode(['success' => true]);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
?>
