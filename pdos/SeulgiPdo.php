<?php
//슬기 교환서(재능 교환서) pdos
function createResume($user_id,$title,$introduction,$talent_images,$online_flag,$desired_day,$desired_regions,
                    $talent_have,$talent_want,$wish)
{
    $pdo = pdoSqlConnect();
    try {
        $pdo->beginTransaction();
      
        //재능 교환서 기본 정보들
        $query = "INSERT INTO TalentResume (user_id,title, introduction, online_flag,wish) VALUES (?,?,?,?,?);";
        $st = $pdo->prepare($query);
        $st->execute([$user_id,$title,$introduction,$online_flag,$wish]);
        $resume_id = $pdo->lastInsertId();
      
        //재능 관련 이미지 받기 base64인코딩 형태->url
        $query = "INSERT INTO TalentImage (resume_id,talent_image) VALUES (?,?)";
        $st = $pdo->prepare($query);
        /*
        foreach ($talent_images as $talent_image){
            switch ($talent_image->talent_image{0}){
                case '/':
                    $extension = 'jpg';
                    break;
                case 'i':
                    $extension = 'png';
                    break;
                default:
                    throw new Exception("wrong extension");
            }
            $binary=base64_decode($talent_image->talent_image);
            $name = round(microtime(true) * 1000) . '.' . $extension;
            $file = fopen(RESUME_UPLOAD_PATH . $name,'wb');
            fwrite($file,$binary);
            fclose($file);
            //$url = $server_ip = gethostbyname(gethostname());
            $st->execute([$resume_id,$name]);
        }
        */
      
        foreach ($talent_images as $talent_image){
            $st->execute([$resume_id,$talent_image->talent_image]);
        }

        //희망 요일 저장
        $query = "INSERT INTO DesiredDay (resume_id, mon, tue, wed, thu, fri, sat, sun) VALUES (?,?,?,?,?,?,?,?)";
        $st = $pdo->prepare($query);
        $st->execute([$resume_id,$desired_day->mon,$desired_day->tue,$desired_day->wed,$desired_day->thu,$desired_day->fri,$desired_day->sat,$desired_day->sun]);

        //희망 지역
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

            $query = "INSERT INTO TalentHave (resume_id,talent_cat_id,introduction,academic_bg,career,certificate,curriculum) VALUES (?,?,?,?,?,?,?)";
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


function updateResume($resume_id,$user_id,$title,$introduction,$talent_images,$online_flag,$desired_day,$desired_regions,
                      $talent_have,$talent_want, $wish)
{
    $pdo = pdoSqlConnect();
    try {
        $pdo->beginTransaction();
        //교환서 기본 사항 수정

        //record가 여러개인 테이블들에는 기존 데이터를 삭제하고 새로운 데이터를 넣어줌
        $query = "UPDATE TalentResume SET user_id = ?,title = ?, introduction = ?, online_flag = ?, wish = ? WHERE resume_id = ? and delete_flag = 0";
        $st = $pdo->prepare($query);
        $st->execute([$user_id,$title,$introduction,$online_flag,$wish,$resume_id]);

        $TABLES = ["TalentHave", "TalentWant","Region","DetailedWant","DetailedHave","TalentImage"];
        foreach ($TABLES as $TABLE){
            $query = "UPDATE ".$TABLE." set delete_flag = 1 WHERE resume_id = ? and delete_flag = 0 ;";
            $st = $pdo->prepare($query);
            $st->execute([$resume_id]);
        }

        //이미지 추가
        $query = "INSERT INTO TalentImage (resume_id,talent_image) VALUES (?,?)";
        $st = $pdo->prepare($query);
        foreach ($talent_images as $talent_image){
            $st->execute([$resume_id,$talent_image->talent_image]);
        }

        //희망 요일 수정
        $query = "UPDATE DesiredDay SET mon=?, tue=?, wed=?, thu=?, fri=?, sat=?, sun=? WHERE resume_id = ? and delete_flag = 0";
        $st = $pdo->prepare($query);
        $st->execute([$desired_day->mon,$desired_day->tue,$desired_day->wed,$desired_day->thu,$desired_day->fri,$desired_day->sat,$desired_day->sun,$resume_id]);


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

            $query = "INSERT INTO TalentHave (resume_id,talent_cat_id,introduction,academic_bg,career,certificate,curriculum) VALUES (?,?,?,?,?,?,?)";
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
        return false;
    }
    $st = null;
    $pdo = null;
    return true;

}

function getBasicResumeData($resume_id,$user_id){
    $pdo = pdoSqlConnect();

    //조회수 상승
    $query = "UPDATE TalentResume SET hit = hit +1 WHERE resume_id = ?";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);

    //제목, 아이디 간단한 소개, online여부
    $query = "SELECT user_id, title, introduction FROM TalentResume WHERE resume_id = ? and delete_flag = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll()[0];
  
    //이미지
    $query = "SELECT talent_image FROM TalentImage WHERE resume_id = ? and delete_flag = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $temp = $st->fetchAll();
    /*
    foreach($temp as &$image){
        $absurl = 'http://' .gethostbyname(gethostname()). RESUME_RETRIVE_PATH . $image['talent_image'];
        $image['talent_image'] = $absurl;
    }
    */
    $res["talent_images"]= $temp;

    if(isset($user_id)) {
        $resume["isScrapped"] = checkIfExist("ResumeScrap",["user_id","resume_id"],[$user_id, $resume_id]);
    }else{
        $resume["isScrapped"] = false;
    }

    $st = null;
    $pdo = null;

    return $res;
}

//보유 재능 탭 정보 가져오기
function getTalentHave($resume_id){
    $pdo = pdoSqlConnect();
    $query = "SELECT talent_cat_id,introduction,academic_bg,career,certificate,curriculum FROM TalentHave WHERE resume_id = ? and delete_flag = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res["talent_have"]= $st->fetchAll();

    foreach($res["talent_have"] as &$talent){
        $talent["category"]=getCategoryName($talent["talent_cat_id"]);
        $query = "SELECT cat_name as detailed FROM DetailedHave NATURAL JOIN DetailedCat WHERE resume_id = ? AND talent_cat_id = ? and delete_flag = 0";
        $st = $pdo->prepare($query);
        $st->execute([$resume_id,$talent["talent_cat_id"]]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $talent["detailed_talent"] = $st->fetchAll();
        unset($talent["talent_cat_id"]);
    }

    $st = null;
    $pdo = null;

    return $res;
}

//원하는 재능 탭 정보 가져오기
function getTalentWant($resume_id){
    $pdo = pdoSqlConnect();
    $query = "SELECT talent_cat_id ,desired_info FROM TalentWant WHERE resume_id = ? and delete_flag = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res["talent_want"]= $st->fetchAll();
    foreach($res["talent_want"] as &$talent){
        $talent["category"]=getCategoryName($talent["talent_cat_id"]);
        $query = "SELECT cat_name as detailed FROM DetailedWant NATURAL JOIN DetailedCat WHERE resume_id = ? AND talent_cat_id = ? and delete_flag = 0";
        $st = $pdo->prepare($query);
        $st->execute([$resume_id,$talent["talent_cat_id"]]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $talent["detailed_talent"] = $st->fetchAll();
        unset($talent["talent_cat_id"]);
    }

    $st = null;
    $pdo = null;

    return $res;
}

//희망사항 탭 정보 가져오기
function getDesiredCondition($resume_id){

    $pdo = pdoSqlConnect();
    //희망 사항, 온라인 여부 불러오기
    $query = "SELECT online_flag, wish FROM TalentResume WHERE resume_id = ? and delete_flag = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll()[0];
  //요일 불러오기
    $query = "SELECT mon, tue, wed,thu, fri,sat,sun FROM DesiredDay WHERE resume_id = ? and delete_flag = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res["desired_day"]= $st->fetchAll();

    //지역 불러오기
    $query = "SELECT desired_region FROM Region WHERE resume_id = ? and delete_flag = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res["desired_regions"]= $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

//교환서 후기탭 정보 불러오기
function getResumeReviews($resume_id){
    $pdo = pdoSqlConnect();
    $query = "SELECT rate FROM TalentResume WHERE resume_id = ? and delete_flag = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll()[0];

    $res["reviews"] = getReviews($resume_id);

    $st = null;
    $pdo = null;

    return $res;
}

//이력서 전체 정보 불러오기
function getResumeData($resume_id){
    $pdo = pdoSqlConnect();

    //이력서 기본정보 불러오기
    $query = "SELECT user_id, title, introduction, online_flag, wish,upload_flag FROM TalentResume WHERE resume_id = ? and delete_flag = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll()[0];

    //이미지 경로 불러오기
    $query = "SELECT talent_image FROM TalentImage WHERE resume_id = ? and delete_flag = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $temp = $st->fetchAll();
//    foreach($temp as &$image){
//        $absurl = 'http://' .gethostbyname(gethostname()). RESUME_RETRIVE_PATH . $image['talent_image'];
//        $image['talent_image'] = $absurl;
//    }
    $res["talent_images"]= $temp;

    //요일 불러오기
    $query = "SELECT mon, tue, wed,thu, fri,sat,sun FROM DesiredDay WHERE resume_id = ? and delete_flag = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res["desired_day"]= $st->fetchAll();

    //지역 불러오기
    $query = "SELECT desired_region FROM Region WHERE resume_id = ? and delete_flag = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res["desired_regions"]= $st->fetchAll();

    $query = "SELECT talent_cat_id,introduction,academic_bg,career,certificate,curriculum FROM TalentHave WHERE resume_id = ? and delete_flag = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res["talent_have"]= $st->fetchAll();

    foreach($res["talent_have"] as &$talent){
        $talent["category"]=getCategoryName($talent["talent_cat_id"]);
        $query = "SELECT cat_name as detailed FROM DetailedHave NATURAL JOIN DetailedCat WHERE resume_id = ? AND talent_cat_id = ? and delete_flag = 0";
        $st = $pdo->prepare($query);
        $st->execute([$resume_id,$talent["talent_cat_id"]]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $talent["detailed_talent"] = $st->fetchAll();
        unset($talent["talent_cat_id"]);
    }

    $query = "SELECT talent_cat_id ,desired_info FROM TalentWant WHERE resume_id = ? and delete_flag = 0;";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res["talent_want"]= $st->fetchAll();
    foreach($res["talent_want"] as &$talent){
        $talent["category"]=getCategoryName($talent["talent_cat_id"]);
        $query = "SELECT cat_name as detailed FROM DetailedWant NATURAL JOIN DetailedCat WHERE resume_id = ? AND talent_cat_id = ? and delete_flag = 0";
        $st = $pdo->prepare($query);
        $st->execute([$resume_id,$talent["talent_cat_id"]]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $talent["detailed_talent"] = $st->fetchAll();
        unset($talent["talent_cat_id"]);
    }

    $st = null;
    $pdo = null;

    return $res;
}

//이력서 삭제
function deleteResume($resume_id){
    $pdo = pdoSqlConnect();
    try{
        $pdo->beginTransaction();
        $TABLES = ["TalentHave", "TalentWant", "TalentResume", "TalentImage","Region","DetailedWant", "DesiredDay","DetailedHave"];
        foreach ($TABLES as $TABLE){
            $query = "UPDATE ".$TABLE." set delete_flag = 1 WHERE resume_id = ? and delete_flag = 0 ;";
            $st = $pdo->prepare($query);
            $st->execute([$resume_id]);
        }
        $pdo->commit();
    }catch (Exception $e){
        echo $e."rollbakc됨";
        $pdo->rollback();
    }

    $st = null;
    $pdo = null;
}

//category 이름으로 id가져오기
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

//category id로 이름 가져오기
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

//상세category 이름으로 id가져오기
function getDetailedCategoryId($detailed,$category_id){
    $pdo = pdoSqlConnect();
    $query = "SELECT detailed_cat_id FROM DetailedCat WHERE cat_name = ? and talent_cat_id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$detailed,$category_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]["detailed_cat_id"];
}

//상세category id로 이름 가져오기
function getDetailedCategoryIdWithName($detailed){
    $pdo = pdoSqlConnect();
    $query = "SELECT detailed_cat_id FROM DetailedCat WHERE cat_name = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$detailed]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]["detailed_cat_id"];
}

//교환서 스크랩
function scrapResume($user_id, $resume_id){
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO ResumeScrap (user_id,resume_id) VALUES (?,?)";

    $st = $pdo->prepare($query);
    $st->execute([$user_id, $resume_id]);

    $st = null;
    $pdo = null;
}

//스크랩 해제
function deleteScrapResume($user_id, $resume_id){
    $pdo = pdoSqlConnect();
    $query = "DELETE FROM ResumeScrap WHERE user_id = ? and resume_id = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$user_id, $resume_id]);

    $st = null;
    $pdo = null;
}

//검색 쿼리, advanced filter(원하는, 보유한 재능 다중 선택, 지역, 요일, 온라인 여부 및 정렬 순 옵션 선택 )
function getResumeList($user_id,$filter,$talentWant,$talentHave,$online_flag,$region,$desired_day,$lastIdx,$detailedWant,$detailedHave){
    $pdo = pdoSqlConnect();
    $query = "select TR.resume_id, title, TR.created_time, hit ,rate from TalentResume as TR Where delete_flag=0 and upload_flag = 1 and ";

    if(isset($online_flag)){
        $query .= "TR.online_flag = ".$online_flag." and " ;
    }

    $i = 0;
    $len = count($talentWant);
    if(isset($talentWant)){
        $query .= "exists(select * from TalentWant as TW where TR.resume_id = TW.resume_id and (";
        foreach ($talentWant as $talent) {
            $cat_id = getCategoryId($talent);
            if($i == $len-1) {
                $query .= "TW.talent_cat_id = " . $cat_id;
            }else {
                $query .= "TW.talent_cat_id = " . $cat_id . " or ";
            }
            $i++;
        }
        $query .= ")) and ";

    }

    $i = 0;
    $len = count($talentHave);
    if(isset($talentHave)){
        $query .= "exists(select * from TalentHave as TH where TR.resume_id = TH.resume_id and (";
        foreach ($talentHave as $talent) {
            $cat_id = getCategoryId($talent);
            if($i == $len-1) {
                $query .= "TH.talent_cat_id = " . $cat_id;
            }else {
                $query .= "TH.talent_cat_id = " . $cat_id . " or ";
            }
            $i++;
        }
        $query .= ")) and ";
    }

    //상세 재능
    $i = 0;
    $len = count($detailedWant);
    if(isset($detailedWant)){
        $query .= "exists(select * from DetailedWant as TW where TR.resume_id = TW.resume_id and (";
        foreach ($detailedWant as $talent) {
            $cat_id = getDetailedCategoryIdWithName($talent);
            if($i == $len-1) {
                $query .= "TW.detailed_cat_id = " . $cat_id;
            }else {
                $query .= "TW.detailed_cat_id = " . $cat_id . " or ";
            }
            $i++;
        }
        $query .= ")) and ";

    }

    $i = 0;
    $len = count($detailedHave);
    if(isset($detailedHave)){
        $query .= "exists(select * from DetailedHave as TW where TR.resume_id = TW.resume_id and (";
        foreach ($detailedHave as $talent) {
            $cat_id =  getDetailedCategoryIdWithName($talent);
            if($i == $len-1) {
                $query .= "TW.detailed_cat_id = " . $cat_id;
            }else {
                $query .= "TW.detailed_cat_id = " . $cat_id . " or ";
            }
            $i++;
        }
        $query .= ")) and ";

    }

    $i = 0;
    $len = count($region);
    if(isset($region)){
        $query .= "exists(select * from Region as R where TR.resume_id = R.resume_id and (";
        foreach ($region as $reg) {
            if($i == $len-1) {
                $query .= "R.desired_region = '" . $reg."'";
            }else {
                $query .= "R.desired_region = '" . $reg . "' or ";
            }
            $i++;
        }
        $query .= ")) and ";
    }

    $i = 0;
    $len = count($desired_day);
    if(isset($desired_day)){
        $query .= "exists(select * from DesiredDay as DD where TR.resume_id = DD.resume_id and (";
        foreach ($desired_day as $day) {
            if($i == $len-1) {
                $query .= "DD.".$day." = 1 ";
            }else {
                $query .= "DD.".$day." = 1 and ";
            }
            $i++;
        }
        $query .= ")) and ";
    }


    //마지막 and 제거
    $query = substr($query,0,-4);

    switch ($filter) {
        //최신순
        case 0:
            $query .= " order by updated_time DESC limit ".$lastIdx.",10;";
            break;
            //후기순
        case 1:
            $query .= " order by rate limit ".$lastIdx.",10;";
            break;
            //조회순
        case 2:
            $query .= " order by hit limit ".$lastIdx.",10;";
            break;
        default:
            $query .= " order by updated_time DESC limit ".$lastIdx.",10;";
    }
    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    foreach ($res as &$resume){
        //가진 재능
        $query = "select distinct cat_name as talent from TalentHave TH join TalentCat TC on TH.talent_cat_id = TC.talent_cat_id WHERE resume_id = ? and delete_flag = 0";
        $st = $pdo->prepare($query);
        $st->execute([$resume["resume_id"]]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $resume["talentHave"] = $st->fetchAll();
        foreach($resume["talentHave"] as &$talent){
            $talent_cat_id =  getCategoryId($talent["talent"]);
            $query = "SELECT cat_name as detailed FROM DetailedHave NATURAL JOIN DetailedCat WHERE resume_id = ? AND talent_cat_id = ? and delete_flag = 0";
            $st = $pdo->prepare($query);
            $st->execute([$resume["resume_id"], $talent_cat_id ]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $talent["detailed_talent"] = $st->fetchAll();
        }
        //원하는 재능
        $query = " select distinct cat_name as talent from TalentWant join TalentCat on TalentWant.talent_cat_id = TalentCat.talent_cat_id WHERE resume_id = ? and delete_flag = 0";
        $st = $pdo->prepare($query);
        $st->execute([$resume["resume_id"]]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $resume["talentWant"] = $st->fetchAll();
        foreach($resume["talentWant"] as &$talent){
            $talent_cat_id = getCategoryId($talent["talent"]);
            $query = "SELECT cat_name as detailed FROM DetailedWant NATURAL JOIN DetailedCat WHERE resume_id = ? AND talent_cat_id = ? and delete_flag = 0";
            $st = $pdo->prepare($query);
            $st->execute([$resume["resume_id"],$talent_cat_id ]);
            $st->setFetchMode(PDO::FETCH_ASSOC);
            $talent["detailed_talent"] = $st->fetchAll();
        }


        //이미지
        $query = "select distinct talent_image from TalentImage where resume_id = ?";
        $st = $pdo->prepare($query);
        $st->execute([$resume["resume_id"]]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $resume["talentImage"] = $st->fetchAll()[0];

        if(isset($user_id)) {
            $resume["isScrapped"] = checkIfExist("ResumeScrap",["user_id","resume_id"],[$user_id, $resume["resume_id"]]);
        }else{
            $resume["isScrapped"] = false;
        }

        $query = "select count(*) as cnt from ResumeScrap where resume_id = ? ;";
        $st = $pdo->prepare($query);
        $st->execute([$resume["resume_id"]]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $resume["scrapCnt"] = $st->fetchAll()[0]["cnt"];
    }


    $st = null;
    $pdo = null;

    return $res;
}

//임시저장된 교환서를 업로드 여부
function updateUploadFlag($resume_id,$flag){
    $pdo = pdoSqlConnect();

    $query = "UPDATE TalentResume SET upload_flag = ? WHERE resume_id = ?";
    $st = $pdo->prepare($query);
    $st->execute([$flag,$resume_id]);

    $st = null;
    $pdo = null;
}

//유저가 해당 교환서 수정에 권한이 있는지
function checkResumePermission($resume_id,$user_id){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM TalentResume WHERE resume_id = ? and user_id = ? and delete_flag =0) As exist; ";
    $st = $pdo->prepare($query);
    $st->execute([$resume_id,$user_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return intval($res[0]["exist"]);
}



