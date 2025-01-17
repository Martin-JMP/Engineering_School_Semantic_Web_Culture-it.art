<?php
header('Content-Type: application/json');

// SPARQL Endpoint URL
$sparqlEndpoint = "http://localhost:3030/Test-artworks7OBJECT-USERS/update";

// Get the comment ID and action from the POST request
$commentId = $_POST['commentId'] ?? null;
$action = $_POST['action'] ?? null;

if (!$commentId || !$action) {
    echo json_encode(['error' => "Missing commentId or action"]);
    exit;
}

// Determine the SPARQL query based on the action
if ($action === 'add') {
    $sparqlQuery = "
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>
    DELETE { ?comment ag:interest ?interest }
    INSERT { ?comment ag:interest ?newInterest }
    WHERE {
        ?comment ag:id \"$commentId\" ;
                 ag:interest ?interest .
        BIND((?interest + 1) AS ?newInterest)
    }";
} elseif ($action === 'remove') {
    $sparqlQuery = "
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>
    DELETE { ?comment ag:interest ?interest }
    INSERT { ?comment ag:interest ?newInterest }
    WHERE {
        ?comment ag:id \"$commentId\" ;
                 ag:interest ?interest .
        BIND((?interest - 1) AS ?newInterest)
    }";
} else {
    echo json_encode(['error' => "Invalid action"]);
    exit;
}

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
    echo json_encode(['error' => "Failed to update interest"]);
} else {
    echo json_encode(['success' => true]);
}
?>