<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        /*
         * API No. 1
         * API Name : JWT 생성 테스트 API (로그인)
         * 마지막 수정 날짜 : 20.08.29
         */
        case "createJwt":
            http_response_code(200);

            // 1) 로그인 시 email, password 받기
            $result=isValidUser($req->userId, $req->password);
            if (!$result[0]) { // JWTPdo.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = $result[2];
                $res->message = $result[1];
                echo json_encode($res, JSON_NUMERIC_CHECK);
                return;
            }
            $deviceToken=$req->tokenId;
            if($deviceToken==null) {
                $res->isSuccess = False;
                $res->code = 200;
                $res->message = "디바이스토큰이 없습니다";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            // 2) JWT 발급
            // Payload에 맞게 다시 설정 요함, 아래는 Payload에 userIdx를 넣기 위한 과정
            $userIdx=getUserIdxById($req->userId);   // JWTPdo.php 에 구현
            insertToken($userIdx,$deviceToken);
            $jwt = getJWT($userIdx, JWT_SECRET_KEY); // function.php 에 구현
            $res->result=new stdClass();
            $res->result->jwt = $jwt;
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "로그인 됐습니다.";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        /*
         * API No. 2
         * API Name : JWT 유효성 검사 테스트 API
         * 마지막 수정 날짜 : 20.08.29
         */
        case "validateJwt":

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];

            // 1) JWT 유효성 검사
            if (!isValidJWT($jwt, JWT_SECRET_KEY)) { // function.php 에 구현
                $res->isSuccess = FALSE;
                $res->code = 417;
                $res->message = "재로그인해주세요";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                addErrorLogs($errorLogs, $res, $req);
                return;
            }

            // 2) JWT Payload 반환
            http_response_code(200);
            $res->isSuccess = TRUE;
            $res->code = 200;
            $res->message = "로그인 됐습니다";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
