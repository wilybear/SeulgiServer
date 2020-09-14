<?php

function createUser($name,$email,$nick,$profileImg,$phone
                ,$region,$birth,$SNS,$sex,$snsToken,$FCMToken)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO User (user_name,user_email,nick_name,profile_img,phone
,region, birth, SNS, sex, snsToken,FCMToken) VALUES (?,?,?,?,?,?,?,?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$name,$email,$nick,$profileImg,$phone
        ,$region,$birth,$SNS,$sex,$snsToken,$FCMToken]);

    $st = null;
    $pdo = null;

}

function getUserInfo($user_id){
    $pdo = pdoSqlConnect();
    $query = "SELECT user_name,user_email,nick_name,profile_img,phone
,region, birth, sex FROM User WHERE user_id= ? ;";

    $st = $pdo->prepare($query);
    $st->execute([$user_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0];
}

function updateUser($nick,$profileImg,$phone
    ,$region,$user_id){
    $pdo = pdoSqlConnect();
    //user 확인 필요, img update 처리
    $query = "UPDATE User SET nick_name = ?,profile_img = ?,phone = ?
,region = ? WHERE user_id= ?";

    $st = $pdo->prepare($query);
    $st->execute([$nick,$profileImg,$phone
        ,$region,$user_id]);

    $st = null;
    $pdo = null;
}

function deleteUser($user_id){
    $pdo = pdoSqlConnect();
    //user 확인
    $query = "UPDATE User SET isDeleted = 1 WHERE user_id= ?";

    $st = $pdo->prepare($query);
    $st->execute([$user_id]);

    $st = null;
    $pdo = null;
}

