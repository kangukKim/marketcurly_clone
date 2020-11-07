<?php

//DB 정보
function pdoSqlConnect()
{
    try {
        $DB_HOST = "database-2.caaxfeb6vg5f.ap-northeast-2.rds.amazonaws.com";
        $DB_NAME = "marketcurly_tmp";
        $DB_USER = "admin";
        $DB_PW = "kimku0540)";
        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PW);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec("set names utf8");

        return $pdo;
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}