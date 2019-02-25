<?php

ini_set('display_errors', 'Off');

use Project\ImageService;
use Project\FileService;

require_once("vendor/autoload.php");

$imageService = new ImageService();
$fileService = new FileService();

$photos = $imageService->getApprovedPhotos();

include('template/gallery.php');
