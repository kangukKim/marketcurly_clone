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
            $result = isValidNewUser($userId, $password, $name, $email, $phoneNumber,$address,$gender, $recommenderId, $event);
            if ($result[0] == false) {
                $res->message = $result[1];
                $res->code = 400;
                $res->isSuccess = False;
                echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
                break;
            }
            $userIdx=createUser($userId, $pwd_hash, $name, $email, $phoneNumber,$address,$birthday, $gender, $recommenderId, $event, $acceptPrivacy, $isSMS, $isEmail);
            $res->result->jwt = getJWT($userIdx, JWT_SECRET_KEY);
            $res->isSuccess = TRUE;
            $res->code = 201;
            $res->message = "리소스 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
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
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
