<?php

require './pdos/DatabasePdo.php';
require './pdos/IndexPdo.php';
require './pdos/JWTPdo.php';
require './vendor/autoload.php';

use \Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

date_default_timezone_set('Asia/Seoul');
ini_set('default_charset', 'utf8mb4');

//에러출력하게 하는 코드
//error_reporting(E_ALL); ini_set("display_errors", 1);
//Main Server API
$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    /* ******************   GET   ****************** */
    $r->addRoute('GET', '/login/jwt', ['JWTController', 'validateJwt']);  // JWT 유효성 검사
    $r->addRoute('GET', '/is-duplicate-id', ['IndexController', 'isValidUserId']);
    $r->addRoute('GET', '/home', ['IndexController', 'getHomePage']);
    $r->addRoute('GET', '/recommend', ['IndexController', 'getRecommendPage']);
    $r->addRoute('GET', '/product/{productIdx}', ['IndexController', 'getProductInfo']);
    $r->addRoute('GET', '/user', ['IndexController', 'getUserInfo']);
    $r->addRoute('GET', '/product/{productIdx}/option', ['IndexController', 'getSelectPage']);
    $r->addRoute('GET', '/basket', ['IndexController', 'getBasket']);
    $r->addRoute('GET', '/history', ['IndexController', 'getHistory']);
    $r->addRoute('GET', '/history/{orderIdx}', ['IndexController', 'getHistoryDetail']);
    $r->addRoute('GET', '/is-morning-destination', ['IndexController', 'isMorningDestination']);
    $r->addRoute('GET', '/search', ['IndexController', 'getSearch']);
    $r->addRoute('GET', '/destination', ['IndexController', 'getDestination']);



    $r->addRoute('POST', '/order-form', ['IndexController', 'getPay']);
    $r->addRoute('POST', '/order-form/coupon', ['IndexController', 'getCoupon']);


    /* ******************   POST   ****************** */
    $r->addRoute('POST', '/user', ['IndexController', 'createUser']);
    $r->addRoute('POST', '/basket', ['IndexController', 'addBasket']);
    $r->addRoute('POST', '/login/guest', ['JWTController', 'createJwt']);   // JWT 생성: 로그인 + 해싱된 패스워드 검증 내용 추가
    $r->addRoute('POST', '/order', ['IndexController', 'addPay']);
    $r->addRoute('POST', '/destination-at-order', ['IndexController', 'addDestinationAtOrder']);
    $r->addRoute('POST', '/destination-at-userinfo', ['IndexController', 'addDestinationAtUserInfo']);

    /* ******************   DELETE   ****************** */
    $r->addRoute('DELETE', '/basket', ['IndexController', 'deleteBasket']);
    $r->addRoute('DELETE', '/destination', ['IndexController', 'deleteDestination']);
    $r->addRoute('DELETE', '/order', ['IndexController', 'deleteOrder']);


    /* ******************   PATCH   ****************** */
    $r->addRoute('PATCH', '/basket', ['IndexController', 'changeBasket']);
    $r->addRoute('PATCH', '/destination-at-order', ['IndexController', 'changeDestinationAtOrder']);
    $r->addRoute('PATCH', '/destination-at-userinfo', ['IndexController', 'changeDestinationAtUserInfo']);

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
            case 'JWTController':
                $handler = $routeInfo[1][1];
                $vars = $routeInfo[2];
                require './controllers/JWTController.php';
                break;

            /*case 'EventController':
                $handler = $routeInfo[1][1]; $vars = $routeInfo[2];
                require './controllers/EventController.php';
                break;
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
