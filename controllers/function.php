<?php

use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Google\Auth\ApplicationDefaultCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;


function getSQLErrorException($errorLogs, $e, $req)
{
    $res = (Object)Array();
    http_response_code(500);
    $res->code = 500;
    $res->message = "SQL Exception -> " . $e->getTraceAsString();
    echo json_encode($res);
    addErrorLogs($errorLogs, $res, $req);
}

function isValidHeader($jwt, $key)
{
    try {
        $data = getDataByJWToken($jwt, $key);
        //로그인 함수 직접 구현 요함
        return isValidUser($data->id);
    } catch (\Exception $e) {
        return false;
    }
}
/*
function checkImageExt($image){
    if($image != null) {
        switch ($image{0}) {
            case '/':
                $extension = 'jpg';
                break;
            case 'i':
                $extension = 'png';
                break;
            default:
                return false;
        }
    }
    return true;
}
*/

function sendFcm($fcmToken, $data, $key, $deviceType)
{
    $url = 'https://fcm.googleapis.com/fcm/send';

    $headers = array(
        'Authorization: key=' . $key,
        'Content-Type: application/json'
    );

    $fields['data'] = $data;

    if ($deviceType == 'IOS') {
        $notification['title'] = $data['title'];
        $notification['body'] = $data['body'];
        $notification['sound'] = 'default';
        $fields['notification'] = $notification;
    }

    $fields['to'] = $fcmToken;
    $fields['content_available'] = true;
    $fields['priority'] = "high";

    $fields = json_encode($fields, JSON_NUMERIC_CHECK);

//    echo $fields;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);

    $result = curl_exec($ch);
    if ($result === FALSE) {
        //die('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);
    return $result;
}

function getTodayByTimeStamp()
{
    return date("Y-m-d H:i:s");
}

function getJWToken($id,$sns_id,$secretKey)
{
    $data = array(
        'date' => (string)getTodayByTimeStamp(),
        'id' => (string)$id,
        'sns_id' => (string)$sns_id
    );

//    echo json_encode($data);

    return $jwt = JWT::encode($data, $secretKey);

//    echo "encoded jwt: " . $jwt . "n";
//    $decoded = JWT::decode($jwt, $secretKey, array('HS256'))
//    print_r($decoded);
}

function getDataByJWToken($jwt, $secretKey)
{
    try{
        $decoded = JWT::decode($jwt, $secretKey, array('HS256'));
    }catch(\Exception $e){
        return "";
    }

//    print_r($decoded);
    return $decoded;

}


function checkAndroidBillingReceipt($credentialsPath, $token, $pid)
{

    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $credentialsPath);
    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->addScope("https://www.googleapis.com/auth/androidpublisher");
    $client->setSubject("USER_ID.iam.gserviceaccount.com");


    $service = new Google_Service_AndroidPublisher($client);
    $optParams = array('token' => $token);

    return $service->purchases_products->get("PACKAGE_NAME", $pid, $token);
}


function addAccessLogs($accessLogs, $body)
{
    if (isset($_SERVER['HTTP_X_ACCESS_TOKEN']))
        $logData["JWT"] = getDataByJWToken($_SERVER['HTTP_X_ACCESS_TOKEN'], JWT_SECRET_KEY);
    $logData["GET"] = $_GET;
    $logData["BODY"] = $body;
    $logData["REQUEST_METHOD"] = $_SERVER["REQUEST_METHOD"];
    $logData["REQUEST_URI"] = $_SERVER["REQUEST_URI"];
//    $logData["SERVER_SOFTWARE"] = $_SERVER["SERVER_SOFTWARE"];
    $logData["REMOTE_ADDR"] = $_SERVER["REMOTE_ADDR"];
    $logData["HTTP_USER_AGENT"] = $_SERVER["HTTP_USER_AGENT"];
    $accessLogs->addInfo(json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

}

function addErrorLogs($errorLogs, $res, $body)
{
    if (isset($_SERVER['HTTP_X_ACCESS_TOKEN']))
        $req["JWT"] = getDataByJWToken($_SERVER['HTTP_X_ACCESS_TOKEN'], JWT_SECRET_KEY);
    $req["GET"] = $_GET;
    $req["BODY"] = $body;
    $req["REQUEST_METHOD"] = $_SERVER["REQUEST_METHOD"];
    $req["REQUEST_URI"] = $_SERVER["REQUEST_URI"];
//    $req["SERVER_SOFTWARE"] = $_SERVER["SERVER_SOFTWARE"];
    $req["REMOTE_ADDR"] = $_SERVER["REMOTE_ADDR"];
    $req["HTTP_USER_AGENT"] = $_SERVER["HTTP_USER_AGENT"];

    $logData["REQUEST"] = $req;
    $logData["RESPONSE"] = $res;

    $errorLogs->addError(json_encode($logData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

//        sendDebugEmail("Error : " . $req["REQUEST_METHOD"] . " " . $req["REQUEST_URI"] , "<pre>" . json_encode($logData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "</pre>");
}


function getLogs($path)
{
    $fp = fopen($path, "r", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$fp) echo "error";

    while (!feof($fp)) {
        $str = fgets($fp, 10000);
        $arr[] = $str;
    }
    for ($i = sizeof($arr) - 1; $i >= 0; $i--) {
        echo $arr[$i] . "<br>";
    }
//        fpassthru($fp);
    fclose($fp);
}

function getFileExtension($file)
{
    $path_parts = pathinfo($file);
    return $path_parts['extension'];
}

function checkIfExist($table, $col_args,$args){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM ".$table." WHERE ";
    foreach($col_args as $col){
        $query .= $col."=? AND ";
    }
    $query = substr($query,0,-4);
    $tables = ["Comment","DetailWant","DetailHave","ExchangeRequest","Post","Region","DesiredDay","Review","TalentHave","TalentWant","User","TalentResume","TalentImage"];
    foreach ($tables as $_table){
        if($_table == $table) {
            $query .= "AND delete_flag = 0 ";
            break;
        }
    }
    $query .=") AS exist;";
    $st = $pdo->prepare($query);
    $st->execute($args);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return intval($res[0]["exist"]);
}

function checkIfIsDeleted($table, $col_args,$args){
    $pdo = pdoSqlConnect();
    $query = "SELECT EXISTS(SELECT * FROM ".$table." WHERE ";
    foreach($col_args as $col){
        $query .= $col."=? AND ";
    }
    $query = substr($query,0,-4);
    //if($table =="")
    $query .="AND delete_flag = 1) AS exist;";
    $st = $pdo->prepare($query);
    //    $st->execute([$param,$param]);
    $st->execute($args);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st=null;$pdo = null;
    return intval($res[0]["exist"]);
}

function successRes($res,$message){
    $res->isSuccess = TRUE;
    $res->code = 100;
    $res->message = $message;
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function failRes($res,$message,$code){
    $res->isSuccess = FALSE;
    $res->code = $code;
    $res->message = $message;
    echo json_encode($res, JSON_NUMERIC_CHECK);
}

function kakaoAuth($access_token){
    $header = "Bearer ".$access_token; // Bearer 다음에 공백 추가
    $getProfileUrl = "https://kapi.kakao.com/v2/user/me";

    $isPost = false;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $getProfileUrl);
    curl_setopt($ch, CURLOPT_POST, $isPost);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $headers = array();
    $headers[] = "Authorization: ".$header;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $profileResponse = curl_exec ($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close ($ch);

  //  var_dump($profileResponse); // Kakao API 서버로 부터 받아온 값

    $profileResponse = json_decode($profileResponse);

    $temp = (Object)Array();
    $temp->userId = $profileResponse->id;
   // $userName = $profileResponse->properties->nickname;
    $temp->userEmail = $profileResponse->kakao_account->email;

    return $temp;
}