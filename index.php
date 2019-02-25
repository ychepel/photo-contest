<?php

use Project\ConsultantService;
use Project\LoggerFactory;
use Project\SessionService;
use Project\ImageService;
use Project\DataService;

header('Content-Type: text/html; charset=utf-8');

require_once("vendor/autoload.php");

$log = LoggerFactory::getLogger('index');

try {
    $consultantService = new ConsultantService();
    $consultantNumber = $consultantService->getUserConsultantNumber();

    SessionService::registerConsultantSession($consultantNumber);

    $sessionService = new SessionService();
    $sessionToken = $sessionService->getToken($consultantNumber);

    if(!$consultant = $consultantService->getConsultant($consultantNumber)) {
        throw new InvalidArgumentException('Cannot get consultant data');
    }

    if(isset($_GET['p'])) {
        if($_GET['p'] == 'unit' && $consultant->isDirector()) {
            $dataService = new DataService();
            $unitData = $dataService->getUnitData($consultant->unitNumber);
            include('template/unit.php');
            exit();
        }
    }

    $imageService = new ImageService();
    $images = $imageService->getConsultantImages($consultantNumber);

    include('template/main.php');
}
catch (Exception $e) {
    $log->error('Cannot show information', ['exception' => $e]);
    include ('template/errorpage/index.html');
    exit();
}