<?php

namespace Project;

class Image {
    var $id;
    var $filePath;
    var $thumbnailPath;
    var $approved;
    var $approvingDateTime;
    var $fileName;

    public function getStatus() {
        if(is_null($this->approvingDateTime)) {
            return 'waiting';
        }
        return $this->approved ? 'approved' : 'unapproved';
    }

    public function getWatermarkColor() {
        $values = [
            'waiting' => 'orange',
            'approved' => 'green',
            'unapproved' => 'red'
        ];
        return $values[$this->getStatus()];
    }

    public function getWatermarkCssClass() {
        $values = [
            'waiting' => 'markitem-clock fas fa-hourglass-half fa-2x',
            'approved' => 'markitem-smile fas fa-smile fa-2x',
            'unapproved' => 'markitem-smile fas fa-meh fa-2x'
        ];
        return $values[$this->getStatus()];
    }

    public function isDeleteAllowed() {
        if(is_null($this->approvingDateTime)) {
            return true;
        }
        if(!$this->approved) {
            return true;
        }
        return false;
    }
}