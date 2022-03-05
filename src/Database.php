<?php

namespace App;

use Exception;
use mysqli_driver;
use mysqli;
use mysqli_sql_exception;

/**
 * Database helper class
 *
 * Return Mysql connection objects.
 */
class Database {

    /**
     * @var mysqli
     */
    public static $mysqli = null;

    /**
     * Get reference to mysqli connection
     *
     * @return mysqli
     * @throws Exception If database connection fails
     */
    public static function get() {

        if (is_null(self::$mysqli)) {
            self::makeConnection();
        }

        return self::$mysqli;
    }

    /**
     * Get connection object based on application config
     *
     * @throws Exception If database connection fails
     */
    private static function makeConnection() {

        $config = include(__DIR__ . "/../config/app.php");
        $config = $config['database'];

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        self::$mysqli = new mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database'],
            $config['port']
        );
    }
}