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
function deleteReview($review_id){
    $pdo = pdoSqlConnect();
    $query = "UPDATE Review set isDeleted = 1 WHERE review_id = ? ;";

    $st = $pdo->prepare($query);
    $st->execute([$review_id]);

    $st = null;
    $pdo = null;
}
//해당 이력서의 모든 review들을 가지고옴
function getReviews($resume_id){
    $pdo = pdoSqlConnect();
    $query = "Select nick_name as review_nick, profile_img as review_profile_imgm,rate, content, Review.createTime
from Review join User on Review.reviewer_id = User.user_id
where resume_id = ? and Review.isDeleted = 0;";

    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

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


