<?php

function createResume($userid,$title,$introduction,$talent_images,$isOnLine,$desired_day,$desired_regions,
                    $talent_have,$talent_want)
{
    $pdo = pdoSqlConnect();
    try {
        $pdo->beginTransaction();

        //교환서 기본 사항 저장 TODO: 중복처리
        $query = "INSERT INTO TalentResume (user_id,title, introduction, isOnLine) VALUES (?,?,?,?);";
        $st = $pdo->prepare($query);
        $st->execute([$userid,$title,$introduction,$isOnLine]);
        
        //교환서 이미지 저장 TODO: 갯수 제한, 중복처리
        $query = "INSERT INTO TalentImage (user_id,talent_image) VALUES (?,?)";
        $st = $pdo->prepare($query);
        foreach ($talent_images as $talent_image){
            $st->execute([$userid,$talent_image->image]);
        }
        
        //희망 요일 저장  TODO: 중복처리
        $query = "INSERT INTO DesiredDay (user_id, mon, tue, wed, thu, fri, sat, sun) VALUES (?,?,?,?,?,?,?,?)";
        $st = $pdo->prepare($query);
        $st->execute([$userid,$desired_day->mon,$desired_day->tue,$desired_day-wed,$desired_day->thu,$desired_day->fri,$desired_day->sat,$desired_day->sun]);

        //희망 지역 TODO: 중복처리
        $query = "INSERT INTO Region (user_id,desired_region) VALUES (?,?)";
        $st = $pdo->prepare($query);
        foreach ($desired_regions as $desired_region){
            $st->execute([$userid,$desired_region->region]);
        }

        //가진 재능
        foreach ($talent_have as $talent){
            $talent_cat_id = getCategoryId($talent->category);
            //재능 큰 분류는 TalentHave에 상세 분류는 DetailedHave
            foreach ($talent)
            $query = "INSERT INTO Region (user_id,desired_region) VALUES (?,?)";
            $st = $pdo->prepare($query);
        }


        $pdo->commit();
    }catch (Exception $e){
        $pdo->rollback();
    }
    $st = null;
    $pdo = null;

}

function getCategoryId($detailed,$category){
    $pdo = pdoSqlConnect();
    $query = "SELECT talent_cat_id FROM TalentCat WHERE cat_name = ? ;";

    $st = $pdo->prepare($query);
    $st->execute([$category]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]->talent_cat_id;
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

    return $res[0]->detailed_cat_id;
}



