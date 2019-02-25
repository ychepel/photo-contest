<?php

use Project\Config;
use Project\ImageService;
use Project\LoggerFactory;
use Project\SessionService;

header('Content-Type: text/html; charset=utf-8');

require_once("vendor/autoload.php");

ini_set('display_errors', 'Off');
ini_set('log_errors', 'On');
ini_set('error_log', Config::LOG_FILE);

$log = LoggerFactory::getLogger('upload');

try {
    $necessaryFields = ['consultantNumber', 'token', 'file', 'fileName'];
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
    $imageHash = md5($_POST['file']);
    $fileId = null;
    $filePath = null;
    if($imageService->isHashExistsInUserProfile($imageHash, $consultantNumber)) {
        $status = 'exists-in-profile';
    }
    elseif($imageService->isHashExists($imageHash)) {
        $status = 'exists-in-base';
    }
    else {
        $fileId = $imageService->upload($consultantNumber, $_POST['fileName'], $_POST['file']);
        $filePath = $imageService->getThumbnailPathById($fileId);
        $status = 'success';
    }

    header("HTTP/1.1 200 OK");
    echo json_encode(['status' => $status, 'fileId' => $fileId, 'filePath' => $filePath], JSON_UNESCAPED_UNICODE);
    exit();
}
catch (Exception $e) {
    $log->error('Cannot upload information', [
        'exception' => $e,
        'IP' => $_SERVER['REMOTE_ADDR'],
    ]);
    header('HTTP/1.0 404 Not Found', true, 404);
}
