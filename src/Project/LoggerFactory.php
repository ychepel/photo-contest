<?php

namespace Project;

use Monolog\ErrorHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerFactory {

    /**
     * @param String $className
     * @return Logger
     */
    public static function getLogger($className) {
        $log = new Logger($className);
        $log->pushHandler(new StreamHandler(Config::LOG_FILE, Config::LOG_LEVEL));
        ErrorHandler::register($log);
        return $log;
    }
}