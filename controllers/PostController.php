<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";
$content_regex="/^[\p{Z}\s]*(?:[^\p{Z}\s][\p{Z}\s]*){2,300}$/u";
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

        case "createPost":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            if(preg_match($content_regex, $req->content)!=true){
                failRes($res, "올바르지 않은 내용 입니다.", 202);
                break;
            }

            if(isset($req->post_image) and !preg_match(URL_REGEX,$req->post_image)){
                failRes($res,"이미지 파일 형식 오류", 210);
                break;
            }
            createPost($userId,$req->content,$req->post_image);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "게시글 작성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "updatePost":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);


            if(preg_match($content_regex, $req->content)!=true){
                failRes($res, "올바르지 않은 내용 입니다.", 202);
                break;
            }
            if(isset($req->post_image) and !preg_match(URL_REGEX,$req->post_image)){
                failRes($res,"이미지 파일 형식 오류", 210);
                break;
            }

            if(!checkPostPermission($userId,$req->post_id)){
                failRes($res, "게시글 수정 권한이 없습니다.", 205);
                break;
            }
            updatePost($req->post_id,$req->content,$req->post_image);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "게시글 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getPost":
            http_response_code(200);
            //user정보 받아서 좋아요 표시해야함
            if(!$res->result = getPost($vars["post-id"])){
                failRes($res, "존재하지않는 게시글입니다..", 204);;
                break;
            }
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "게시글 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getPostList":
            http_response_code(200);
            //user정보 받아서 좋아요 표시해야함
            $keyword = preg_replace("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "", $_GET["keyword"]);
            $res->result = getPostList($keyword,$_GET["lastIdx"]);
            if(empty($res->result)){
                failRes($res, "더 이상 게시글이 없습니다", 212);;
                break;
            }
            
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "게시글 목록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "deletePost":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);

            if(!checkPostPermission($userId,$vars["post-id"])){
                failRes($res, "게시글 수정 권한이 없습니다.", 205);
                break;
            }
            deletePost($vars["post-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "게시글 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "likePost":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            if(!checkIfExist("PostLike",["user_id","post_id"],[$userId,$req->post_id])) {
               likePost($userId, $req->post_id);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "게시글 좋아요 성공";
            }else{
                deleteLikePost($userId, $req->post_id);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "게시글 좋아요 취소 성공";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "createComment":
            http_response_code(200);
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            if(preg_match($content_regex, $req->content)!=true){
                failRes($res, "올바르지 않은 내용 입니다.", 202);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);
            createComment($req->post_id,$userId,$req->content);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "댓글 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "updateComment":
            http_response_code(200);
            //user 인증
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);

            if(!checkCommentPermission($userId,$req->comment_id)) {
                failRes($res, "댓글 수정 권한이 없습니다.", 205);
                break;
            }

            if(preg_match($content_regex, $req->content)!=true){
                failRes($res, "올바르지 않은 내용 입니다.", 202);
                break;
            }
            updateComment($req->comment_id,$req->content);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "댓글 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "deleteComment":
            http_response_code(200);
            //user 인증
            $jwt = $_SERVER["HTTP_X_ACCESS_TOKEN"];
            if (!isValidHeader($jwt, JWT_SECRET_KEY)) {
                failRes($res,"유효하지 않은 토큰입니다",201);
                addErrorLogs($errorLogs, $res, $req);
                break;
            }
            $userId = getUserNoFromHeader($jwt, JWT_SECRET_KEY);

            if(!checkCommentPermission($userId,$vars["comment-id"])) {
                failRes($res, "댓글 수정 권한이 없습니다.", 205);
                break;
            }
            deleteComment($vars["comment-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "댓글 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
