<?php

//DB 정보
function pdoSqlConnect()
{
    try {
        $DB_HOST = "db-server-b.caiccybkhvmh.ap-northeast-2.rds.amazonaws.com";
        $DB_NAME = "movie_paradise";
        $DB_USER = "joinsung";
        $DB_PW = "201521116";
        $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME", $DB_USER, $DB_PW);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}