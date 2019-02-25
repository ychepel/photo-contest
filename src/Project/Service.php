<?php

namespace Project;

abstract class Service {

    /**
     * @var \Monolog\Logger
     */
    protected $log;
    protected $dbConnection;

    public function __construct()
    {
        $this->log = LoggerFactory::getLogger(get_class($this));
        $this->dbConnection = DbConnection::getConnection();
    }
}