<?php

function createUser($name,$email,$nick,$profileImgFile, $extension,$phone
                ,$region,$birth,$SNS,$sex,$snsToken,$FCMToken)
{
    try {
        $pdo = pdoSqlConnect();
        $query = "INSERT INTO User (user_name,user_email,nick_name,phone
,region, birth, SNS, sex, snsToken,FCMToken) VALUES (?,?,?,?,?,?,?,?,?,?,?);";
        $st = $pdo->prepare($query);
        $st->execute([$name, $email, $nick, $phone
            , $region, $birth, $SNS, $sex, $snsToken, $FCMToken]);
        $user_id = $pdo->lastInsertId();
        if(!saveProfileImage($profileImgFile, $extension, $user_id)){
            //이미지 업로드에 실패했을때
            throw new Exception();
        }
        
    }catch (Exception $e){
        echo $e."image upload error";
        $pdo->rollback();
        return false;
    }
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

function saveProfileImage($file, $extension, $user_id)
{
    try {
        $pdo = pdoSqlConnect();
        $pdo->beginTransaction();
        $name = round(microtime(true) * 1000) . '.' . $extension;
        $filedest = UPLOAD_PATH . $name;
        move_uploaded_file($file, $filedest);

        $url = $server_ip = gethostbyname(gethostname());
        $query = "INSERT INTO User (profile_img) VALUES (?) WHERE user_id = ?";
        $st = $pdo->prepare($query);
        $st->execute([$name,$user_id]);
        $pdo->commit();
    }catch (Exception $e){
        echo $e."image upload error";
        $pdo->rollback();
        return false;
    }
    $st = null;
    $pdo = null;
    return true;
}
function getAllFiles()
{
    $pdo = pdoSqlConnect();
    $st=$pdo->prepare("SELECT id, description, url FROM TEST ORDER BY id DESC");
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    foreach($res as &$image){
        $absurl = 'http://' . gethostbyname(gethostname()) . UPLOAD_PATH . $image['url'];
        $image['url'] = $absurl;
    }

    return $res;
}