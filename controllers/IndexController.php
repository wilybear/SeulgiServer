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
         * API No. 0
         * API Name : 테스트 API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "test":
            http_response_code(200);
            $res->result = test();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
           break;
        /*
         * API No. 0
         * API Name : 테스트 Path Variable API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "testDetail":
            http_response_code(200);
            $res->result = testDetail($vars["testNo"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        /*
         * API No. 0
         * API Name : 테스트 Body & Insert API
         * 마지막 수정 날짜 : 19.04.29
         */
        case "testPost":
            http_response_code(200);
            $res->result = testPost($req->name);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "createUser":
            http_response_code(200);
            if (isset($req->name)) {
                //$profileImgFile = $_FILES['image']['tmp_name'];
                $res->result = createUser($req->name, $req->email, $req->nick, $req->profileImgFile, $req->phone
                    , $req->region, $req->birth, $req->SNS, $req->sex, $req->snsToken, $req->FCMToken);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "유저 생성 성공";
            }else{
                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "image 파라미터 체크해주세요";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "deleteUser":
            http_response_code(200);
            $res->result = deleteUser($vars["user-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "유저 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getUserInfo":
            http_response_code(200);
            $res->result = getUserInfo($vars["user-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "유저 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "updateUser":
                http_response_code(200);
            $res->result = updateUser($req->nick,$req->profileImg,$req->phone
                ,$req->region,$req->user_id);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "유저 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getImages":
            http_response_code(200);
            $res->result = getAllFiles();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "이미지 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "reportContent":
            http_response_code(200);
            //TODO JWT 체크
            if(!checkIfExist("Report",["user_id","id","report_type"],[$req->user_id,$req->id,$req->report_type])){
                reportContent($req->user_id,$req->id,$req->report_type);
                $res->message = "신고 성공";
                $res->isSuccess = TRUE;
                $res->code = 100;
            }else{
                $res->message = "이미 신고 했습니다";
                $res->isSuccess = FALSE;
                $res->code = 100;
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
