<?php
require './pdos/DatabasePdo.php';
require './pdos/IndexPdo.php';
require './pdos/UserPdo.php';
require './pdos/PostPdo.php';
require './pdos/SeulgiPdo.php';
require './pdos/ReviewPdo.php';
require './pdos/ExchangePdo.php';
require './vendor/autoload.php';

use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

define("PROFILE_UPLOAD_PATH",dirname(__FILE__)."/uploads/profile/");
define("PROFILE_RETRIVE_PATH","/uploads/profile/");
define("RESUME_UPLOAD_PATH",dirname(__FILE__)."/uploads/resume/");
define("RESUME_RETRIVE_PATH","/uploads/resume/");
define("POST_UPLOAD_PATH",dirname(__FILE__)."/uploads/community/");
define("POST_RETRIVE_PATH","/uploads/community/");
define("URL_REGEX","/\bhttps?:\/\/\S+(?:png|jpg|jpeg)\b/");
define("GOOGLE_API_KEY","AAAAEAHqjGo:APA91bEMrNSojahDB6eQkXyJ5xLhA6ZmBd61mKa6AXJoixjM_Y1gD8xyx5RbfkBIgp6dbOR-rbziPH73ke5aW-1c6oY2Pu7QkVGAbSi9tDpV0UOkeNLfqO0fQuNk-4E2YGQYYOM-oE4N");

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//에러출력하게 하는 코드
error_reporting(E_ALL); ini_set("display_errors", 1);

//Main Server API
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    /* ******************   Test   ****************** */
    $r->addRoute('GET', '/', ['IndexController', 'index']);
    $r->addRoute('GET', '/test', ['IndexController', 'test']);
    $r->addRoute('GET', '/test/{testNo}', ['IndexController', 'testDetail']);
    $r->addRoute('POST', '/test', ['IndexController', 'testPost']);
    $r->addRoute('GET', '/jwt', ['MainController', 'validateJwt']);
    $r->addRoute('POST', '/jwt', ['MainController', 'createJwt']);


    /***** User ******/
    $r->addRoute('POST', '/user', ['IndexController', 'createUser']);
    $r->addRoute('PUT', '/user', ['IndexController', 'updateUser']);
    $r->addRoute('DELETE', '/user', ['IndexController', 'deleteUser']);
    $r->addRoute('GET', '/user', ['IndexController', 'getUserInfo']);
    $r->addRoute('GET', '/upload', ['IndexController', 'getImages']);


    /***** Seulgi *****/
    //교환서
    $r->addRoute('POST', '/seulgi/resume', ['SeulgiController', 'createResume']);
    $r->addRoute('PUT', '/seulgi/resume', ['SeulgiController', 'updateResume']);
    $r->addRoute('GET', '/seulgi/resume/{resume-id}', ['SeulgiController', 'getResume']);
    $r->addRoute('DELETE', '/seulgi/resume/{resume-id}', ['SeulgiController', 'deleteResume']);
    $r->addRoute('POST', '/seulgi/resume/upload', ['SeulgiController', 'uploadResume']);
    $r->addRoute('PUT', '/seulgi/resume/upload', ['SeulgiController', 'cancelUpload']);
    $r->addRoute('POST', '/seulgi/scrap', ['SeulgiController', 'scrapResume']);

    //교환서 상세 페이지
    $r->addRoute('GET', '/seulgi/resume-info/basic/{resume-id}', ['SeulgiController', 'getResumeBasic']);
    $r->addRoute('GET', '/seulgi/resume-info/talent-have/{resume-id}', ['SeulgiController', 'getTalentHave']);
    $r->addRoute('GET', '/seulgi/resume-info/talent-want/{resume-id}', ['SeulgiController', 'getTalentWant']);
    $r->addRoute('GET', '/seulgi/resume-info/desired-opt/{resume-id}', ['SeulgiController', 'getDesiredOpt']);
    $r->addRoute('GET', '/seulgi/resume-info/reviews/{resume-id}', ['SeulgiController', 'getResumeReviews']);

    //후기
    $r->addRoute('POST', '/review', ['ReviewController', 'createReview']);
    $r->addRoute('GET', '/review/{review-id}', ['ReviewController', 'getReviews']);
    $r->addRoute('DELETE', '/review/{review-id}', ['ReviewController', 'deleteReview']);
    $r->addRoute('PUT', '/review', ['ReviewController', 'updateReview']);

    //요청
    $r->addRoute('POST', '/exchange-management/exchange', ['ExchangeController', 'createExchangeReq']);
    $r->addRoute('GET', '/exchange-management/received-exchanges/{user-id}', ['ExchangeController', 'getReceivedExchangeReqs']);
    $r->addRoute('GET', '/exchange-management/sended-exchanges/{user-id}', ['ExchangeController', 'getSendedExchangeReqs']);
    $r->addRoute('GET', '/exchange-management/exchanges', ['ExchangeController', 'getExchangedReqs']);
    $r->addRoute('PUT', '/exchange-management/accept-exchange', ['ExchangeController', 'acceptExchangeReq']);
    $r->addRoute('GET', '/exchange-management/exchange-result', ['ExchangeController', 'ExchangeInfo']);
    $r->addRoute('GET', '/exchange-management/exchange/{exchange-id}', ['ExchangeController', 'getExchangeReq']);
    $r->addRoute('DELETE', '/exchange-management/exchange/{exchange-id}', ['ExchangeController', 'deleteExchangeReq']);

    //화면 기능들
    $r->addRoute('GET', '/home/resume-list', ['SeulgiController', 'getResumeList']);
    //TODO: 수정, 삭제(교환 요청은 제외), 조회

    //피드 기능
    $r->addRoute('POST', '/post', ['PostController', 'createPost']);
    $r->addRoute('PUT', '/post', ['PostController', 'updatePost']);
    $r->addRoute('GET', '/post/{post-id}', ['PostController', 'getPost']);
    $r->addRoute('GET', '/post-list', ['PostController', 'getPostList']);
    $r->addRoute('DELETE', '/post/{post-id}', ['PostController', 'deletePost']);
    $r->addRoute('POST', '/post/like', ['PostController', 'likePost']);
    $r->addRoute('POST', '/post/comment', ['PostController', 'createComment']);
    $r->addRoute('PUT', '/post/comment', ['PostController', 'updateComment']);
    $r->addRoute('DELETE', '/post/comment/{comment-id}', ['PostController', 'deleteComment']);

    //신고 기능
    $r->addRoute('POST', '/report', ['IndexController', 'reportContent']);
    $r->addRoute('POST','/login',['IndexController','login']);


//    $r->addRoute('GET', '/users', 'get_all_users_handler');
//    // {id} must be a number (\d+)
//    $r->addRoute('GET', '/user/{id:\d+}', 'get_user_handler');
//    // The /{title} suffix is optional
//    $r->addRoute('GET', '/articles/{id:\d+}[/{title}]', 'get_article_handler');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

// 로거 채널 생성
$accessLogs = new Logger('ACCESS_LOGS');
$errorLogs = new Logger('ERROR_LOGS');
// log/your.log 파일에 로그 생성. 로그 레벨은 Info
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));
// add records to the log
//$log->addInfo('Info log');
// Debug 는 Info 레벨보다 낮으므로 아래 로그는 출력되지 않음
//$log->addDebug('Debug log');
//$log->addError('Error log');

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        echo "405 Method Not Allowed";
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        switch ($routeInfo[1][0]) {
            case 'IndexController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/IndexController.php';
                break;
            case 'MainController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/MainController.php';
                break;
            case 'SeulgiController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/SeulgiController.php';
                break;
            case 'ReviewController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/ReviewController.php';
                break;
            case 'ExchangeController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/ExchangeController.php';
                break;
            case 'PostController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/PostController.php';
                break;
            /*
        case 'ProductController':
            $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
            require './controllers/ProductController.php';
            break;
        case 'SearchController':
            $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
            require './controllers/SearchController.php';
            break;
        case 'ReviewController':
            $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
            require './controllers/ReviewController.php';
            break;
        case 'ElementController':
            $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
            require './controllers/ElementController.php';
            break;
        case 'AskFAQController':
            $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
            require './controllers/AskFAQController.php';
            break;*/
        }

        break;
}
