<?php
$app->post('/checkUsername', function (Slim\Http\Request $request, Slim\Http\Response $response) {
    $username = $request->getParsedBody()['username'];
    $db = new DBproprierties();
    $conn = $db->getConnection();
    // statement
    $sql = "SELECT username FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) != 0) {
        echo 'username presente';
    } else {
        echo 'username non presente';
    }
});