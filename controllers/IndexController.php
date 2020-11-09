<?php
require 'function.php';
const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            echo "API Server";
            break;
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;
        /*
         * API No. 4
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "addPay":
            http_response_code(200);
            if(!isset($_SERVER["HTTP_X_ACCESS_TOKEN"])){
                $res->message = "로그인 해주세요.";
                $res->code = 419;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                $userIdx=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            }
            $destination=$req->destination;
            $receivePlace=$req->receivePlace;
            $howToEnter=$req->howToEnter;
            $entrancePwd=$req->entrancePwd;
            $timeToMsg=$req->timeToMsg;
            $usedCouponIdx=$req->usedCouponIdx;
            $usedPoint = $req->usedPoint;
            $savedPoint=$req->savedPoint;
            $originalPrice = $req->originalPrice;
            $clientPrice = $req->clientPrice;
            $wayToPay=$req->wayToPay;
            $orderList=$req->orderList;
            $rand = strtoupper(substr(uniqid(time()),0,6));
            $orderNum = strval(date("YmdHis"). $rand) ;
            $result=addPay($orderNum,$userIdx,$destination,$receivePlace,$howToEnter,$entrancePwd,$timeToMsg,$usedCouponIdx,$usedPoint,$savedPoint,$originalPrice,$clientPrice,$wayToPay,$orderList);
            $res->message = $result[1];
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;

            break;
        case "getCoupon":
            http_response_code(200);
            if(!isset($_SERVER["HTTP_X_ACCESS_TOKEN"])){
                $res->message = "로그인 해주세요.";
                $res->code = 419;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                $userIdx=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            }
            $option=$req->option;
            $result=getCoupon($userIdx,$option);
            if ($result[0] == false) {
                $res->message = $result[1];
                $res->code = $result[2];
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $res->result = $result[2];
            $res->message = $result[1];
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;
        case "getPay":
            http_response_code(200);
            if(!isset($_SERVER["HTTP_X_ACCESS_TOKEN"])){
                $res->message = "로그인 해주세요.";
                $res->code = 419;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                $userIdx=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            }
            $option=$req->option;
            $result=getPay($userIdx,$option);
            if ($result[0] == false) {
                $res->message = $result[1];
                $res->code = $result[2];
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $res->result = $result[2];
            $res->message = $result[1];
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;
        case "changeBasket":
            http_response_code(200);
            if(!isset($_SERVER["HTTP_X_ACCESS_TOKEN"])){
                $res->message = "로그인 해주세요.";
                $res->code = 419;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                $userIdx=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            }
            $option=$req->option;
            $result=changeBasket($userIdx,$option);
            if ($result[0] == false) {
                $res->message = $result[1];
                $res->code = $result[2];
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $res->message = $result[1];
            $res->code = $result[2];
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;
        case "deleteBasket":
            http_response_code(200);
            if(!isset($_SERVER["HTTP_X_ACCESS_TOKEN"])){
                $res->message = "로그인 해주세요.";
                $res->code = 419;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                $userIdx=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            }
            $option=$req->option;
            $result=deleteBasket($userIdx,$option);
            if ($result[0] == false) {
                $res->message = $result[1];
                $res->code = $result[2];
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $res->message = $result[1];
            $res->code = $result[2];
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;
        case "getBasket":
            http_response_code(200);
            if(!isset($_SERVER["HTTP_X_ACCESS_TOKEN"])){
                $res->message = "로그인 해주세요.";
                $res->code = 419;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                $userIdx=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            }
            $result=getBasket($userIdx);
            $res->result=$result;
            $res->message = "장바구니조회입니다.";
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;

        case "getSelectPage":
            http_response_code(200);
            if(!isset($_SERVER["HTTP_X_ACCESS_TOKEN"])){
                $userIdx=null;
            }
            else{
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                $userIdx=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            }
            $productIdx=$vars['productIdx'];
            $result=getSelectPage($userIdx,$productIdx);
            if ($result[0] == false) {
                $res->message = $result[1];
                $res->code = $result[2];
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $res->result=$result[2];
            $res->message = $result[1];
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;

        case "getUserInfo":
            http_response_code(200);
            if(!isset($_SERVER["HTTP_X_ACCESS_TOKEN"])){
                $res->message = "로그인 해주세요.";
                $res->code = 419;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                $userIdx=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            }
            $res->result = getUserInfo($userIdx);
            $res->message = "회원정보 입니다";
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;


        case "getProductInfo":
            http_response_code(200);
            if(!isset($_SERVER["HTTP_X_ACCESS_TOKEN"])){
                $userIdx=null;
            }
            else{
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                $userIdx=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            }
            $productIdx=$vars['productIdx'];
            $result=getProductInfo($userIdx,$productIdx);
            if ($result[0] == false) {
                $res->message = $result[1];
                $res->code = $result[2];
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $res->result=$result[2];
            $res->message = $result[1];
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;

        case "addBasket":
            http_response_code(200);
            if(!isset($_SERVER["HTTP_X_ACCESS_TOKEN"])){
                $res->message = "로그인 해주세요.";
                $res->code = 419;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            else{
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                $userIdx=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            }
            $option=$req->option;
            $result = addBasket($userIdx,$option);
            if ($result[0] == false) {
                $res->message = $result[1];
                $res->code = $result[2];
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $res->message = $result[1];
            $res->code = $result[2];
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;
        case "getRecommendPage":
            http_response_code(200);
            if(!isset($_SERVER["HTTP_X_ACCESS_TOKEN"])){
                $userIdx=null;
            }
            else{
                $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
                $userIdx=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            }
            if($userIdx!=null){
                if (!isValidUserIdx($userIdx)) {
                    $res->message = "없는 유저입니다.";
                    $res->code = 418;
                    $res->isSuccess = False;
                    echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                    break;
                }
            }
            $res->result = getRecommendPage($userIdx);
            $res->message = "추천화면 입니다.";
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;

        case "getHomePage":
            http_response_code(200);
            if(!isset($_SERVER["HTTP_X_ACCESS_TOKEN"])){
                $userIdx=null;
            }
            else{
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            }
            if($userIdx!=null){
            if (!isValidUserIdx($userIdx)) {
                $res->message = "없는 유저입니다.";
                $res->code = 418;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            }
            $res->result = getHomePage($userIdx);
            $res->message = "홈화면 입니다.";
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;

        case "isValidUserId":
            http_response_code(200);

            $userId = $_GET['userId'];
            $res=new stdClass();
            $result = isValidUserId($userId);
            if ($result[0] == false) {
                $res->message = $result[1];
                $res->code = $result[2];
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $res->isSuccess = TRUE;
            $res->code = $result[2];
            $res->message = $result[1];
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "createUser":

            http_response_code(200);
            $userId = $req->userId;
            $password = $req->password;
            $pwd_hash = password_hash($req->password, PASSWORD_DEFAULT); // Password Hash
            $name = $req->name;
            $email = $req->email;
            $birthday = $req->birthday;
            $phoneNumber=$req->phoneNumber;
            $address=$req->address;
            $gender = $req->gender;
            $recommenderId = $req->recommenderId;
            $event = $req->event;
            $acceptPrivacy = $req->acceptPrivacy;
            $isSMS = $req->isSMS;
            $isEmail = $req->isEmail;
            $result = isValidNewUser($userId, $password, $name, $email, $phoneNumber,$address, $recommenderId, $event);
            if ($result[0] == false) {
                $res->message = $result[1];
                $res->code = $result[2];
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $userIdx=createUser($userId, $pwd_hash, $name, $email, $phoneNumber,$address,$birthday, $gender, $recommenderId, $event, $acceptPrivacy, $isSMS, $isEmail);
//            $res=new stdClass();
            $res->result=new stdClass();
//            $res->result->jwt=new stdClass();
            $res->result->jwt = getJWT($userIdx, JWT_SECRET_KEY);
            $res->isSuccess = TRUE;
            $res->code = 201;
            $res->message = "회원가입에 성공했습니다";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
