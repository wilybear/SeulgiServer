<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (Object)Array();
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
         * API No. 2
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 20.09.02
         * 교환서 올릴시에 필수항목들이 존재하는지만 체크
         * isset 사용 null 체크
         */

        case "createExchangeReq":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            if(!checkIfExist("TalentResume",["resume_id"],[$req->resume_id])){
                failRes($res, "존재하지 않는 교환서입니다.", 211);
                break;
            }
            if(checkExchangeHistory($userId,$req->resume_id)){
                failRes($res, "교환 신청 중인 교환서입니다.", 211);
                break;
            }
            $res->result = createExchangeReq($userId,$req->resume_id);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환 요청 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getReceivedExchangeReqs":
            http_response_code(200);
            $res->result = getReceivedExchangeReqs($vars["user-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "수신한 교환 요청 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getSendedExchangeReqs":
            http_response_code(200);
            $res->result = getSendedExchangeReqs($vars["user-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "발신한 교환 요청 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "acceptExchangeReq":
            http_response_code(200);
            //TODO: $req->user_id 유저 체크 , 중복 xx
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);

            if(!checkExchange($userId,$req->exchange_id)){
                failRes($res, "존재하지 않는 교환 요청입니다..", 211);
                break;
            }
            $res->result = acceptExchangeReq($req->exchange_id);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = " 교환 요청 수락 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getExchangedReqs":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            $res->result = getExchangedReqs($userId);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환 요청들 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "ExchangeInfo":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);

            if(!checkIfExist("ExchangeRequest",["sender_id,resume_id"],[$userId,$_GET["op-resume-id"]])){
                failRes($res,"교환 신청이 제대로 이루어지지 않았습니다.",201);
                break;
            }

            $res->result = getExchangeInfo($userId,$_GET["op-resume-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환 정보 조회";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getExchangeReq":
            //해당 유저과 교환에 참여한 유저인지 검증
            http_response_code(200);
            $res->result = getExchangeReq($_GET["user_id"],$vars["exchange-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환 정보 조회";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "deleteExchangeReq":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);

            if(!checkExchangePermission($userId,$_GET["exchange-id"])){
                failRes($res,"권한이 없습니다",201);
                break;
            }
            $res->result = deleteExchange($_GET["exchange-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환 요청 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
