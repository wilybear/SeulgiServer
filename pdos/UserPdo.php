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

