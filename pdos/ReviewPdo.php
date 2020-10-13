<?php

function createReview($reviewer_id,$resume_id,$content,$rate){
    $pdo = pdoSqlConnect();
    //TODO: exchange기록이 있는 사람만, 여러번 제한, 자기 자신 불가
    $query = "INSERT INTO Review (reviewer_id,resume_id,content,rate) VALUES (?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$reviewer_id,$resume_id,$content,$rate]);

    $st = null;
    $pdo = null;

}
function deleteReview($review_id)
{
    $pdo = pdoSqlConnect();
    $query = "UPDATE Review set isDeleted = 1 WHERE review_id = ? ;";

    $st = $pdo->prepare($query);
    $st->execute([$review_id]);

    $st = null;
    $pdo = null;
}

function getReview($review_id){
    $pdo = pdoSqlConnect();
    $query = "Select nick_name as reviewer_nick, profile_img,rate, content, Review.createTime
from Review join User on Review.reviewer_id = User.user_id
where review_id = ? and Review.isDeleted = 0;";

    $st = $pdo->prepare($query);
    $st->execute([$review_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

//    foreach($res as &$image){
//        if($image['profile_img']!=null) {
//            $absurl = 'http://' . gethostbyname(gethostname()) . PROFILE_RETRIVE_PATH . $image['profile_img'];
//            $image['profile_img'] = $absurl;
//        }
//    }
    $st = null;
    $pdo = null;

    return $res;
}

//안씀 일단 냅둠
//해당 이력서의 모든 review들을 가지고옴
function getReviews($resume_id){
    $pdo = pdoSqlConnect();
    $query = "Select nick_name as reviewer_nick, profile_img,rate, content, Review.createTime
from Review join User on Review.reviewer_id = User.user_id
where resume_id = ? and Review.isDeleted = 0 order by Review.createTime DESC ;";

    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

//    foreach($res as &$image){
//        if($image['profile_img']!=null) {
//            $absurl = 'http://' . gethostbyname(gethostname()) . PROFILE_RETRIVE_PATH . $image['profile_img'];
//            $image['profile_img'] = $absurl;
//        }
//    }
    $st = null;
    $pdo = null;

    return $res;
}

function updateReview($review_id, $content, $rate){
    $pdo = pdoSqlConnect();
    //TODO: exchange기록이 있는 사람만, 여러번 제한, 자기 자신 불가
    $query = "UPDATE Review SET content=?,rate=? WHERE review_id = ? AND isDeleted = 0";

    $st = $pdo->prepare($query);
    $st->execute([$content,$rate,$review_id]);

    $st = null;
    $pdo = null;
}


function checkReivewPermission($user_id,$review_id)
{
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM Review WHERE review_id = ? and reviewer_id = ? and isDeleted =0) As exist; ";
    $st = $pdo->prepare($query);
    $st->execute([$review_id, $user_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;
    return intval($res[0]["exist"]);
}
