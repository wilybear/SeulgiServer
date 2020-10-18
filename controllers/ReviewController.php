<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";
$content_regex="/^[\p{Z}\s]*(?:[^\p{Z}\s][\p{Z}\s]*){0,200}$/u";
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
        case "createReview":
            //중복생성 못하게
            http_response_code(200);
            //교환 내역 확인
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            if(!checkIfExist("TalentResume",["resume_id"],[$req->resume_id])){
                failRes($res, "존재하지 않는 교환서입니다.", 204);
                break;
            }
            if(!checkIsSender($userId,$req->resume_id)){
                failRes($res,"Sender가 아님",204);
                break;
            }

            if(checkIfExist("Review",["reviewer_id","resume_id"],[$userId,$req->resume_id])){
                failRes($res,"이미 후기를 남겼습니다.",203);
                break;
            }

            if(preg_match($content_regex, $req->content)!=true){
                failRes($res, "올바르지 않은 내용 입니다.", 202);
                break;
            }
            
            createReview($userId,$req->resume_id,$req->content,$req->rate);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "리뷰 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "deleteReview":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            
            if(!checkIfExist("Review",["review_id"],[$vars["review-id"]])){
                failRes($res, "후기가 존재하지 않습니다.", 204);
                break;
            }
            
            if(!checkReivewPermission($userId,$vars["review-id"])){
                failRes($res, "수정 권한 없음.", 205);
                break;
            }
            
             deleteReview($vars["review-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "리뷰 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getReviews":
            http_response_code(200);
            if(!checkIfExist("Review",["review_id"],[$vars["review-id"]])){
                failRes($res, "존재하지 않는 리뷰id입니다.", 204);
                break;
            }

            $res->result = getReview($vars["review-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "리뷰 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "updateReview":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);

            if(!checkIfExist("Review",["review_id"],[$req->review_id])){
                failRes($res, "후기가 존재하지 않습니다.", 204);
                break;
            }

            if(!checkReivewPermission($userId,$req->review_id)){
                failRes($res, "수정 권한 없음.", 205);
                break;
            }

            if(preg_match($content_regex, $req->content)!=true){
                failRes($res, "올바르지 않은 내용 입니다.", 202);
                break;
            }

            updateReview($req->review_id,$req->content,$req->rate);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "리뷰 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
