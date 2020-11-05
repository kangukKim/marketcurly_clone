<?php

function isValidUser($userId, $password){
    if($userId==null){
        return array(false, "아이디를 입력해주세요.",401);
        exit;
    }
    if($password==null){
        return array(false, "비밀번호를 입력해주세요",405);
        exit;
    }
    $pdo = pdoSqlConnect();
    $query = "SELECT userId, password as hash FROM User WHERE userId= ?;";


    $st = $pdo->prepare($query);
    $st->execute([$userId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    if(!password_verify($password, $res[0]['hash'])){
        return array(false,"유효하지 않은 아이디입니다",416);
    }
    else{
        return array(true);
    }

}
function getUserIdxById($userId)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT userIdx FROM User WHERE userId = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$userId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['userIdx'];
}