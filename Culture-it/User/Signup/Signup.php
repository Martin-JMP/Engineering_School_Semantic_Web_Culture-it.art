<?php
header('Content-Type: application/json');

// SPARQL endpoint
$sparqlEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/query";
$sparqlUpdateEndpoint = "http://localhost:3030/Test-artworks8OBJECT-USERS/update";

// Vérifier si les données sont envoyées via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données depuis la requête POST
    $pseudonyme = $_POST['pseudonyme'] ?? '';
    $email = $_POST['email'] ?? '';
    $birth = $_POST['birth'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Vérifier que les mots de passe correspondent
    if ($password !== $confirm_password) {
        echo json_encode(['status' => 'error', 'message' => 'Passwords do not match!']);
        exit();
    }

    // Vérifier que l'email n'existe pas déjà dans la base de données
    $sparqlQueryEmailCheck = "
    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>
    SELECT ?email
    WHERE {
        ?user rdf:type ag:User ;
            ag:email ?email .
        FILTER(?email = \"$email\")
    }";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sparqlEndpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('query' => $sparqlQueryEmailCheck)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $responseEmailCheck = curl_exec($ch);
    $dataEmailCheck = json_decode($responseEmailCheck, true);

    if (!empty($dataEmailCheck['results']['bindings'])) {
        echo json_encode(['status' => 'error', 'message' => 'Email already exists!']);
        curl_close($ch);
        exit();
    }

    // Vérifier que le pseudo n'existe pas déjà dans la base de données
    $sparqlQueryPseudonymeCheck = "
    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>
    SELECT ?pseudonyme
    WHERE {
        ?user rdf:type ag:User ;
        ag:pseudonyme ?pseudonyme .
        FILTER(?pseudonyme = \"$pseudonyme\")
        }";

    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('query' => $sparqlQueryPseudonymeCheck)));
    $responsePseudonymeCheck = curl_exec($ch);
    $dataPseudonymeCheck = json_decode($responsePseudonymeCheck, true);

    if (!empty($dataPseudonymeCheck['results']['bindings'])) {
        echo json_encode(['status' => 'error', 'message' => 'Pseudonyme already exists!']);
        curl_close($ch);
        exit();
    }

    // Hacher le mot de passe avec bcrypt
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Récupérer le dernier ID utilisateur
    $sparqlQueryMaxId = "
    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>
    SELECT (MAX(?id) AS ?maxId)
    WHERE {
        ?user rdf:type ag:User ;
              ag:id ?id .
    }";

    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('query' => $sparqlQueryMaxId)));
    $responseMaxId = curl_exec($ch);
    $dataMaxId = json_decode($responseMaxId, true);
    curl_close($ch);

    $newId = 1;
    if (!empty($dataMaxId['results']['bindings'])) {
        $newId = intval($dataMaxId['results']['bindings'][0]['maxId']['value']) + 1;
    }

    // Insérer l'utilisateur dans la base de données
    $sparqlUpdateQuery = "
    PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>
    PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
    PREFIX ag: <http://example.org/artgallery/>
    INSERT DATA {
        _:user rdf:type ag:User ;
               ag:id \"$newId\"^^<http://www.w3.org/2001/XMLSchema#integer> ;
               ag:pseudonyme \"$pseudonyme\" ;
               ag:email \"$email\" ;
               ag:password_hash \"$hashed_password\" ;
               ag:birth \"$birth\"^^<http://www.w3.org/2001/XMLSchema#date> ;
               ag:created_at \"" . date('Y-m-d\TH:i:s') . "\"^^<http://www.w3.org/2001/XMLSchema#dateTime> ;
               ag:Artist \"0\"^^<http://www.w3.org/2001/XMLSchema#integer> .
    }";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $sparqlUpdateEndpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('update' => $sparqlUpdateQuery)));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $updateResponse = curl_exec($ch);
    curl_close($ch);

    if ($updateResponse === false) {
        echo json_encode(['status' => 'error', 'message' => 'Error registering user!']);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'User registered successfully!']);
    }
}
?>
