<?php

use Project\Config;
use Project\LoggerFactory;
use Project\SessionService;
use Project\ImageService;

header('Content-Type: text/html; charset=utf-8');

require_once("vendor/autoload.php");

ini_set('display_errors', 'Off');
ini_set('log_errors', 'On');
ini_set('error_log', Config::LOG_FILE);

$log = LoggerFactory::getLogger('delete');

try {
    $necessaryFields = ['consultantNumber', 'token', 'fileId'];
    foreach ($necessaryFields as $necessaryField) {
        if(!isset($_POST[$necessaryField])) {
            throw new InvalidArgumentException('Field '.$necessaryField.' does not exist in post request');
        }
    }

    $consultantNumber = $_POST['consultantNumber'];
    $sessionService = new SessionService();
    if($sessionService->getToken($consultantNumber) != $_POST['token']) {
        throw new InvalidArgumentException('Incorrect session token: requested value (' . $_POST['token'] . ') not equal to stored value (' . $sessionService->getToken($consultantNumber) . ')');
    }

    $imageService = new ImageService();
    if(!$imageService->delete($consultantNumber, $_POST['fileId']))  {
        throw new RuntimeException('File has not been deleted');
    }

    header("HTTP/1.1 200 OK");
    echo json_encode(['status' => 'success'], JSON_UNESCAPED_UNICODE);
    exit();
}
catch (Exception $e) {
    $log->error('Cannot delete information', [
        'exception' => $e,
        'IP' => $_SERVER['REMOTE_ADDR'],
        'Body' => file_get_contents('php://input'),
        'GET' => $_GET,
        'POST' => $_POST
    ]);
    header('HTTP/1.0 404 Not Found', true, 404);
}
