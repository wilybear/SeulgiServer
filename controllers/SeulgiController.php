<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";
$title_regex="/^[\p{Z}\s]*(?:[^\p{Z}\s][\p{Z}\s]*){2,45}$/u";
$wish_regex="/^[\p{Z}\s]*(?:[^\p{Z}\s][\p{Z}\s]*){2,300}$/u";
$introduction_regex="/^[\p{Z}\s]*(?:[^\p{Z}\s][\p{Z}\s]*){2,200}$/u";
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
            //토큰 확인
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);

            if($userId == null){
                failRes($res, "존재하지 않는 아이디입니다.", 204);
                break;
            }

            //이미지 체크
            if(isset($req->talent_images)) {
                foreach ($req->talent_images as $image) {
                    if (!checkImageExt($image->talent_image)) {
                        failRes($res, "이미지 파일 형식 오류", 210);
                        break;
                    }
                }
            }

            if(checkIfExist("TalentResume",["user_id"],[$userId])){
                failRes($res, "이력서가 이미 존재합니다.", 203);
                break;
            }
            //reg체크
            if(preg_match($introduction_regex, $req->introduction)!=true){
                failRes($res, "올바르지 않은 자기소개 입니다.", 202);
                break;
            }
            if(preg_match($title_regex, $req->title)!=true){
                failRes($res, "올바르지 않은 제목 입니다.", 202);
                break;
            }
            if(preg_match($wish_regex, $req->wish)!=true){
                failRes($res, "올바르지 않은 희망사항 입니다.", 202);
                break;
            }


           createResume($userId,$req->title,$req->introduction,$req->talent_images,$req->isOnLine
                ,$req->desired_day,$req->desired_regions,$req->talent_have,$req->talent_want,$req->wish);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "updateResume":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            echo $userId." : userID\n";
            //이미지 체크
            if(isset($req->talent_images)) {
                foreach ($req->talent_images as $image) {
                    if (!checkImageExt($image->talent_image)) {
                        failRes($res, "이미지 파일 형식 오류", 210);
                        break;
                    }
                }
            }

            if(!checkIfExist("TalentResume",["user_id"],[$userId])){
                failRes($res, " 해당 이력서가 존재하지 않습니다.", 204);
                break;
            }

            if(!checkResumePermission($req->resume_id,$userId)){
                failRes($res, "교환서 수정 권한이 없습니다.", 205);
                break;
            }
            //reg체크
            if(preg_match($introduction_regex, $req->introduction)!=true){
                failRes($res, "올바르지 않은 자기소개 입니다.", 202);
                break;
            }
            if(preg_match($title_regex, $req->title)!=true){
                failRes($res, "올바르지 않은 제목 입니다.", 202);
                break;
            }
            if(preg_match($wish_regex, $req->wish)!=true){
                failRes($res, "올바르지 않은 희망사항 입니다.", 202);
                break;
            }


            updateResume($req->resume_id,$userId,$req->title,$req->introduction,$req->talent_images,$req->isOnLine
                ,$req->desired_day,$req->desired_regions,$req->talent_have,$req->talent_want,$req->wish);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getResume":
            http_response_code(200);

            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);

            if(!checkIfExist("TalentResume",["resume_id"],[$vars["resume-id"]])){
                failRes($res, "존재하지 않는 교환서입니다.", 204);
                break;
            }

            if(!checkResumePermission($vars["resume-id"],$userId)){
                failRes($res, "교환서 조회 권한이 없습니다.", 205);
                break;
            }

            $res->result = getResumeData($vars["resume-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 불러오기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "deleteResume":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            if(!checkIfExist("TalentResume",["resume_id"],[$vars["resume-id"]])){
            failRes($res, "존재하지 않는 교환서입니다.", 204);
            break;
            }
            if(!checkResumePermission($vars["resume-id"],$userId)){
                failRes($res, "교환서 수정 권한이 없습니다.", 205);
                break;
            }
            deleteResume($vars["resume-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getResumeList":
            http_response_code(200);
            // 유저가 scrap을 했는지 알아보기 위해서는 유저 아이디를 건내야한다.
            $res->result = getResumeList($_GET["user-id"],$_GET["filter"],$_GET["talent-want"],$_GET["talent-have"],$_GET["isOnline"],$_GET["region"],$_GET["desired-day"],$_GET["lastIdx"]);
            if(empty($res->result)){
                failRes($res, "교환서가 없습니다", 211);;
                break;
            }
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 리스트 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "scrapResume":
            http_response_code(200);
            //TODO JWT 체크
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

            if(!checkIfExist("ResumeScrap",["user_id","resume_id"],[$userId,$req->resume_id])){
                scrapResume($userId,$req->resume_id);
                $res->message = "교환서 스크랩 성공";
                $res->isSuccess = TRUE;
                $res->code = 100;
            }else{
                deleteScrapResume($userId,$req->resume_id);
                $res->message = "교환서 스크랩 해제 성공";
                $res->isSuccess = TRUE;
                $res->code = 100;
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getResumeBasic":
            http_response_code(200);
            if(!checkIfExist("TalentResume",["resume_id"],[$vars["resume-id"]])){
                failRes($res, "존재하지 않는 교환서입니다.", 204);
                break;
            }
            // 유저가 scrap을 했는지 알아보기 위해서는 유저 아이디를 건내야한다.
            $res->result =  getBasicResumeData($vars["resume-id"],$_GET["user-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 기본 정보 불러오기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getTalentHave":
            http_response_code(200);
            if(!checkIfExist("TalentHave",["resume_id"],[$vars["resume-id"]])){
                failRes($res, "존재하지 않는 교환서입니다.", 204);
                break;
            }
            $res->result =  getTalentHave($vars["resume-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 가진 재능 불러오기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getTalentWant":
            http_response_code(200);
            if(!checkIfExist("TalentWant",["resume_id"],[$vars["resume-id"]])){
                failRes($res, "존재하지 않는 교환서입니다.", 204);
                break;
            }
            $res->result =  getTalentWant($vars["resume-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 원하는 재능 불러오기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getDesiredOpt":
            http_response_code(200);
            if(!checkIfExist("TalentResume",["resume_id"],[$vars["resume-id"]])){
                failRes($res, "존재하지 않는 교환서입니다.", 204);
                break;
            }
            $res->result =  getDesiredCondition($vars["resume-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "교환서 희망 조건 불러오기 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getResumeReviews":
            http_response_code(200);
            if(!checkIfExist("TalentResume",["resume_id"],[$vars["resume-id"]])){
                failRes($res, "존재하지 않는 교환서입니다.", 204);
                break;
            }
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
