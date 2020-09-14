<?php

function createResume($user_id,$title,$introduction,$talent_images,$isOnLine,$desired_day,$desired_regions,
                    $talent_have,$talent_want)
{
    $pdo = pdoSqlConnect();
    try {
        $pdo->beginTransaction();
        //교환서 기본 사항 저장 TODO: 중복처리
        $query = "INSERT INTO TalentResume (user_id,title, introduction, isOnLine) VALUES (?,?,?,?);";
        $st = $pdo->prepare($query);
        $st->execute([$user_id,$title,$introduction,$isOnLine]);
        $resume_id = $pdo->lastInsertId();

        //교환서 이미지 저장 TODO: 갯수 제한, 중복처리
        $query = "INSERT INTO TalentImage (resume_id,talent_image) VALUES (?,?)";
        $st = $pdo->prepare($query);
        foreach ($talent_images as $talent_image){
            $st->execute([$resume_id,$talent_image->talent_image]);
        }

        //희망 요일 저장  TODO: 중복처리
        $query = "INSERT INTO DesiredDay (resume_id, mon, tue, wed, thu, fri, sat, sun) VALUES (?,?,?,?,?,?,?,?)";
        $st = $pdo->prepare($query);
        $st->execute([$resume_id,$desired_day->mon,$desired_day->tue,$desired_day->wed,$desired_day->thu,$desired_day->fri,$desired_day->sat,$desired_day->sun]);

        //희망 지역 TODO: 중복처리
        $query = "INSERT INTO Region (resume_id,desired_region) VALUES (?,?)";
        $st = $pdo->prepare($query);
        foreach ($desired_regions as $desired_region){
            $st->execute([$resume_id,$desired_region->desired_region]);
        }

        //가진 재능
        foreach ($talent_have as $talent){
            $talent_cat_id = getCategoryId($talent->category);
            //재능 상세 분류 DetailedHave에 저장
            foreach ($talent->detailed_talent as $detail ){
                $detail_id = getDetailedCategoryId($detail->detailed,$talent_cat_id);
                $query = "INSERT INTO DetailedHave (resume_id,detailed_cat_id,talent_cat_id) VALUES (?,?,?)";
                $st = $pdo->prepare($query);
                $st->execute([$resume_id,$detail_id,$talent_cat_id]);
            }
            //대분류 category는 중복해서 쓰지 못함

            $query = "INSERT INTO TalentHave (resume_id,talentCategory,introduction,academic_bg,career,certificate,curriculum) VALUES (?,?,?,?,?,?,?)";
            $st = $pdo->prepare($query);
            $st->execute([$resume_id,$talent_cat_id,$talent->introduction,$talent->academic_bg,$talent->career,$talent->certificate,$talent->curriculum]);
        }

        //원하는 재능
        foreach ($talent_want as $talent){
            $talent_cat_id = getCategoryId($talent->category);
            //재능 상세 분류 DetailedHave에 저장
            foreach ($talent->detailed_talent as $detail ){
                $detail_id = getDetailedCategoryId($detail->detailed,$talent_cat_id);
                $query = "INSERT INTO DetailedWant (resume_id,detailed_cat_id,talent_cat_id) VALUES (?,?,?)";
                $st = $pdo->prepare($query);
                $st->execute([$resume_id,$detail_id,$talent_cat_id]);
            }
            //대분류 category는 중복해서 쓰지 못함
            $query = "INSERT INTO TalentWant (resume_id,talent_cat_id,desired_info) VALUES (?,?,?)";
            $st = $pdo->prepare($query);
            $st->execute([$resume_id,$talent_cat_id,$talent->desired_info]);
        }
        $pdo->commit();
    }catch (Exception $e){
        echo $e."rollbakc됨";
        $pdo->rollback();
    }
    $st = null;
    $pdo = null;

}


function updateResume($resume_id,$user_id,$title,$introduction,$talent_images,$isOnLine,$desired_day,$desired_regions,
                      $talent_have,$talent_want)
{
    $pdo = pdoSqlConnect();
    try {
        $pdo->beginTransaction();
        //교환서 기본 사항 수정
        $query = "UPDATE TalentResume SET user_id = ?,title = ?, introduction = ?, isOnLine = ? WHERE resume_id = ?";
        $st = $pdo->prepare($query);
        $st->execute([$user_id,$title,$introduction,$isOnLine,$resume_id]);

        //여기서부터 해야함
        //TODO:교환서 이미지는 고민해보자
        $query = "INSERT INTO TalentImage (resume_id,talent_image) VALUES (?,?)";
        $st = $pdo->prepare($query);
        foreach ($talent_images as $talent_image){
            $st->execute([$resume_id,$talent_image->talent_image]);
        }

        //희망 요일 수정
        $query = "UPDATE DesiredDay SET mon=?, tue=?, wed=?, thu=?, fri=?, sat=?, sun=? WHERE resume_id = ?";
        $st = $pdo->prepare($query);
        $st->execute([$desired_day->mon,$desired_day->tue,$desired_day->wed,$desired_day->thu,$desired_day->fri,$desired_day->sat,$desired_day->sun,$resume_id]);

        //TODO:희망 수정도 고민해보자
        $query = "INSERT INTO Region (resume_id,desired_region) VALUES (?,?)";
        $st = $pdo->prepare($query);
        foreach ($desired_regions as $desired_region){
            $st->execute([$resume_id,$desired_region->desired_region]);
        }

        //가진 재능
        foreach ($talent_have as $talent){
            $talent_cat_id = getCategoryId($talent->category);
            //재능 상세 분류 DetailedHave에 저장
            foreach ($talent->detailed_talent as $detail ){
                $detail_id = getDetailedCategoryId($detail->detailed,$talent_cat_id);
                $query = "INSERT INTO DetailedHave (resume_id,detailed_cat_id,talent_cat_id) VALUES (?,?,?)";
                $st = $pdo->prepare($query);
                $st->execute([$resume_id,$detail_id,$talent_cat_id]);
            }
            //대분류 category는 중복해서 쓰지 못함

            $query = "INSERT INTO TalentHave (resume_id,talentCategory,introduction,academic_bg,career,certificate,curriculum) VALUES (?,?,?,?,?,?,?)";
            $st = $pdo->prepare($query);
            $st->execute([$resume_id,$talent_cat_id,$talent->introduction,$talent->academic_bg,$talent->career,$talent->certificate,$talent->curriculum]);
        }

        //원하는 재능
        foreach ($talent_want as $talent){
            $talent_cat_id = getCategoryId($talent->category);
            //재능 상세 분류 DetailedHave에 저장
            foreach ($talent->detailed_talent as $detail ){
                $detail_id = getDetailedCategoryId($detail->detailed,$talent_cat_id);
                $query = "INSERT INTO DetailedWant (resume_id,detailed_cat_id,talent_cat_id) VALUES (?,?,?)";
                $st = $pdo->prepare($query);
                $st->execute([$resume_id,$detail_id,$talent_cat_id]);
            }
            //대분류 category는 중복해서 쓰지 못함
            $query = "INSERT INTO TalentWant (resume_id,talent_cat_id,desired_info) VALUES (?,?,?)";
            $st = $pdo->prepare($query);
            $st->execute([$resume_id,$talent_cat_id,$talent->desired_info]);
        }
        $pdo->commit();
    }catch (Exception $e){
        echo $e."rollbakc됨";
        $pdo->rollback();
    }
    $st = null;
    $pdo = null;

}

function getResumeData($resume_id){
    $pdo = pdoSqlConnect();

    //이력서 기본정보 불러오기
    $query = "SELECT user_id, title, introduction, isOnLine FROM TalentResume WHERE resume_id = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll()[0];

    //이미지 경로 불러오기
    $query = "SELECT talent_image FROM TalentImage WHERE resume_id = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res["talent_images"]= $st->fetchAll();

    //요일 불러오기
    $query = "SELECT mon, tue, wed,thu, fri,sat,sun FROM DesiredDay WHERE resume_id = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res["desired_day"]= $st->fetchAll();

    //지역 불러오기
    $query = "SELECT desired_region FROM Region WHERE resume_id = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res["desired_regions"]= $st->fetchAll();

    $query = "SELECT talentCategory,introduction,academic_bg,career,certificate,curriculum FROM TalentHave WHERE resume_id = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res["talent_have"]= $st->fetchAll();

    foreach($res["talent_have"] as &$talent){
        $talent["category"]=getCategoryName($talent["talentCategory"]);
        $query = "SELECT cat_name as detailed FROM DetailedHave NATURAL JOIN DetailedCat WHERE resume_id = ? AND talent_cat_id = ?";
        $st = $pdo->prepare($query);
        $st->execute([$resume_id,$talent["talentCategory"]]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $talent["detailed_talent"] = $st->fetchAll();
        unset($talent["talentCategory"]);
    }

    $query = "SELECT talent_cat_id as talentCategory,desired_info FROM TalentWant WHERE resume_id = ?;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res["talent_want"]= $st->fetchAll();
    foreach($res["talent_want"] as &$talent){
        $talent["category"]=getCategoryName($talent["talentCategory"]);
        $query = "SELECT cat_name as detailed FROM DetailedWant NATURAL JOIN DetailedCat WHERE resume_id = ? AND talent_cat_id = ?";
        $st = $pdo->prepare($query);
        $st->execute([$resume_id,$talent["talentCategory"]]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $talent["detailed_talent"] = $st->fetchAll();
        unset($talent["talentCategory"]);
    }

    $st = null;
    $pdo = null;

    return $res;
}

function deleteResume($resume_id){
    $pdo = pdoSqlConnect();
    $query = "UPDATE TalentResume set isDeleted = 1 WHERE resume_id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);

    $st = null;
    $pdo = null;
}

function createReview($reviewer_id,$resume_id,$content,$rate){
    $pdo = pdoSqlConnect();
    //TODO: exchange기록이 있는 사람만, 여러번 제한, 자기 자신 불가
    $query = "INSERT INTO Review (reviewer_id,resume_id,content,rate) VALUES (?,?,?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$reviewer_id,$resume_id,$content,$rate]);

    $st = null;
    $pdo = null;

}

function createExchangeReq($sender_id,$resume_id){
    $pdo = pdoSqlConnect();
    //TODO: 여러번 신청 제한, sender != reviewer
    $query = "INSERT INTO ExchangeRequest (sender_id,resume_id) VALUES (?,?);";

    $st = $pdo->prepare($query);
    $st->execute([$sender_id,$resume_id]);

    $st = null;
    $pdo = null;
}


function getCategoryId($category){
    $pdo = pdoSqlConnect();
    $query = "SELECT talent_cat_id FROM TalentCat WHERE cat_name = ? ;";

    $st = $pdo->prepare($query);
    $st->execute([$category]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]["talent_cat_id"];
}

function getCategoryName($category_id){
    $pdo = pdoSqlConnect();
    $query = "SELECT cat_name FROM TalentCat WHERE talent_cat_id = ? ;";

    $st = $pdo->prepare($query);
    $st->execute([$category_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    $st = null;
    $pdo = null;
    return $res[0]["cat_name"];
}

function getDetailedCategoryId($detailed,$category_id){
    $pdo = pdoSqlConnect();
    $query = "SELECT detailed_cat_id FROM DetailedCat WHERE cat_name = ? and talentCategory_id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$detailed,$category_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]["detailed_cat_id"];
}



