<?php

use Project\Config;
use Project\LoggerFactory;
use Project\ImageService;

header('Content-Type: text/html; charset=utf-8');

require_once("vendor/autoload.php");

ini_set('display_errors', 'Off');
ini_set('log_errors', 'On');
ini_set('error_log', Config::LOG_FILE);

$log = LoggerFactory::getLogger('approve');

try {
    $necessaryFields = ['id', 'approved'];
    foreach ($necessaryFields as $necessaryField) {
        if(!isset($_POST[$necessaryField])) {
            throw new InvalidArgumentException('Field '.$necessaryField.' does not exist in post request');
        }
    }

    $imageService = new ImageService();
    if(!$imageService->approve($_POST['id'], $_POST['approved']))  {
        throw new RuntimeException('File has not been approved');
    }

    header("HTTP/1.1 200 OK");
    echo json_encode(['status' => 'success'], JSON_UNESCAPED_UNICODE);
    exit();
}
catch (Exception $e) {
    $log->error('Cannot approve file', [
        'exception' => $e,
        'IP' => $_SERVER['REMOTE_ADDR'],
        'Body' => file_get_contents('php://input'),
        'GET' => $_GET,
        'POST' => $_POST
    ]);
    header('HTTP/1.0 404 Not Found', true, 404);
}
