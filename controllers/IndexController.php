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
        case "getFriend":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            $res->result=getFriend($userIdx);
            if(count($res->result)==0){
                $res=new stdClass();
                $res->code = 320;$res->isSuccess = False;
                $res->message = "친구가 없습니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $res->isSuccess = True;
            $res->code = 100;
            $res->message = "친구 목록 로드 성공.";
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;
        case "getSchedule":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            $userIdx=getDataByJWToken($jwt,JWT_SECRET_KEY)->userIdx;
            $scheduleIdx=$_GET['scheduleIdx'];
            if(!isValidSchedule($userIdx,$scheduleIdx)){
                $res->code = 200;$res->isSuccess = False;
                $res->message = "해당 시간표는 존재하지 않습니다.";
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $res->result=getSchedule($userIdx,$scheduleIdx);
            $res->isSuccess = True;
            $res->code = 100;
            $res->message = "시간표 로드 성공";
            echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
            break;


        case "getUsers":
            http_response_code(200);

            $res->result = getUsers();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 5
         * API Name : 테스트 Path Variable API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "getUserDetail":
            http_response_code(200);

            $res->result = getUserDetail($vars["userIdx"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 6
         * API Name : 테스트 Body & Insert API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "createUser":
            http_response_code(200);

            // Packet의 Body에서 데이터를 파싱합니다.
            $userID = $req->userID;
            $pwd_hash = password_hash($req->pwd, PASSWORD_DEFAULT); // Password Hash
            $name = $req->name;

            $res->result = createUser($userID, $pwd_hash, $name);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
