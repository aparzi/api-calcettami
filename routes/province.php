<?php
$app->get('/province/{codRegione}', function (Slim\Http\Request $request, Slim\Http\Response $response) {
    $codRegione = $request->getAttribute('codRegione');
    $db = new DBproprierties();
    $conn = $db->getConnection();
    // statement
    $sql = "SELECT * FROM province WHERE codISTATr = '$codRegione'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 0) {
        $response->getBody()->write("nessuna provincia");
    } else {
        header('Content-Type: application/json');
        $x = 0;
        while ($row = mysqli_fetch_array($result)) {
            $province[$x]['nome'] = $row['nomeProvincia'];
            $province[$x]['sigla'] = $row['siglaProvincia'];
            $x++;
        }
        print json_encode($province);
    }
});