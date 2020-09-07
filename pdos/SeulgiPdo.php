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
            $st->execute([$resume_id,$talent_image->image]);
        }

        //희망 요일 저장  TODO: 중복처리
        $query = "INSERT INTO DesiredDay (resume_id, mon, tue, wed, thu, fri, sat, sun) VALUES (?,?,?,?,?,?,?,?)";
        $st = $pdo->prepare($query);
        $st->execute([$resume_id,$desired_day->mon,$desired_day->tue,$desired_day->wed,$desired_day->thu,$desired_day->fri,$desired_day->sat,$desired_day->sun]);

        //희망 지역 TODO: 중복처리
        $query = "INSERT INTO Region (resume_id,desired_region) VALUES (?,?)";
        $st = $pdo->prepare($query);
        foreach ($desired_regions as $desired_region){
            $st->execute([$resume_id,$desired_region->region]);
        }

        //가진 재능
        foreach ($talent_have as $talent){
            $talent_cat_id = getCategoryId($talent->talent->category);
            //재능 상세 분류 DetailedHave에 저장
            foreach ($talent->talent->detailed_talent as $detail ){
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
            $talent_cat_id = getCategoryId($talent->talent->category);
            //재능 상세 분류 DetailedHave에 저장
            foreach ($talent->talent->detailed_talent as $detail ){
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



