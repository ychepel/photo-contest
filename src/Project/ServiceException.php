<?php

namespace Project;

use Exception;

class ServiceException extends Exception {
    function __construct($msg, $e=null) {
        parent::__construct($msg."'<br>".$e);
    }
}