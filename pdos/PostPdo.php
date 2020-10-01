<?php

function createPost($user_id, $content, $post_image)
{
    $pdo = pdoSqlConnect();
    try {
        $pdo->beginTransaction();
        $query = "INSERT INTO Post (user_id,content) VALUES (?,?);";
        $st = $pdo->prepare($query);
        $st->execute([$user_id, $content]);
        $post_id = $pdo->lastInsertId();
        if ($post_image != null) {
            switch ($post_image{0}) {
                case '/':
                    $extension = 'jpg';
                    break;
                case 'i':
                    $extension = 'png';
                    break;
                default:
                    throw new Exception("wrong extension");
            }
            $binary = base64_decode($post_image);
            $name = round(microtime(true) * 1000) . '.' . $extension;
            // $filedest = UPLOAD_PATH . $name;
            //move_uploaded_file($file, $filedest);
            $file = fopen(POST_UPLOAD_PATH . $name, 'wb');
            fwrite($file, $binary);
            fclose($file);
            //$url = $server_ip = gethostbyname(gethostname());
            $query = "UPDATE Post SET post_image = ? WHERE post_id = ?";
            $st = $pdo->prepare($query);
            $st->execute([$name, $post_id]);
        }
        $pdo->commit();
    } catch (Exception $e) {
        echo $e . "error on creating post";
        $pdo->rollback();
        return false;
    }
    $st = null;
    $pdo = null;

    return true;
}

function updatePost($post_id,$content,$post_image){
    $pdo = pdoSqlConnect();
    $query = "SELECT post_image FROM Post WHERE post_id = ?";
    $st = $pdo->prepare($query);
    $st->execute([$post_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    if($res[0]["post_image"] != $post_image){
        echo "post_image 바뀜";
        if($res[0]["post_image"] != null) {
            unlink(POST_UPLOAD_PATH . $res[0]["post_image"]);
        }
        if($post_image != null) {
            switch ($post_image{0}) {
                case '/':
                    $extension = 'jpg';
                    break;
                case 'i':
                    $extension = 'png';
                    break;
                default:
                    throw new Exception("wrong extension");
            }
            $binary = base64_decode($post_image);
            $name = round(microtime(true) * 1000) . '.' . $extension;
            // $filedest = UPLOAD_PATH . $name;
            //move_uploaded_file($file, $filedest);
            $file = fopen(POST_UPLOAD_PATH . $name, 'wb');
            fwrite($file, $binary);
            fclose($file);
            $post_image = $name;
        }
    }
    $query = "UPDATE Post set content = ?,post_image = ? WHERE post_id = ? ;";

    $st = $pdo->prepare($query);
    $st->execute([$content,$post_image,$post_id]);

    $st = null;
    $pdo = null;
}

function getPost($post_id){
    $pdo = pdoSqlConnect();
    $query = "select User.user_id,nick_name,profile_img, content, Post.createTime, post_image from Post
join User on Post.user_id = User.user_id where post_id = ? and Post.isDeleted = 0;";


    //댓글 카운트와 like카운트
    $st = $pdo->prepare($query);
    $st->execute([$post_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    if($res[0] == null){
        return false;
    }

    if($res[0]['profile_img']!=null) {
        $absurl = 'http://' . gethostbyname(gethostname()) . PROFILE_RETRIVE_PATH . $res[0]['profile_img'];
        $res[0]['profile_img'] = $absurl;
    }

    if($res[0]['post_image']!=null) {
        $absurl = 'http://' . gethostbyname(gethostname()) . POST_RETRIVE_PATH . $res[0]['post_image'];
        $res[0]['post_image'] = $absurl;
    }

    $res[0] += getLikeCnt($post_id);
    $res[0] += getCommentCnt($post_id);


    //comment들 불러오기
    $query = "select nick_name,content, User.user_id, profile_img,Comment.createTime,comment_id  from Comment
join User on Comment.user_id = User.user_id 
where Comment.isDeleted = 0 and post_id = ? 
order by Comment.createTime desc;
";

    //댓글 카운트와 like카운트
    $st = $pdo->prepare($query);
    $st->execute([$post_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res[0]["comments"] = $st->fetchAll();
    foreach ($res[0]["comments"] as &$comment){
        if($comment['profile_img']!=null) {
            $absurl = 'http://' . gethostbyname(gethostname()) . PROFILE_RETRIVE_PATH . $comment['profile_img'];
            $comment['profile_img'] = $absurl;
        }
    }

    $st = null;
    $pdo = null;

    return $res;
}

function getPostList($filter){
    $pdo = pdoSqlConnect();
    //filter 미구현
    $query = "select post_id, nick_name,content, Post.createTime, post_image from Post
join User on Post.user_id = User.user_id where Post.isDeleted = 0 order by Post.createTime desc
limit 0, 5;";
    //댓글 카운트와 like카운트
    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    foreach($res as &$post){
        if($post['post_image']!=null) {
            $absurl = 'http://' . gethostbyname(gethostname()) . POST_RETRIVE_PATH . $post['post_image'];
            $post['post_image'] = $absurl;
        }

        $post += getLikeCnt($post['post_id']);
        $post += getCommentCnt($post['post_id']);

    }
    $st = null;
    $pdo = null;

    return $res;
}

function deletePost($post_id){
    $pdo = pdoSqlConnect();
    try {
        $pdo->beginTransaction();
        $query = "UPDATE Post set isDeleted = 1 WHERE post_id = ? ;";

        $st = $pdo->prepare($query);
        $st->execute([$post_id]);

        $query = "SELECT post_image FROM Post WHERE post_id = ?";
        $st = $pdo->prepare($query);
        $st->execute([$post_id]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();
        if ($res[0]["post_image"] != null) {
            unlink(POST_UPLOAD_PATH . $res[0]["post_image"]);
        }
        $pdo->commit();
    } catch (Exception $e) {
        echo $e . "error on deleting post";
        $pdo->rollback();
        return false;
    }
    $st = null;
    $pdo = null;
    return true;
}

function likePost($user_id,$post_id){
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO PostLike (user_id,post_id) VALUES (?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$user_id,$post_id]);

    $st = null;
    $pdo = null;
}

function deleteLikePost($user_id,$post_id){
    $pdo = pdoSqlConnect();

    $query = "DELETE FROM PostLike WHERE user_id = ? and post_id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$user_id,$post_id]);

    $st = null;
    $pdo = null;
}

function isLiked($user_id, $post_id){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM PostLike WHERE user_id=? AND post_id = ?) AS exist;";

    $st = $pdo->prepare($query);
    $st->execute([$user_id,$post_id ]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return intval($res[0]["exist"]);
}

function getLikeCnt($post_id){
    $pdo = pdoSqlConnect();
    $query = "select count(*) as likeCnt from PostLike where post_id = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$post_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    return $st->fetchAll()[0];
}
function getCommentCnt($post_id){
    $pdo = pdoSqlConnect();
    $query = "select count(*) as CommentCnt from Comment where post_id = ? and isDeleted = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$post_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    return $st->fetchAll()[0];
}
function createComment($post_id,$user_id,$content){
    $pdo = pdoSqlConnect();

    $query = "INSERT INTO Comment (post_id,user_id,content) VALUES (?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$post_id,$user_id,$content]);

    $st = null;
    $pdo = null;

}
function updateComment($comment_id,$content){
    $pdo = pdoSqlConnect();
    $query = "UPDATE Comment set content = ? WHERE comment_id = ? ;";

    $st = $pdo->prepare($query);
    $st->execute([$content,$comment_id]);

    $st = null;
    $pdo = null;
}

function deleteComment($comment_id){
    $pdo = pdoSqlConnect();
    $query = "UPDATE Comment set isDeleted = 1 WHERE comment_id = ? ;";

    $st = $pdo->prepare($query);
    $st->execute([$comment_id]);

    $st = null;
    $pdo = null;
}
