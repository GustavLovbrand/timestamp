<?php
class Model {
    protected static $db;

    public function __construct() {
        if (!self::$db) {
            $config = require __DIR__ . '/../config/config.php';
            self::$db = new PDO(
                "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8",
                $config['db']['user'],
                $config['db']['pass']
            );
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
    }
}
