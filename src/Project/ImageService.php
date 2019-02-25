<?php

namespace Project;

use DateTime;
use Exception;
use MeekroDBException;
use RuntimeException;

class ImageService extends Service {

    public function isHashExistsInUserProfile($imageHash, $consultantNumber) {
        try {
            if (sizeof($this->dbConnection->query("SELECT * FROM ".Config::FILE_TABLE." WHERE ImageHash=%s AND DeletingDateTime IS NULL AND ConsultantNumber=%s", $imageHash, $consultantNumber))) {
                return true;
            } else {
                return false;
            }
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get consultant image hash', ['exception' => $e]);
        }
        return false;
    }

    public function isHashExists($imageHash) {
        try {
            if (sizeof($this->dbConnection->query("SELECT * FROM ".Config::FILE_TABLE." WHERE ImageHash=%s AND DeletingDateTime IS NULL", $imageHash))) {
                return true;
            } else {
                return false;
            }
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get image hash', ['exception' => $e]);
        }
        return false;
    }

    public function upload($consultantNumber, $fileName, $imageBase64) {
        $fileService = new FileService();
        $filePath = $fileService->saveFile($consultantNumber, $imageBase64);
        if(!file_exists($filePath)) {
            throw new ServiceException('File was not saved: ' . json_encode([
                    'consultantNumber' => $consultantNumber,
                    'fileName' => $fileName,
                    'filePath' => $filePath
                ]));
        }

        $fileService->alignImage($filePath);
        if(!file_exists($filePath)) {
            $this->log->warning('File was not rotated.', [
                    'consultantNumber' => $consultantNumber,
                    'fileName' => $fileName,
                    'filePath' => $filePath
                ]);
            $currentFilePath = $fileService->generatePath($filePath, Config::NOT_ROTATED_IMAGE_FOLDER);
            $filePath = $fileService->moveFile($currentFilePath, Config::PENDING_IMAGE_FOLDER);
        }

        $thumbnailPath = $fileService->makeThumbnail($filePath);
        if(!file_exists($thumbnailPath)) {
            $this->log->error('Thumbnail was not created.', [
                    'consultantNumber' => $consultantNumber,
                    'fileName' => $fileName,
                    'filePath' => $filePath
                ]);
            $thumbnailPath = $fileService->copyFile($filePath, Config::THUMBNAIL_FOLDER);
        }
        $originalDate = $this->getOriginalFileDate($filePath);

        return $this->insertFileData($consultantNumber, $fileName, md5($imageBase64), $filePath, $thumbnailPath, $originalDate);
    }

    private function getOriginalFileDate($filePath) {
        if(isset(exif_read_data($filePath)['DateTimeOriginal'])) {
            $dateFormat = 'Y-m-d H:i:s';
            $dateValue = $this->strReplaceFirst(':', '-', exif_read_data($filePath)['DateTimeOriginal'], 2);
            $d = DateTime::createFromFormat($dateFormat, $dateValue);
            if ($d && $d->format($dateFormat) === $dateValue) {
                return $dateValue;
            }
        }
        return null;
    }

    private function insertFileData($consultantNumber, $fileName, $imageHash, $filePath, $thumbnailPath, $originalDate){
        try {
            $this->dbConnection->insert(Config::FILE_TABLE, [
                'ConsultantNumber' => $consultantNumber,
                'FilePath' => $filePath,
                'ThumbnailPath' => $thumbnailPath,
                'UploadingDateTime' => date('Y-m-d H:i:s'),
                'DeletingDateTime' => null,
                'OriginalFileName' => $fileName,
                'OriginalFileDateTime' => $originalDate,
                'ImageHash' => $imageHash,
                'PhotoApproved' => false,
                'ApprovingDateTime' => null
            ]);
            return $this->dbConnection->insertId();
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot upload image', ['exception' => $e]);
            throw new RuntimeException('Cannot upload image', $e);
        }
    }

    public function getConsultantImages($consultantNumber) {
        $images = array();
        try {
            if ($results = $this->dbConnection->query('SELECT Id, FilePath, ThumbnailPath, PhotoApproved, ApprovingDateTime, OriginalFileName, OriginalFileDateTime
              FROM ' . Config::FILE_TABLE . ' WHERE ConsultantNumber=%s AND DeletingDateTime IS NULL
              ORDER BY UploadingDateTime DESC', $consultantNumber)) {
                foreach ($results as $row) {
                    $image = new Image();
                    $image->id = $row['Id'];
                    $image->filePath = $row['FilePath'];
                    $image->thumbnailPath = $row['ThumbnailPath'];
                    $image->fileName = $row['OriginalFileName'];
                    $image->approved = $row['PhotoApproved'];
                    $image->approvingDateTime = $row['ApprovingDateTime'];
                    $images[] = $image;
                }
            }
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get image data', ['exception' => $e]);
        }
        return $images;
    }

    /**
     * @param $consultantNumber
     * @param $imageId
     * @return boolean
     */
    public function delete($consultantNumber, $imageId) {
        $fileService = new FileService();
        try {
            if ($result = $this->dbConnection->queryFirstRow('SELECT Id, FilePath
              FROM ' . Config::FILE_TABLE . '
              WHERE ConsultantNumber=%s AND Id=%i
              AND DeletingDateTime IS NULL
              AND (ApprovingDateTime IS NULL OR (ApprovingDateTime IS NOT NULL AND PhotoApproved=0))', $consultantNumber, $imageId)) {
                $newPath = $fileService->moveFile($result['FilePath'], Config::DELETED_IMAGE_FOLDER);
                return $this->update(['FilePath' => $newPath, 'DeletingDateTime' => date('Y-m-d H:i:s')], 'Id=%i', $imageId);
            } else {
                $this->log->error('Cannot retrieve image data id = '.$imageId.' of consultant with number=' . $consultantNumber);
            }
        }
        catch (Exception $e) {
            $this->log->error('Cannot mark image as deleted', ['exception' => $e]);
        }
        return false;
    }

    private function update($newValues, $whereString, $whereValue) {
        return $this->dbConnection->update(Config::FILE_TABLE, $newValues, $whereString, $whereValue);
    }

    public function getThumbnailPathById($imageId) {
        try {
            if ($path = $this->dbConnection->queryFirstField("SELECT ThumbnailPath FROM ".Config::FILE_TABLE." WHERE Id=%i", $imageId)) {
                return $path;
            }
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get thumbnail path', ['exception' => $e]);
        }
        return false;
    }

    /**
     * @return array WaitingImage
     */
    public function getWaitingPhotos() {
        $images = array();
        try {
            if ($results = $this->dbConnection->query('SELECT f.Id, f.ConsultantNumber, f.FilePath, f.ThumbnailPath, f.OriginalFileName, f.OriginalFileDateTime, c.ConsultantName
              FROM '.Config::FILE_TABLE.' f
              INNER JOIN '.Config::CONSULTANT_TABLE.' c ON f.ConsultantNumber=c.ConsultantNumber
              WHERE f.DeletingDateTime IS NULL AND ApprovingDateTime IS NULL
              ORDER BY f.ConsultantNumber, f.UploadingDateTime')) {
                foreach ($results as $row) {
                    $images[] = $this->parseViewImage($row);
                }
            }
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get image data', ['exception' => $e]);
        }
        return $images;
    }

    public function approve($imageId, $approved) {
        $fileService = new FileService();
        if($this->update(['PhotoApproved' => ($approved == 'true'), 'ApprovingDateTime' => date('Y-m-d H:i:s')], 'Id=%i', $imageId)) {
            $currentPath = $this->getFailPathPathById($imageId);
            $targetFolder = $approved == 'true' ? Config::APPROVED_IMAGE_FOLDER : Config::UNAPPROVED_IMAGE_FOLDER;
            $newPath = $fileService->moveFile($currentPath, $targetFolder);
            return $this->update(['FilePath' => $newPath], 'Id=%i', $imageId);
        }
        return false;
    }

    private function getFailPathPathById($fileId) {
        try {
            if ($path = $this->dbConnection->queryFirstField("SELECT FilePath FROM ".Config::FILE_TABLE." WHERE Id=%i", $fileId)) {
                return $path;
            }
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get file path', ['exception' => $e]);
        }
        return false;
    }

    /**
     * @return array WaitingImage
     */
    public function getApprovedPhotos() {
        $images = array();
        try {
            if ($results = $this->dbConnection->query('SELECT f.Id, f.ConsultantNumber, f.FilePath, f.ThumbnailPath, f.OriginalFileName, f.OriginalFileDateTime, c.ConsultantName
              FROM '.Config::FILE_TABLE.' f
              INNER JOIN '.Config::CONSULTANT_TABLE.' c ON f.ConsultantNumber=c.ConsultantNumber
              WHERE f.DeletingDateTime IS NULL AND PhotoApproved=1
              ORDER BY f.UploadingDateTime DESC')) {
                foreach ($results as $row) {
                    $images[] = $this->parseViewImage($row);
                }
            }
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get image data', ['exception' => $e]);
        }
        return $images;
    }

    /**
     * @return array ConsultantImage
     */
    public function getConsultantApprovedPhotos($consultantNumber) {
        $images = array();
        try {
            if ($results = $this->dbConnection->query('SELECT Id, FilePath, ThumbnailPath, OriginalFileName, OriginalFileDateTime, UploadingDateTime
              FROM '.Config::FILE_TABLE.'
              WHERE DeletingDateTime IS NULL AND PhotoApproved=1 AND ConsultantNumber=%s
              ORDER BY UploadingDateTime', $consultantNumber)) {
                foreach ($results as $row) {
                    $images[] = $this->parseConsultantImage($row);
                }
            }
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get consultant approved images', ['exception' => $e]);
        }
        return $images;
    }

    /**
     * @return array ConsultantImage
     */
    public function getConsultantPendingPhotos($consultantNumber) {
        $images = array();
        try {
            if ($results = $this->dbConnection->query('SELECT Id, FilePath, ThumbnailPath, OriginalFileName, OriginalFileDateTime, UploadingDateTime
              FROM '.Config::FILE_TABLE.'
              WHERE DeletingDateTime IS NULL AND ApprovingDateTime IS NULL AND ConsultantNumber=%s
              ORDER BY UploadingDateTime', $consultantNumber)) {
                foreach ($results as $row) {
                    $images[] = $this->parseConsultantImage($row);
                }
            }
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get consultant pending images', ['exception' => $e]);
        }
        return $images;
    }

    /**
     * @return array ConsultantImage
     */
    public function getConsultantUnapprovedPhotos($consultantNumber) {
        $images = array();
        try {
            if ($results = $this->dbConnection->query('SELECT Id, FilePath, ThumbnailPath, OriginalFileName, OriginalFileDateTime, UploadingDateTime
              FROM '.Config::FILE_TABLE.'
              WHERE ApprovingDateTime IS NOT NULL AND PhotoApproved=0 AND ConsultantNumber=%s
              ORDER BY UploadingDateTime', $consultantNumber)) {
                foreach ($results as $row) {
                    $images[] = $this->parseConsultantImage($row);
                }
            }
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get consultant unapproved images', ['exception' => $e]);
        }
        return $images;
    }

    /**
     * @return array ConsultantImage
     */
    public function getConsultantDeletedPhotos($consultantNumber) {
        $images = array();
        try {
            if ($results = $this->dbConnection->query('SELECT Id, FilePath, ThumbnailPath, OriginalFileName, OriginalFileDateTime, UploadingDateTime
              FROM '.Config::FILE_TABLE.'
              WHERE DeletingDateTime IS NOT NULL AND ApprovingDateTime IS NULL AND ConsultantNumber=%s
              ORDER BY UploadingDateTime', $consultantNumber)) {
                foreach ($results as $row) {
                    $images[] = $this->parseConsultantImage($row);
                }
            }
        }
        catch (MeekroDBException $e) {
            $this->log->error('Cannot get consultant deleted images', ['exception' => $e]);
        }
        return $images;
    }

    private function parseConsultantImage($data) {
        $image = new ConsultantImage();
        $image->id = $data['Id'];
        $image->filePath = $data['FilePath'];
        $image->thumbnailPath = $data['ThumbnailPath'];
        $image->fileName = $data['OriginalFileName'];
        $image->uploadingDateTime = $data['UploadingDateTime'];
        $image->originalDate = is_null($data['OriginalFileDateTime']) ? 'unknown' : $data['OriginalFileDateTime'];
        return $image;
    }

    private function parseViewImage($data) {
        $image = new ViewImage();
        $image->id = $data['Id'];
        $image->filePath = $data['FilePath'];
        $image->thumbnailPath = $data['ThumbnailPath'];
        $image->fileName = $data['OriginalFileName'];
        $image->consultantNumber = $data['ConsultantNumber'];
        $image->consultantName = $data['ConsultantName'];
        $image->originalDate = is_null($data['OriginalFileDateTime']) ? 'unknown' : $data['OriginalFileDateTime'];
        return $image;
    }

    private function strReplaceFirst($from, $to, $content, $number) {
        $from = '/'.preg_quote($from, '/').'/';
        return preg_replace($from, $to, $content, $number);
    }
}