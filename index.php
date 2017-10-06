<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require ('vendor/autoload.php');
require ('storage/DBproperties.php');
// Create and configure Slim app
$config = ['settings' => [
    'addContentLengthHeader' => false,
    'displayErrorDetails' => true,
]];
$app = new \Slim\App($config);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
// define Routes
include 'routes/login.php';
include 'routes/registrer.php';
include 'routes/checkUsername.php';
include 'routes/games.php';
include 'routes/province.php';
include 'routes/email.php';

$app->run();

