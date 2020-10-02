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

        case "createPost":
            http_response_code(200);
            $res->result = createPost($req->user_id,$req->content,$req->post_image);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "게시글 작성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "updatePost":
            http_response_code(200);
            $res->result = updatePost($req->post_id,$req->content,$req->post_image);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "게시글 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getPost":
            http_response_code(200);
            //user정보 받아서 좋아요 표시해야함
            $res->result = getPost($vars["post-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "게시글 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "getPostList":
            http_response_code(200);
            //user정보 받아서 좋아요 표시해야함
            $res->result = getPostList($_GET["filter"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "게시글 목록 조회 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "deletePost":
            http_response_code(200);
            $res->result = deletePost($_GET["post-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "게시글 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "likePost":
            http_response_code(200);
            if(!checkIfExist("PostLike",["user_id","post_id"],[$req->user_id,$req->post_id])) {
                $res->result = likePost($req->user_id, $req->post_id);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "게시글 좋아요 성공";
            }else{
                $res->result = deleteLikePost($req->user_id, $req->post_id);
                $res->isSuccess = TRUE;
                $res->code = 100;
                $res->message = "게시글 좋아요 취소 성공";
            }
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "createComment":
            http_response_code(200);
            $res->result = createComment($req->post_id,$req->user_id,$req->content);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "댓글 생성 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "updateComment":
            http_response_code(200);
            //user 인증
            $res->result = updateComment($req->comment_id,$req->content);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "댓글 수정 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
        case "deleteComment":
            http_response_code(200);
            //user 인증
            $res->result = deleteComment($_GET["comment-id"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "댓글 삭제 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;
    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
