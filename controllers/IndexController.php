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
        case "deleteOrder":
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
            $payIdx=$_GET['orderIdx'];
            $reason=$_GET['reason'];
            $bool=isDeleted($userIdx,$payIdx);

            if(!$bool[0]){

                $res->message = $bool[1];
                $res->code = $bool[2];
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            if($reason==null){
                $res->message = "취소사유는 필수항목입니다";
                $res->code = 449;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $result = deleteOrder($userIdx,$payIdx,$reason);
            $res->message = $result[1];
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;
        case "getDestination":
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
            $result=getDestination($userIdx);
            $res->result=$result[2];
            $res->message = $result[1];
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;
        case "deleteDestination":
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
            $destinationIdx=$_GET['destinationIdx'];
            $bool = isMyDestination($userIdx,$destinationIdx);
            if(!$bool){
                $res->message = "본인의 배송지가 아닙니다.";
                $res->code = 446;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $result=deleteDestination($userIdx,$destinationIdx);
            if(!$result[0]){
                $res->message = $result[2];
                $res->code = $result[1];
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $res->message = $result[1];
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;

        case "getSearch":
            $keyword=$_GET['keyword'];
            $filter=$_GET['filter'];
            $result = getSearch($keyword,$filter);
            if($result[0]==false){
                $res->message =$result[2];
                $res->code = $result[1];
                $res->isSuccess = True;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $res->result=$result[3];
            $res->message =$result[2];
            $res->code = $result[1];
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;

        case "getHistoryDetail":
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
            $payIdx=$vars['orderIdx'];
            $bool = isMyHistory($userIdx,$payIdx);
            if(!$bool){
                $res->message = "본인의 주문내역이 아닙니다.";
                $res->code = 445;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $result=getHistoryDetail($payIdx);
            $res->result=$result[2];
            $res->message = $result[1];
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;
        case "addDestinationAtUserInfo":
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
            $address=$req->address;
            $addressDetail=$req->addressDetail;
            $postNum=$req->postNum;
            $isMorning=$req->isMorning;
            $isMain=$req->isMain;
            if($isMain==null)
                $isMain='N';
            $result=addOnlyAddress($userIdx,$address,$addressDetail,$postNum,$isMorning,$isMain);
            $res->message = $result[1];
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;
        case "changeDestinationAtOrder":
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
            $destinationIdx=$req->destinationIdx;
            $address=$req->address;
            $postNum=$req->postNum;
            $addressDetail=$req->addressDetail;
            $isMorning=$req->isMorning;
            $isMain=$req->isMain;
            if($isMain==null)
                $isMain='N';
            $receiverName=$req->receiverName;
            if($receiverName==null){
                $res->message = "받으실 분 성함을 입력해주세요.";
                $res->code = 431;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $receiverPhone=$req->receiverPhone;

            if($receiverPhone==null){
                $res->message = "받으실 분 전화번호를 입력해주세요.";
                $res->code = 432;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            if(!preg_match("/^01[0-9]{8,9}$/", $receiverPhone))
            {
                $res->message = "전화번호를 양식에 맞게 입력해주세요.";
                $res->code = 413;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            if($isMorning=="Y"){
                $receivePlace=$req->receivePlace;
                if($receivePlace==null){
                    $res->message = "받을장소를 선택해주세요.";
                    $res->code = 433;
                    $res->isSuccess = False;
                    echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                    break;
                }
                $howToEnter=$req->howToEnter;
                if($howToEnter==null){
                    $res->message = "공동현관 출입방법을 선택해주세요.";
                    $res->code = 434;
                    $res->isSuccess = False;
                    echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                    break;
                }

                $entrancePwd=$req->entrancePwd;
                if($howToEnter=='공동현관 비밀번호'){
                    if($entrancePwd==null){
                        $res->message = "공동현관 비밀번호를 입력해주세요.";
                        $res->code = 436;
                        $res->isSuccess = False;
                        echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                        break;
                    }
                }
                $comment=$req->comment;
                $timeToMsg=$req->timeToMsg;
                if($timeToMsg==null){
                    $res->message = "배송완료 메시지 수신 시간을 선택해주세요.";
                    $res->code = 435;
                    $res->isSuccess = False;
                    echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                    break;
                }
                $result=changeMorning($userIdx,$destinationIdx,$address,$addressDetail,$postNum,$receiverName,$receiverPhone,$receivePlace,$howToEnter,$entrancePwd,$comment,$timeToMsg,$isMorning,$isMain);
                $res->message = $result[1];
                $res->code = 200;
                $res->isSuccess = True;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $request=$req->request;
            $result=changePost($userIdx,$destinationIdx,$address,$addressDetail,$postNum,$receiverName,$receiverPhone,$request,$isMorning,$isMain);
            $res->message = $result[1];
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;
        case "changeDestinationAtUserInfo":
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
            $destinationIdx=$req->destinationIdx;
            $addressDetail=$req->addressDetail;
            $isMain=$req->isMain;
            $receiverName=$req->receiverName;
            $receiverPhone=$req->receiverPhone;
            if($isMain==null)
                $isMain='N';
            $result=changeOnlyAddress($destinationIdx,$userIdx,$addressDetail,$isMain,$receiverName,$receiverPhone);
            $res->message = $result[1];
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;

        case "addDestinationAtOrder":
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
            $address=$req->address;
            $addressDetail=$req->addressDetail;
            $postNum=$req->postNum;
            $isMorning=$req->isMorning;
            $isMain=$req->isMain;
            if($isMain==null)
                $isMain='N';
            $receiverName=$req->receiverName;
            if($receiverName==null){
                $res->message = "받으실 분 성함을 입력해주세요.";
                $res->code = 431;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $receiverPhone=$req->receiverPhone;

            if($receiverPhone==null){
                $res->message = "받으실 분 전화번호를 입력해주세요.";
                $res->code = 432;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            if(!preg_match("/^01[0-9]{8,9}$/", $receiverPhone))
            {
                $res->message = "전화번호를 양식에 맞게 입력해주세요.";
                $res->code = 413;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            if($isMorning=="Y"){
                $receivePlace=$req->receivePlace;
                if($receivePlace==null){
                    $res->message = "받을장소를 선택해주세요.";
                    $res->code = 433;
                    $res->isSuccess = False;
                    echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                    break;
                }
                $howToEnter=$req->howToEnter;
                if($howToEnter==null){
                    $res->message = "공동현관 출입방법을 선택해주세요.";
                    $res->code = 434;
                    $res->isSuccess = False;
                    echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                    break;
                }

                $entrancePwd=$req->entrancePwd;
                if($howToEnter=='공동현관 비밀번호'){
                    if($entrancePwd==null){
                        $res->message = "공동현관 비밀번호를 입력해주세요.";
                        $res->code = 436;
                        $res->isSuccess = False;
                        echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                        break;
                    }
                }
                $comment=$req->comment;
                $timeToMsg=$req->timeToMsg;
                if($timeToMsg==null){
                    $res->message = "배송완료 메시지 수신 시간을 선택해주세요.";
                    $res->code = 435;
                    $res->isSuccess = False;
                    echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                    break;
                }
                $result=addMorning($userIdx,$address,$postNum,$addressDetail,$receiverName,$receiverPhone,$receivePlace,$howToEnter,$entrancePwd,$comment,$timeToMsg,$isMorning,$isMain);
                $res->message = $result[1];
                $res->code = 200;
                $res->isSuccess = True;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $request=$req->request;
            $result=addPost($userIdx,$address,$addressDetail,$postNum,$receiverName,$receiverPhone,$request,$isMorning,$isMain);
            $res->message = $result[1];
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;

        case "isMorningDestination":
            http_response_code(200);
            if($_GET['address']==null){
                $res->message = "주소를 입력해주세요.";
                $res->code = 430;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $address=$_GET['address'];
            $address = mb_substr($address,0,2);
            if($address=='경기'||$address=='서울'||$address=='인천'){
                $res->isMorning='Y';
                $res->message = "샛별배송 지역입니다.";
                $res->code = 204;
                $res->isSuccess = True;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $res->isMorning='N';
            $res->message = "택배배송 지역입니다.";
            $res->code = 205;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;
        case "getHistory":
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
            $result = getHistory($userIdx);
            $res->result=$result[2];
            $res->message = $result[1];
            $res->code = 200;
            $res->isSuccess = True;
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;

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
            $destinationIdx=$req->destinationIdx;
            $usedCouponIdx=$req->usedCouponIdx;
            $usedPoint = $req->usedPoint;
            $savedPoint=$req->savedPoint;
            $originalPrice = $req->originalPrice;
            $totalClientPrice=$req->totalClientPrice;
            $clientPrice = $req->payPrice;
            $wayToPay=$req->wayToPay;
            $orderList=$req->orderList;
            $rand = strtoupper(substr(uniqid(time()),0,4));
            $orderNum = strval(date("YmdHis"). $rand) ;
            $result=addPay($orderNum,$userIdx,$destinationIdx,$usedCouponIdx,$usedPoint,$savedPoint,$originalPrice,$totalClientPrice,$clientPrice,$wayToPay,$orderList);
            $res->message = $result[1];
            $res->code = $result[2];
            $res->isSuccess = $result[0];
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
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
            $res->basketCount=$result[3];
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
            $option=$_GET['option'];
            $result=deleteBasket($userIdx,$option);
            if ($result[0] == false) {
                $res->message = $result[1];
                $res->code = $result[2];
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $res->basketCount=$result[3];
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
            $res->basketCount=$result[3];
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
