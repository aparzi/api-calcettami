<?php
$app->post('/login', function (Slim\Http\Request $request, Slim\Http\Response $response) {
    $username = $request->getParsedBody()['username'];
    $password = $request->getParsedBody()['pwd'];
    $db = new DBproprierties();
    $conn = $db->getConnection();
    // statement
    $sql = "SELECT * FROM users WHERE username = '$username' AND passw = '$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 0) {
        $response->getBody()->write("utente non trovato");
    } else {
        header('Content-Type: application/json');
        $fetchArrayUser = mysqli_fetch_array($result);
        $user = [
            'nome' => $fetchArrayUser['nome'],
            'cognome' => $fetchArrayUser['cognome'],
            'email' => $fetchArrayUser['email'],
            'username' => $fetchArrayUser['username']
        ];
        print json_encode($user);
    }
});