<?php
$app->post('/registrer', function (Slim\Http\Request $request, Slim\Http\Response $response) {
    $nome = $request->getParsedBody()['name'];
    $cognome = $request->getParsedBody()['surname'];
    $email = $request->getParsedBody()['email'];
    $username = $request->getParsedBody()['username'];
    $password = $request->getParsedBody()['pwd'];
    $db = new DBproprierties();
    $conn = $db->getConnection();
    // statement
    $sql = "INSERT INTO users (nome, cognome, email, username, passw) VALUES ('$nome', '$cognome', '$email', '$username', '$password')";

    if (mysqli_query($conn, $sql)) {
        echo 'utente inserito';
    } else {
        echo 'utente non inserito';
    }
});