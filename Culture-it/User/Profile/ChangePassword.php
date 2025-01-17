<?php
session_start();

// Vérification que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Récupérer les données de la requête POST
$data = json_decode(file_get_contents("php://input"), true);

$current_password = $data['current_password'];
$new_password = $data['new_password'];

// SPARQL endpoint
$sparqlEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/query";
$sparqlUpdateEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/update";

// Vérifier si le mot de passe actuel est correct
$user_id = $_SESSION['user_id'];
$sparqlQuery = "
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX ag: <http://example.org/artgallery/>
SELECT ?password_hash
WHERE {
    ?user rdf:type ag:User ;
          ag:id \"$user_id\"^^<http://www.w3.org/2001/XMLSchema#integer> ;
          ag:password_hash ?password_hash .
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

if (!empty($data['results']['bindings'])) {
    $user = $data['results']['bindings'][0];
    $hashed_password = $user['password_hash']['value'];

    // Vérifier le mot de passe actuel
    if (password_verify($current_password, $hashed_password)) {
        // Hacher le nouveau mot de passe
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Préparer la requête SPARQL pour mettre à jour le mot de passe
        $sparqlUpdateQuery = "
        PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
        PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
        PREFIX ag: <http://example.org/artgallery/>
        DELETE {
            ?user ag:password_hash ?password_hash .
        }
        INSERT {
            ?user ag:password_hash \"$new_hashed_password\" .
        }
        WHERE {
            ?user rdf:type ag:User ;
                  ag:id \"$user_id\"^^<http://www.w3.org/2001/XMLSchema#integer> ;
                  ag:password_hash ?password_hash .
        }";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $sparqlUpdateEndpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('update' => $sparqlUpdateQuery)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $updateResponse = curl_exec($ch);
        curl_close($ch);

        if ($updateResponse) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Failed to update password']);
        }
    } else {
        echo json_encode(['error' => 'Current password is incorrect']);
    }
} else {
    echo json_encode(['error' => 'User not found']);
}
?>
