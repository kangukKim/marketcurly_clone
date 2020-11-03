<?php

//READ
function getUsers()
{
    $pdo = pdoSqlConnect();
    $query = "select * from Users;";

    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


//READ
function isValidUserIdx($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from Users where userIdx = ?) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
//validation
function isValidNewUser($userId, $password, $name, $email, $phoneNumber,$address,$gender, $recommenderId, $event)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from User where userId = ?) exist;";
    $st = $pdo->prepare($query);
    $st->execute([$userId]);
    //    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    if($userId==null){
        $st = null;
        $pdo = null;
        return array(false, "아이디를 입력해주세요.");
        exit;
    }
    if($res[0]['exist']==1){
        $st = null;
        $pdo = null;
        return array(false, "이미 존재하는 ID 입니다.");
        exit;
    }
    $pattern1 = '/^[0-9A-Za-z]{6,20}$/u';
    $pattern2='/[a-zA-Z]/u';
    if(!preg_match($pattern1 ,$userId)){
        $st = null;
        $pdo = null;
        return array(false, "ID는 6자리 이상 영문자 또는 영문자와 숫자 조합만 가능합니다.");
        exit;
    }
    if(!preg_match($pattern2 ,$userId)){
        $st = null;
        $pdo = null;
        return array(false, "ID는 영문자를 반드시 포함해야합니다.");
        exit;
    }
    if($password==null){
        $st = null;
        $pdo = null;
        return array(false, "비밀번호를 입력해주세요.");
        exit;
    }
    $pattern3='/^[0-9A-Za-z!@#$%^&*]{10,}$/';
    $pattern4='/(\d)\\1\\1/';
    $num = preg_match('/[0-9]/u', $password);
    $eng = preg_match('/[a-z]/u', $password);
    $spe = preg_match("/[\!\@\#\$\%\^\&\*]/u",$password);
    if(!preg_match($pattern3,$password)){
        $st = null;
        $pdo = null;
        return array(false, "영문자 또는 숫자 또는 특수문자 조합으로 10자 이상 입력하세요.");
        exit;
    }
    if(preg_match($pattern4,$password)){
        $st = null;
        $pdo = null;
        return array(false, "동일한 숫자는 3개이상 쓰지마세요.");
        exit;
    }
    if(($num==0&&$eng==0)||($num==0&&$spe==0)||($eng==0&&$spe==0)){
        $st = null;
        $pdo = null;
        return array(false, "영문자 또는 숫자 또는 특수문자 중 적어도 2가지 조합은 사용해야합니다.");
        exit;
    }
    if($name==null){
        $st = null;
        $pdo = null;
        return array(false, "이름을 입력해주세요.");
        exit;
    }
    if($email==null){
        $st = null;
        $pdo = null;
        return array(false, "이메일을 입력해주세요.");
        exit;
    }
    if(!filter_Var($email, FILTER_VALIDATE_EMAIL)){
        $st = null;
        $pdo = null;
        return array(false, "이메일 주소가 옳지 않습니다");
        exit;
    }
    if($phoneNumber==null){
        $st = null;
        $pdo = null;
        return array(false, "휴대폰 번호를 입력해주세요.");
        exit;
    }
    if(!preg_match("/^01[0-9]{8,9}$/", $phoneNumber))
    {
        $st = null;
        $pdo = null;
        return array(false, "휴대폰 번호가 옳지 않습니다");
        exit;
    }
    if($address==null){
        $st = null;
        $pdo = null;
        return array(false, "주소를 입력해주세요.");
        exit;
    }
    if($gender==null){
        $st = null;
        $pdo = null;
        return array(false, "성별을 입력해주세요.");
        exit;
    }
    if($recommenderId!=null&&$event!=null){
        $st = null;
        $pdo = null;
        return array(false, "추천인과 이벤트 둘 중 하나만 입력가능합니다.");
        exit;
    }
    return array(True,"유효한 유저입니다.");

}




//POST

function createUser($userId,$password,$name,$email, $phoneNumber,$address,$birthday,$gender,$recommenderId,$event,$acceptPrivacy,$isSMS,$isEmail)
{
        $pdo = pdoSqlConnect();
        $query = "select ifnull(max(userIdx)+1,1) as userIdx from User;";
        $st = $pdo->prepare($query);
        $st->execute();
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $userIdx = $st->fetchAll()[0]['userIdx'];
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO User (userIdx,userId,password,name,email, phoneNumber,address,birthday,gender,recommenderId,event,acceptPrivacy,isSMS,isEmail) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
        $st = $pdo->prepare($query);
        $st->execute([$userIdx,$userId,$password,$name,$email, $phoneNumber,$address,$birthday,$gender,$recommenderId,$event,$acceptPrivacy,$isSMS,$isEmail]);
        $st = null;
        $pdo = null;
        return $userIdx;
}



// CREATE
//    function addMaintenance($message){
//        $pdo = pdoSqlConnect();
//        $query = "INSERT INTO MAINTENANCE (MESSAGE) VALUES (?);";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message]);
//
//        $st = null;
//        $pdo = null;
//
//    }


// UPDATE
//    function updateMaintenanceStatus($message, $status, $no){
//        $pdo = pdoSqlConnect();
//        $query = "UPDATE MAINTENANCE
//                        SET MESSAGE = ?,
//                            STATUS  = ?
//                        WHERE NO = ?";
//
//        $st = $pdo->prepare($query);
//        $st->execute([$message, $status, $no]);
//        $st = null;
//        $pdo = null;
//    }

// RETURN BOOLEAN
//    function isRedundantEmail($email){
//        $pdo = pdoSqlConnect();
//        $query = "SELECT EXISTS(SELECT * FROM USER_TB WHERE EMAIL= ?) AS exist;";
//
//
//        $st = $pdo->prepare($query);
//        //    $st->execute([$param,$param]);
//        $st->execute([$email]);
//        $st->setFetchMode(PDO::FETCH_ASSOC);
//        $res = $st->fetchAll();
//
//        $st=null;$pdo = null;
//
//        return intval($res[0]["exist"]);
//
//    }
