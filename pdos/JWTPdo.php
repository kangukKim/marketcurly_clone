<?php

function isValidUser($ID, $pwd){
    $pdo = pdoSqlConnect();
    $query = "SELECT ID, pwd as hash FROM Users WHERE ID= ?;";


    $st = $pdo->prepare($query);
    $st->execute([$ID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;

    return password_verify($pwd, $res[0]['hash']);

}
function getUserIdxById($id)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT userIdx FROM User WHERE id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['userIdx'];
}