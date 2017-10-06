<?php
/**
 * Created by PhpStorm.
 * User: aparzi
 * Date: 30/09/17
 * Time: 12.19
 */

$app->post('/sendEmail', function (Slim\Http\Request $request, Slim\Http\Response $response) {
    require_once '../PHPMailer-master/class.phpmailer.php';
    $mail = new PHPMailer(true); // Passing `true` enables exceptions
    $username = $request->getParsedBody()['username'];
    $oggetto = $request->getParsedBody()['oggetto'];
    $messaggio = $request->getParsedBody()['messaggio'];

    try {
        //Recipients
        $mail->setFrom('from@example.com', 'Calcettami');
        $mail->addAddress('angeloparziale94@gmail.com');     // Add a recipient
        //$mail->addReplyTo('info@example.com', 'Information');

        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = "Email dall' app Calcettami: " . $oggetto;
        $mail->Body = 'Mittente: '. $username . '. <br><br>' . $messaggio;

        $mail->send();
        return $response->getBody()->write('messaggio inviato');
    } catch (Exception $e) {
        return $response->getBody()->write('Mailer Error: ' . $mail->ErrorInfo);
    }
});
