<?php
// SPARQL Endpoint URL
$sparqlEndpoint = "http://localhost:3030/Test-artworks5OBJECT/query";

// The SPARQL query
$sparqlQuery = <<<SPARQL
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX art: <http://www.culture-it.art/ontology#>

SELECT 
  ?objectId 
  ?nameArt 
  ?medium 
  ?dimension 
  ?yearCreatedStart 
  ?yearCreatedEnd 
  ?authorName 
  ?authorNationality 
  ?authorBirthYear 
  ?authorDeathYear 
  ?repositoryName 
  ?repositoryCity
WHERE {
  ?artwork rdf:type art:Artwork ;
           art:objectId 5059 ;
           art:hasNameArt ?nameArt ;
           art:medium ?medium ;
           art:dimension ?dimension ;
           art:yearCreatedStart ?yearCreatedStart ;
           art:yearCreatedEnd ?yearCreatedEnd ;
           art:hasAuthor ?authorNode ;
           art:Located ?repositoryNode .

  # Author details
  ?authorNode art:hasNameAuth ?authorName ;
              art:nationality ?authorNationality ;
              art:birthYear ?authorBirthYear ;
              art:deathYear ?authorDeathYear .

  # Repository details
  ?repositoryNode art:hasNameRep ?repositoryName ;
                  art:inCity ?repositoryCity .
}
SPARQL;

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
    die("cURL error: " . curl_error($ch));
}

curl_close($ch);

// Parse the response
$data = json_decode($response, true);

// Debugging: Output the raw response for inspection
if (!$data) {
    die("Error: Unable to decode JSON response. Raw response: " . htmlspecialchars($response));
}

// Check for valid response
if (!isset($data['results']['bindings'])) {
    die("Error: Invalid SPARQL query results. Raw response: " . htmlspecialchars($response));
}

// Display the data in an HTML table
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPARQL Query Results</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h1>SPARQL Query Results</h1>
    <table>
        <thead>
            <tr>
                <th>Object ID</th>
                <th>Name</th>
                <th>Medium</th>
                <th>Dimension</th>
                <th>Year Created Start</th>
                <th>Year Created End</th>
                <th>Author Name</th>
                <th>Author Nationality</th>
                <th>Author Birth Year</th>
                <th>Author Death Year</th>
                <th>Repository Name</th>
                <th>Repository City</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['results']['bindings'] as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['objectId']['value'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['nameArt']['value'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['medium']['value'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['dimension']['value'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['yearCreatedStart']['value'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['yearCreatedEnd']['value'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['authorName']['value'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['authorNationality']['value'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['authorBirthYear']['value'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['authorDeathYear']['value'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['repositoryName']['value'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['repositoryCity']['value'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
