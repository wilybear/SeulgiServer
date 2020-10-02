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
        case "getResumeList":
            http_response_code(200);
            $res->result = getResumeList($_GET["user-id"],$_GET["filter"],$_GET["talent-want"],$_GET["talent-have"],$_GET["isOnline"],$_GET["region"],$_GET["desired-day"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 리스트 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "scrapResume":
            http_response_code(200);
            //TODO JWT 체크
            if(!checkIfExist("ResumeScrap",["user_id","resume_id"],[$req->user_id,$req->resume_id])){
                scrapResume($req->user_id,$req->resume_id);
                $res->message = "교환서 스크랩 성공";
                $res->isSuccess = TRUE;
                $res->code = 100;
            }else{
                deleteScrapResume($req->user_id,$req->resume_id);
                $res->message = "교환서 스크랩 해제 성공";
                $res->isSuccess = TRUE;
                $res->code = 100;
            }
            $res->isSuccess = TRUE;
            $res->code = 100;
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getResumeBasic":
            http_response_code(200);
            $res->result =  getBasicResumeData($vars["resume-id"],$_GET["user-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 기본 정보 불러오기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getTalentHave":
            http_response_code(200);
            $res->result =  getTalentHave($vars["resume-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 가진 재능 불러오기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getTalentWant":
            http_response_code(200);
            $res->result =  getTalentWant($vars["resume-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 원하는 재능 불러오기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getDesiredOpt":
            http_response_code(200);
            $res->result =  getDesiredCondition($vars["resume-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 희망 조건 불러오기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getResumeReviews":
            http_response_code(200);
            $res->result = getResumeReviews($vars["resume-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 리뷰들 불러오기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
