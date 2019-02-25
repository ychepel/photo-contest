<?php

namespace Project;

use MeekroDB;

class DbConnection {

    public static function getConnection() {
        $mdb = new MeekroDB(
            Config::DB_HOST,
            Config::DB_USER,
            Config::DB_PASSWORD,
            Config::DB_NAME,
            Config::DB_PORT,
            Config::DB_ENCODING
        );
        $mdb->error_handler = '';
        $mdb->throw_exception_on_error = true;
        return $mdb;
    }
}