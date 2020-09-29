<?php

//DB 정보
function pdoSqlConnect()
{
    try {
        $DB_HOST = "127.0.0.1";
        $DB_NAME = "SgDB";
        $DB_USER = "wily";
        $DB_PW = "sg@0829";
        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PW);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}