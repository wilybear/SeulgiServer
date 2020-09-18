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
        case "createResume":
            http_response_code(200);
            $res->result = createResume($req->user_id,$req->title,$req->introduction,$req->talent_images,$req->isOnLine
                ,$req->desired_day,$req->desired_regions,$req->talent_have,$req->talent_want);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "updateResume":
            http_response_code(200);
            $res->result = updateResume($req->resume_id,$req->user_id,$req->title,$req->introduction,$req->talent_images,$req->isOnLine
                ,$req->desired_day,$req->desired_regions,$req->talent_have,$req->talent_want);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getResume":
            http_response_code(200);
            $res->result = getResumeData($vars["resume-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 불러오기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteResume":
            http_response_code(200);
            $res->result = deleteResume($vars["resume-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "createReview":
            //중복생성 못하게
            http_response_code(200);
            $res->result = createReview($req->reviewer_id,$req->resume_id,$req->content,$req->rate);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "리뷰 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "deleteReview":
            http_response_code(200);
            $res->result = deleteReview($vars["review-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "리뷰 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getReviews":
            http_response_code(200);
            $res->result = getReviews($_GET["resume-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "리뷰 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "updateReview":
            http_response_code(200);
            $res->result = updateReview($req->resume_id,$req->content,$req->rate);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "리뷰 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
            
        case "createExchangeReq":
            http_response_code(200);
            $res->result = createExchangeReq($req->sender_id,$req->resume_id);
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

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
