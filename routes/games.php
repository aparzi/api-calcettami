<?php
define('DIFF_VALID', '01:00');
define('ORARIO_FINE_NON_VALIDO', false);
define('MATCHES_NOT_FOUND', 'nessuna partita');
/**
 * Inserisce un match
 */
$app->post('/game', function (Slim\Http\Request $request, Slim\Http\Response $response) {
    $oraInizio = $request->getParsedBody()['ora_inizio'];
    $oraFine = $request->getParsedBody()['ora_fine'];
    if (!checkTime($oraInizio, $oraFine)) {
        return $response->getBody()->write('Orari non validi');
    }

    $giorno = $request->getParsedBody()['giorno'];
    $regione = $request->getParsedBody()['regione'];
    $provincia = $request->getParsedBody()['provincia'];
    $comune = $request->getParsedBody()['comune'];
    $nomeCampo = $request->getParsedBody()['nome_campo'];
    $descrizione = $request->getParsedBody()['descrizione'];
    $organizzatore = $request->getParsedBody()['organizzatore'];

    $db = new DBproprierties();
    $conn = $db->getConnection();
    // statement
    $descrizione = addslashes($descrizione);
    $sql = "INSERT INTO partite (orario_inizio, orario_fine, giorno, regione, provincia, comune, nome_campo, descrizione, organizzatore) VALUES 
            ('$oraInizio', '$oraFine', '$giorno', '$regione', '$provincia', '$comune', '$nomeCampo', '$descrizione', '$organizzatore')";

    if (mysqli_query($conn, $sql)) {
        echo 'partita salvata';
    } else {
        echo 'errore nell inserimento', mysqli_error($conn);
    }
});

/**
 * Restituisce i match organizzati dall' utente
 */
$app->get('/games/organizzatore/{username}', function (Slim\Http\Request $request, Slim\Http\Response $response) {
    $username = $request->getAttribute('username');

    $db = new DBproprierties();
    $conn = $db->getConnection();
    // statement
    $sql = "SELECT * FROM partite WHERE organizzatore = '$username'";

    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 0) {
        echo MATCHES_NOT_FOUND;
    } else {
        $index = 0;
        while ($row = mysqli_fetch_array($result)) {
            $games[$index]['ora_inizio'] = $row['orario_inizio'];
            $games[$index]['ora_fine'] = $row['orario_fine'];
            $games[$index]['data'] = $row['giorno'];
            $games[$index]['descrizione'] = $row['descrizione'];
            $games[$index]['comune'] = $row['comune'];
            $games[$index]['campo'] = $row['nome_campo'];
            $index++;
        }
        echo json_encode($games);
    }
});

/**
 * Restituisce i match in base ai filtri applicati dall'utente e alle partite in cui l'utente non è iscritto
 */
$app->post('/researchGames', function (Slim\Http\Request $request, Slim\Http\Response $response) {
    $regione = $request->getParsedBody()['regione'];
    $provincia = $request->getParsedBody()['provincia'];
    $ora_inizio = $request->getParsedBody()['ora_inizio'];
    $ora_fine = $request->getParsedBody()['ora_fine'];
    $giorno = getCurrentDate();
    $username = $request->getParsedBody()['username'];

    $db = new DBproprierties();
    $conn = $db->getConnection();
    // statement che restituisce la partite in base ai filtri
    $sql = "SELECT * FROM partite 
            WHERE regione = '$regione' AND provincia = '$provincia' AND giorno >= '$giorno' 
            AND orario_inizio >= '$ora_inizio' AND orario_fine <= '$ora_fine' AND iscritti < 10";

    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 0) {
        return $response->getBody()->write(MATCHES_NOT_FOUND);
    } else {
        $index = 0;
        while ($row = mysqli_fetch_array($result)) {
            // statement che controlla le partite in cui l'utente è iscritto
            $sql = "SELECT * FROM partite_users WHERE id_partita = ".$row['id']." AND id_user = '$username'";
            $result2 = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result2) == 0) {
                $arrayPartite[$index]['id'] = $row['id'];
                $arrayPartite[$index]['ora_inizio'] = $row['orario_inizio'];
                $arrayPartite[$index]['ora_fine'] = $row['orario_fine'];
                $arrayPartite[$index]['data'] = $row['giorno'];
                $arrayPartite[$index]['comune'] = $row['comune'];
                $arrayPartite[$index]['campo'] = $row['nome_campo'];
                $arrayPartite[$index]['descrizione'] = $row['descrizione'];
                $arrayPartite[$index]['organizzatore'] = $row['organizzatore'];
                $arrayPartite[$index]['iscritti'] = $row['iscritti'];
                $index++;
            }
        }

       if (sizeof($arrayPartite) == 0) {
           return $response->getBody()->write(MATCHES_NOT_FOUND);
       } else {
            echo json_encode($arrayPartite);
       }
    }
});

/**
 * Iscrive un utente al match, come parametri bisogna passargli id_partita (int) e l'username dell'utente (stringa)
 */
$app->post('/joinMatch', function (Slim\Http\Request $request, Slim\Http\Response $response) {
    $idPartita = $request->getParsedBody()['id_partita'];
    $username = $request->getParsedBody()['username'];

    $db = new DBproprierties();
    $conn = $db->getConnection();
    // statement
    $sql = "INSERT INTO partite_users (id_partita, id_user) VALUES ($idPartita, '$username')";

    if (mysqli_query($conn, $sql)) {
        $sql = "UPDATE partite SET iscritti = iscritti + 1 WHERE id = $idPartita";
        if (mysqli_query($conn, $sql)) {
            return $response->getBody()->write('utente iscritto');
        } else {
            return $response->getBody()->write('errore: ' . mysqli_error($conn));
        }
    } else {
        return $response->getBody()->write('errore: ' . mysqli_error($conn));
    }
});

/**
 * Restituisce i match a cui un utente si è iscritto e dovrò partecipare
 */
$app->get('/matchScheduled/{username}', function (Slim\Http\Request $request, Slim\Http\Response $response) {
    $username = $request->getAttribute('username');
    $currentDate = getCurrentDate();

    $db = new DBproprierties();
    $conn = $db->getConnection();
    // statement
    $sql = "SELECT partite.id, partite.orario_inizio, partite.orario_fine, partite.giorno, partite.comune, partite.nome_campo, 
            partite.descrizione, partite.organizzatore, partite.iscritti FROM partite JOIN partite_users 
            ON (partite.id = partite_users.id_partita) AND partite_users.id_user = '$username' AND partite.giorno >= '$currentDate'";

    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) == 0) {
        return $response->getBody()->write('nessuna partita in programma');
    } else {
        $index = 0;
        while ($row = mysqli_fetch_array($result)) {
            $games[$index]['id'] = $row['id'];
            $games[$index]['ora_inizio'] = $row['orario_inizio'];
            $games[$index]['ora_fine'] = $row['orario_fine'];
            $games[$index]['data'] = $row['giorno'];
            $games[$index]['comune'] = $row['comune'];
            $games[$index]['campo'] = $row['nome_campo'];
            $games[$index]['descrizione'] = $row['descrizione'];
            $games[$index]['organizzatore'] = $row['organizzatore'];
            $games[$index]['iscritti'] = $row['iscritti'];
            $index++;
        }
        echo json_encode($games);
    }
});

/**
 * Cancella un utente da una partita che dovrà giocare
 */
$app->delete('/deleteUser/{username}/{id_match}', function (Slim\Http\Request $request, Slim\Http\Response $response) {
    $username = $request->getAttribute('username');
    $idMatch = $request->getAttribute('id_match');

    $db = new DBproprierties();
    $conn = $db->getConnection();
    // statement
    $sql = "DELETE FROM partite_users WHERE id_partita = $idMatch AND id_user =  '$username'";

    if (mysqli_query($conn, $sql)) {
        $sql = "UPDATE partite SET iscritti = iscritti - 1 WHERE id = $idMatch";
        if (mysqli_query($conn, $sql)) {
            return $response->getBody()->write('utente cancellato');
        } else {
            return $response->getBody()->write('errore: ' . mysqli_error($conn));
        }
    } else {
        return $response->getBody()->write('errore ' . mysqli_error($conn));
    }
});

/**
 * @param $start
 * @param $end
 * @return bool
 * Funzione che controlla se gli orari hanno una distanza di massimo un ora.
 */

function checkTime($start, $end) {
    $datetime1 = new DateTime($start);
    $datetime2 = new DateTime($end);
    if ($end <= $start) {
        return ORARIO_FINE_NON_VALIDO;
    }
    $interval = $datetime2->diff($datetime1);
    if ($interval->format('%H:%I') == DIFF_VALID) {
        return true;
    } else {
        return ORARIO_FINE_NON_VALIDO;
    }
}

/**
 * @return false|string
 * Restituisce la data attuale con il seguente formato: YYYY-MM-DD
 */
function getCurrentDate() {
    return date('Y-m-d');
}

