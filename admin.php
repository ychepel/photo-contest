<?php

ini_set('display_errors', 'Off');

use Project\ConsultantService;
use Project\ImageService;
use Project\LoggerFactory;
use Project\SessionService;

require_once("vendor/autoload.php");
$log = LoggerFactory::getLogger('admin');

try {
    $showAdminPage = false;
    $isSuperAdmin = false;
    if(isset($_POST['user']) && isset($_POST['password'])) {
        $sessionService = new SessionService();
        if($sessionService->checkAuthentication($_POST['user'], $_POST['password'])) {
            $showAdminPage = true;
            $isSuperAdmin = $sessionService->isUserSuperAdmin($_POST['user']);
        }
    }
    elseif(isset($_COOKIE['VitaminAdminToken'])) {
        $sessionService = new SessionService();
        if($sessionService->checkAdminToken($_COOKIE['VitaminAdminToken'])) {
            $showAdminPage = true;
            $isSuperAdmin = $sessionService->isSessionOfSuperAdmin($_COOKIE['VitaminAdminToken']);
        }
    }

    if($showAdminPage) {
        $imageService = new ImageService();
        if(isset($_GET['profile'])) {
            $consultantNumber = $_GET['profile'];
            $consultantService = new ConsultantService();
            if(!$consultant = $consultantService->getConsultant($consultantNumber)) {
                throw new InvalidArgumentException('Wrong consultantnumber parameter = '.$consultantNumber);
            }

            $photoGroups = [
                'Pending photos' => $imageService->getConsultantPendingPhotos($consultantNumber),
                'Approved photos' => $imageService->getConsultantApprovedPhotos($consultantNumber),
                'Unapproved photos' => $imageService->getConsultantUnapprovedPhotos($consultantNumber),
                'Deleted photos' => $imageService->getConsultantDeletedPhotos($consultantNumber),
            ];
            include('template/profile.php');
        }
        else {
            $photos = $imageService->getWaitingPhotos();
            include('template/approving.php');
        }

        exit();
    }
    else {
        include('template/login.php');
        exit();
    }

}
catch (Exception $e) {
    $log->error($e);
    include ('template/errorpage/index.html');
    exit();
}