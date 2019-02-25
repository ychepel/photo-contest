<?php

namespace Project;

use InvalidArgumentException;
use RuntimeException;

class FileService extends Service {

    public function saveFile($consultantNumber, $base64) {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
            $data = substr($base64, strpos($base64, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif

            if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
                throw new InvalidArgumentException('Invalid image type = '.$type);
            }
            $data = base64_decode($data);
            if ($data === false) {
                throw new RuntimeException('Base64_decode failed');
            }
        } else {
            throw new InvalidArgumentException('Did not match data URI with image data. ConsultantNumber='.$consultantNumber.'; Base64Image='.$base64);
        }
        $filePath = Config::PENDING_IMAGE_FOLDER . '/' . $this->generateFileName($consultantNumber) . '.' . $type;
        file_put_contents($filePath, $data);

        return $filePath;
    }

    private function generateFileName($consultantNumber) {
        return $consultantNumber.'_'.date('YmdHis').substr((string)microtime(),2,6);
    }

    public function generatePath($currentPath, $targetFolder) {
        $fileName = substr($currentPath, strrpos($currentPath, '/') + 1);
        return $targetFolder.'/'.$fileName;
    }

    public function moveFile($currentPath, $targetFolder) {
        $newPath = $this->generatePath($currentPath, $targetFolder);
        if(rename($currentPath, $newPath)) {
            return $newPath;
        }
        throw new ServiceException('Cannot move file. OldPath=`'.$currentPath.'`. NewPath=`'.$newPath.'`.');
    }

    public function copyFile($currentPath, $destinationFolder) {
        $destinationPath = $this->generatePath($currentPath, $destinationFolder);
        if(copy($currentPath, $destinationPath)) {
            return $destinationPath;
        }
        throw new ServiceException('Cannot copy file. OriginalPath=`'.$currentPath.'`. DestinationPath=`'.$destinationPath.'`.');
    }

    public function makeThumbnail($currentPath) {
        $fileName = substr($currentPath, strrpos($currentPath, '/') + 1, strrpos($currentPath, '.') - strrpos($currentPath, '/') - 1);
        $thumbnailPath = Config::THUMBNAIL_FOLDER.'/'.$fileName.'.jpg';

        $imageSizes = getimagesize($currentPath);

        $width = $imageSizes[0];
        $height = $imageSizes[1];

        $desiredWidth = 800;
        $desiredHeight = floor($height * ($desiredWidth / $width));

        $virtualImage = $this->getVirtuallImage($desiredWidth, $desiredHeight);
        if(!$sourceImage = $this->getSourceImage($currentPath)) {
            return false;
        }

        imagecopyresampled($virtualImage, $sourceImage, 0, 0, 0, 0, $desiredWidth, $desiredHeight, $width, $height);
        imagedestroy($sourceImage);

        imagejpeg($virtualImage, $thumbnailPath);
        imagedestroy($virtualImage);

        return $thumbnailPath;
    }

    private function getSourceImage($file) {
        $memoryLimit = ini_get('memory_limit');
        ini_set('memory_limit','400M');
        $size = getimagesize($file);
        switch($size["mime"]){
            case "image/jpeg":
                $img = imagecreatefromjpeg($file);
                break;
            case "image/gif":
                $img = imagecreatefromgif($file);
                break;
            case "image/png":
                $img = imagecreatefrompng($file);
                break;
            default:
                $img=false;
                break;
        }
        ini_set('memory_limit', $memoryLimit);
        return $img;
    }

    private function getVirtuallImage($width, $height) {
        $memoryLimit = ini_get('memory_limit');
        ini_set('memory_limit','400M');
        $virtualImage = imagecreatetruecolor($width, $height);
        ini_set('memory_limit', $memoryLimit);
        return $virtualImage;
    }

    public function alignImage($filePath) {
        $exif = exif_read_data($filePath,0,true);
        if(!isset($exif['IFD0'])) {
            return false;
        }
        if(!isset($exif['IFD0']['Orientation'])) {
            return false;
        }

        $orientation = $exif['IFD0']['Orientation'];

        if($orientation == 7 || $orientation == 8) {
            $degrees = 90;
        } elseif($orientation == 5 || $orientation == 6) {
            $degrees = 270;
        } elseif($orientation == 3 || $orientation == 4) {
            $degrees = 180;
        } else {
            $degrees = 0;
        }

        if($degrees) {
            $newPath = $this->moveFile($filePath, Config::NOT_ROTATED_IMAGE_FOLDER);

            $memoryLimit = ini_get('memory_limit');
            ini_set('memory_limit','400M');
            $rotate = imagerotate(imagecreatefromjpeg($newPath), $degrees, 0);
            ini_set('memory_limit', $memoryLimit);
            imagejpeg($rotate, $filePath);
            imagedestroy($rotate);

            return true;
        }

        return false;
    }

}