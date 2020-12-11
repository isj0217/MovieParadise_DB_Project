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

    /* ******************   Movie Paradise   ****************** */

    // 1. 회원가입
    $r->addRoute('POST', '/user', ['IndexController', 'createUser']);

    // 2. 로그인 - accountNum 반환
    $r->addRoute('POST', '/signin', ['IndexController', 'signin']);

    // 3. 현재 빌린 상태인 영화 제목 조회
    $r->addRoute('GET', '/currently_held/{accountNum}', ['IndexController', 'getCurrentlyHeld']);

    // 4. 내 무비큐 조회
    $r->addRoute('GET', '/movie_queue/{accountNum}', ['IndexController', 'getMovieQueue']);

    // 5. Account type 조회 (L인지 U인지)
    $r->addRoute('GET', '/account_type/{accountNum}', ['IndexController', 'getAccountType']);

    // 6. 장르별로 대여 가능한 영화 조회
    $r->addRoute('GET', '/available', ['IndexController', 'getAvailableGenre']);

    // 7. 영화 제목 검색
    $r->addRoute('GET', '/search', ['IndexController', 'searchMovieName']);

    // 8. 배우이름으로 영화 제목 검색
    $r->addRoute('GET', '/search_from_actor', ['IndexController', 'searchMovieNameFromActor']);

    // 9. 베스트셀러 -> 생략!!

    // 10. 영화에 평점 매기기
    $r->addRoute('POST', '/rating', ['IndexController', 'postRating']);


    // 이 아래는 테스트입니다.

    /* ******************   Test   ****************** */
    $r->addRoute('GET', '/', ['IndexController', 'index']);
    $r->addRoute('GET', '/users', ['IndexController', 'getUsers']);
    $r->addRoute('GET', '/user/{no}', ['IndexController', 'getUserDetail']);

    /* ******************   JWT   ****************** */
    $r->addRoute('POST', '/jwt', ['JWTController', 'createJwt']);   // JWT 생성: 로그인 + 해싱된 패스워드 검증 내용 추가
    $r->addRoute('GET', '/jwt', ['JWTController', 'validateJwt']);  // JWT 유효성 검사

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
$accessLogs->pushHandler(new StreamHandler('logs/access.log', Logger::INFO));
$errorLogs->pushHandler(new StreamHandler('logs/errors.log', Logger::ERROR));


switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        echo "404 Not Found";
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
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
        }

        break;
}
