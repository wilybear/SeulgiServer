<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";
$nick_regex="/^[!^\x{1100}-\x{11FF}\x{3130}-\x{318F}\x{AC00}-\x{D7AF}0-9a-zA-Z]{4,15}$/u";


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
            $check_nick = preg_match($nick_regex,$req->nick);
            if(checkIfExist("User",["phone"],[$req->phone])){
                failRes($res,"해당 전화번호로 가입한 이력이 존재합니다.",203);
                break;
            }
            if(!preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $req->email)){
                failRes($res,"올바르지 않은 이메일 형식입니다.",202);
                break;
            }
            if(checkIfExist("User",["user_email"],[$req->email])){
                failRes($res,"해당 이메일로 가입한 이력이 존재합니다.",203);
                break;
            }

            if($check_nick!=true){
                failRes($res,"올바르지 않은 닉네임입니다.",202);
                //4~15자 영어 숫자 한글만
                break;
            }
            if(checkIfExist("User",["nick_name"],[$req->nick])){
                failRes($res,"중복된 닉네임이 존재합니다.",203);
                break;
            }
            /*
            if(isset($req->profileImgFile) and !checkImageExt($req->profileImgFile)){
                failRes($res,"이미지 파일 형식 오류", 210);
                break;
            }
            */

            if(isset($req->profile_img) and !preg_match(URL_REGEX,$req->profile_img)){
                failRes($res,"이미지 url 형식 오류", 210);
                break;
            }

            if (isset($req->name) and isset($req->email) and isset($req->nick) and isset($req->phone) and isset($req->birth)
                and isset($req->sns)) {
                //$profileImgFile = $_FILES['image']['tmp_name'];
                createUser($req->name, $req->email, $req->nick, $req->profile_img, $req->phone
                    , $req->birth, $req->sns, $req->sex, $req->sns_token, $req->FCM_token);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "유저 생성 성공";
                echo json_encode($res, JSON_NUMERIC_CHECK);
            }else{
                $res->isSuccess = FALSE;
                $res->code = 211;
                $res->message = "파라미터 오류.";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }
            break;
        case "deleteUser":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            if(checkIfIsDeleted("User",["user_id"],[$userId])){
                failRes($res,"이미 탈퇴된 id입니다",204);
                break;
            }
            if(!checkIfExist("User",["user_id"],[$userId])){
                failRes($res,"존재하지 않는 아이디 입니다.",204);
                break;
            }
            $res->result = deleteUser($userId);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "유저 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getUserInfo":

            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);

            if(!checkIfExist("User",["user_id"],[$userId])){
                failRes($res,"존재하지 않는 아이디 입니다.",204);
                break;
            }

            $res->result = getUserInfo($userId);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "유저 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "updateUser":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);

            if(!checkIfExist("User",["user_id"],[$userId])){
                failRes($res,"존재하지 않는 아이디 입니다.",204);
                break;
            }
            $check_nick = preg_match($nick_regex,$req->nick);
            if($check_nick!=true) {
                failRes($res, "올바르지 않은 닉네임입니다.", 202);
                //4~15자 영어 숫자 한글만
                break;
            }
            if(checkIfExist("User",["nick_name"],[$req->nick])){
                failRes($res,"중복된 닉네임이 존재합니다.",203);
                break;
            }
            if(isset($req->profile_img) and !preg_match(URL_REGEX,$req->profile_img)){

                failRes($res,"이미지 url 오류", 210);
                break;
            }
            updateUser($req->nick,$req->profile_img,$req->phone
                ,$userId);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "유저 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "reportContent":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);

            switch ($req->report_type){
                case 'comment':
                    $tp = checkIfExist("Comment",["comment_id"],[$req->id]);
                    break;
                case 'post':
                    $tp = checkIfExist("Post",["post_id"],[$req->id]);
                    break;
                case 'resume':
                    $tp = checkIfExist("TalentResume",["resume_id"],[$req->id]);
                    break;
                case 'user':
                    $tp = checkIfExist("User",["user_id"],[$req->id]);
                    break;
                default:
                    failRes($res,"올바르지 않은 type입니다.",211);
                    break;
            }
            if(!$tp){
                failRes($res,"존재하지 않는 content입니다.",204);
                break;
            }
            if(!checkIfExist("Report",["user_id","id","report_type"],[$userId ,$req->id,$req->report_type])){
                reportContent($userId ,$req->id,$req->report_type);
                $res->message = "신고 성공";
                $res->isSuccess = TRUE;
                $res->code = 100;
            }else{
                failRes($res,"이미 신고했습니다.",203);
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
