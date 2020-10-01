<?php

function createExchangeReq($sender_id,$resume_id){
    $pdo = pdoSqlConnect();
    //TODO: 여러번 신청 제한, sender != reviewer
    $query = "SELECT user_id FROM TalentResume Where resume_id = ? and isDeleted = 0";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $receiver_id = $st->fetchAll()[0]["user_id"];
    $query = "INSERT INTO ExchangeRequest (sender_id,resume_id,receiver_id) VALUES (?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$sender_id,$resume_id,$receiver_id]);

    $st = null;
    $pdo = null;
}
function getReceivedExchangeReqs($user_id){
    $pdo = pdoSqlConnect();
    $query = "select exchange_id, sender_id,updateTime, isExchanged  from ExchangeRequest where receiver_id = ? and isExchanged= 0 ;";
    $st = $pdo->prepare($query);
    $st->execute([$user_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    foreach ($res as &$exchanges){
        $query = "select nick_name as sender_nick, resume_id as sender_resume_id from User join TalentResume TR on User.user_id = TR.user_id where User.user_id = ? and TR.isDeleted =0;";
        $st = $pdo->prepare($query);
        $st->execute([$exchanges["sender_id"]]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $exchanges += $st->fetchAll()[0];
    }

    $st = null;
    $pdo = null;

    return $res;
}

function acceptExchangeReq($exchange_id){
    $pdo = pdoSqlConnect();
    $query = "UPDATE ExchangeRequest SET isExchanged = 1  WHERE exchange_id = ?  AND isDeleted = 0";

    $st = $pdo->prepare($query);
    $st->execute([$exchange_id]);

    $st = null;
    $pdo = null;
}

function getSendedExchangeReqs($user_id){
    $pdo = pdoSqlConnect();
    //수락 대기중. ..님이 수락 대기중입니다.
    $query = "select exchange_id, receiver_id,updateTime from ExchangeRequest where sender_id = ? and isExchanged = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$user_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    foreach ($res as &$exchanges){
        $query = "select nick_name as recevier_nick, resume_id as receiver_resume_id from User join TalentResume TR on User.user_id = TR.user_id where User.user_id = ? and TR.isDeleted =0;";
        $st = $pdo->prepare($query);
        $st->execute([$exchanges["receiver_id"]]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $exchanges += $st->fetchAll()[0];
    }

    $st = null;
    $pdo = null;

    return $res;

}

function getExchangedReqs($user_id){
    $pdo = pdoSqlConnect();
    //상대가 보냈는데 수락한 요청, 보낸 요청이 수락됬을때 3가지
    $query = "select exchange_id,sender_id ,receiver_id,updateTime, isExchanged  from ExchangeRequest where (sender_id = ? or receiver_id = ?) and isExchanged = 1;";
    $st = $pdo->prepare($query);
    $st->execute([$user_id,$user_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    foreach ($res as &$exchanges){
        if($exchanges["sender_id"]==$user_id){
            $opponent_id = $exchanges["receiver_id"];
        }else{
            $opponent_id = $exchanges["sender_id"];
        }
        $query = "select nick_name as opponent_nick, resume_id as opponent_resume_id, phone from User join TalentResume TR on User.user_id = TR.user_id where User.user_id = ? and TR.isDeleted =0;";
        $st = $pdo->prepare($query);
        $st->execute([$opponent_id]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $exchanges += $st->fetchAll()[0];
    }
    $st = null;
    $pdo = null;
    return $res;
}

function getExchangeInfo($user_id,$op_resume_id){
    $pdo = pdoSqlConnect();
    //나의 정보 먼저
    $query = "select resume_id from TalentResume join User using(user_id) where user_id = ? and TalentResume.isDeleted = 0 and User.user_id;";
    $st = $pdo->prepare($query);
    $st->execute([$user_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $my_resume_id = $st->fetchAll()[0]["resume_id"];

    $query = "SELECT talentCategory FROM TalentHave WHERE resume_id = ? and isDeleted = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$my_resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res["my"]["talent_have"]= $st->fetchAll();
    foreach($res["my"]["talent_have"] as &$talent){
        $talent["category"]=getCategoryName($talent["talentCategory"]);
        $query = "SELECT cat_name as detailed FROM DetailedHave NATURAL JOIN DetailedCat WHERE resume_id = ? AND talent_cat_id = ? and isDeleted = 0";
        $st = $pdo->prepare($query);
        $st->execute([$my_resume_id,$talent["talentCategory"]]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $talent["detailed_talent"] = $st->fetchAll();
        unset($talent["talentCategory"]);
    }
    
    //상대방 재능
    $query = "select nick_name from TalentResume join User using(user_id) where resume_id = ? and TalentResume.isDeleted = 0 and User.user_id;";
    $st = $pdo->prepare($query);
    $st->execute([$op_resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res["opponent"]["nick"] = $st->fetchAll()[0]["nick_name"];


    $query = "SELECT talentCategory FROM TalentHave WHERE resume_id = ? and isDeleted = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$op_resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res["opponent"]["talent_have"]= $st->fetchAll();
    foreach($res["opponent"]["talent_have"] as &$talent){
        $talent["category"]=getCategoryName($talent["talentCategory"]);
        $query = "SELECT cat_name as detailed FROM DetailedHave NATURAL JOIN DetailedCat WHERE resume_id = ? AND talent_cat_id = ? and isDeleted = 0";
        $st = $pdo->prepare($query);
        $st->execute([$op_resume_id,$talent["talentCategory"]]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $talent["detailed_talent"] = $st->fetchAll();
        unset($talent["talentCategory"]);
    }



    $st = null;
    $pdo = null;
    return $res;
}
