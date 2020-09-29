<?php

function createUser($name,$email,$nick,$profileImgFile,$phone
                ,$region,$birth,$SNS,$sex,$snsToken,$FCMToken)
{
    try {
        $pdo = pdoSqlConnect();
        $pdo->beginTransaction();
        $query = "INSERT INTO User (user_name,user_email,nick_name,phone
,region, birth, SNS, sex, snsToken,FCMToken) VALUES (?,?,?,?,?,?,?,?,?,?);";
        $st = $pdo->prepare($query);
        $st->execute([$name, $email, $nick, $phone
            , $region, $birth, $SNS, $sex, $snsToken, $FCMToken]);
        $user_id = $pdo->lastInsertId();

        switch ($profileImgFile{0}){
            case '/':
                $extension = 'jpg';
                break;
            case 'i':
                $extension = 'png';
                break;
            default:
                throw new Exception("wrong extension");
        }
        $binary=base64_decode($profileImgFile);
        $name = round(microtime(true) * 1000) . '.' . $extension;
        // $filedest = UPLOAD_PATH . $name;
        //move_uploaded_file($file, $filedest);
        $file = fopen(PROFILE_UPLOAD_PATH . $name,'wb');
        fwrite($file,$binary);
        fclose($file);
        //$url = $server_ip = gethostbyname(gethostname());
        $query = "UPDATE User SET profile_img = ? WHERE user_id = ?";
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

}

function getUserInfo($user_id){
    $pdo = pdoSqlConnect();
    $query = "SELECT user_name,user_email,nick_name,profile_img,phone
,region, birth, sex FROM User WHERE user_id= ? ;";

    $st = $pdo->prepare($query);
    $st->execute([$user_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    foreach($res as &$image){
        print_r(gethostname());
        $absurl = 'http://' .gethostbyname(gethostname()). PROFILE_RETRIVE_PATH . $image['profile_img'];
        $image['profile_img'] = $absurl;
    }
    $st = null;
    $pdo = null;
    return $res[0];
}

function updateUser($nick,$profileImg,$phone
    ,$region,$user_id){
    $pdo = pdoSqlConnect();
    //user 확인 필요, img update 처리
    $query = "SELECT profile_img FROM User WHERE user_id = ?";
    $st = $pdo->prepare($query);
    $st->execute([$user_id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    if($res[0]["profile_img"] != $profileImg){
        echo "profile img 바뀜";
        if($res[0]["profile_img"] != null) {
            unlink(PROFILE_UPLOAD_PATH . $res[0]["profile_img"]);
        }
        if($profileImg != null) {
            switch ($profileImg{0}) {
                case '/':
                    $extension = 'jpg';
                    break;
                case 'i':
                    $extension = 'png';
                    break;
                default:
                    throw new Exception("wrong extension");
            }
            $binary = base64_decode($profileImg);
            $name = round(microtime(true) * 1000) . '.' . $extension;
            // $filedest = UPLOAD_PATH . $name;
            //move_uploaded_file($file, $filedest);
            $file = fopen(PROFILE_UPLOAD_PATH . $name, 'wb');
            fwrite($file, $binary);
            fclose($file);
            $profileImg = $name;
        }
    }

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


//SAMPLE
function getAllFiles()
{
    $pdo = pdoSqlConnect();
    $st=$pdo->prepare("SELECT id, description, url FROM TEST ORDER BY id DESC");
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();
    foreach($res as &$image){
        $absurl = 'http://' . gethostbyname(gethostname()) . PROFILE_UPLOAD_PATH . $image['url'];
        $image['url'] = $absurl;
    }

    return $res;
}