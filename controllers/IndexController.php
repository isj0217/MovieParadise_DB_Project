<?php
require 'function.php';

const JWT_SECRET_KEY = "TEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEYTEST_KEY";

$res = (object)array();
header('Content-Type: json');
$req = json_decode(file_get_contents("php://input"));
try {
    addAccessLogs($accessLogs, $req);
    switch ($handler) {
        case "index":
            echo "API Server";
            break;
        case "ACCESS_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/access.log");
            break;
        case "ERROR_LOGS":
            //            header('content-type text/html charset=utf-8');
            header('Content-Type: text/html; charset=UTF-8');
            getLogs("./logs/errors.log");
            break;

        // 1. 회원가입
        case "createUser":
            http_response_code(200);

            // Packet의 Body에서 데이터를 파싱합니다.
            $id = $req->id;

            $last_name = $req->last_name;
            $first_name = $req->first_name;
            $address = $req->address;
            $city = $req->city;
            $state = $req->state;
            $zipcode = $req->zipcode;
            $telephone = $req->telephone;
            $email = $req->email;
            $credit_card = $req->credit_card;
            $account_type = $req->account_type;

            if (isDuplicatedUser($id)) {

                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "Customer ID already exist";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            createUser($id, $last_name, $first_name, $address, $city, $state, $zipcode, $telephone, $email, $credit_card, $account_type);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "Sign Up Success";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        // 2. 로그인 - accountNum 반환
        case "signin":
            http_response_code(200);

            // Packet의 Body에서 데이터를 파싱합니다.
            $id = $req->id;

            if (!isDuplicatedUser($id)) {

                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "Customer ID does not exist";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $res->result = getAccountNumFromId($id);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "Sign In Success";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        // 3. 현재 빌린 상태인 영화 제목 조회
        case "getCurrentlyHeld":
            http_response_code(200);

            $account_num = $vars["account_num"];

            $res->result = getCurrentlyHeld($vars["account_num"]);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "Get currently held movie list Success";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        // 4. 내 무비큐 조회
        case "getMovieQueue":
            http_response_code(200);

            $accountNum = $vars["account_num"];

            $res->result = getMovieQueue($vars["account_num"]);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "Get my movie queue Success";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;



        // 5. Account type 조회 (L인지 U인지)
        case "getAccountType":
            http_response_code(200);

            $res->result = getAccountType($vars["account_num"]);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "Get my account type Success";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        // 6. 장르별로 대여 가능한 영화 조회
        case "getAvailableGenre":
            http_response_code(200);

            $genre = $_GET['genre'];

            $res->result = getAvailableGenre($genre);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "Get movies by genre Success";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        // 7. 영화 제목 검색
        case "searchMovieName":
            http_response_code(200);


            $query_string = $_GET['query'];

            $res->result = searchMovieName($query_string);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "Search Movies by Title Success";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        // 8. 배우이름으로 영화 제목 검색
        case "searchMovieNameFromActor":
            http_response_code(200);

            $query_string = $_GET['query'];

            $res->result = searchMovieNameFromActor($query_string);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "Search Movies by Actor Success";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        // 9. 영화에 평점 매기기
        case "postRating":
            http_response_code(200);

            // Packet의 Body에서 데이터를 파싱합니다.
            $customerID = $req->customerID;
            $movieID = $req->movieID;
            $rating = $req->rating;

            if (isDuplicatedRating($customerID, $movieID)) {

                $res->isSuccess = FALSE;
                $res->code = 200;
                $res->message = "Already rated this movie";
                echo json_encode($res, JSON_NUMERIC_CHECK);
                break;
            }

            $movieID = postRating($customerID, $movieID, $rating);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "Rating the movie Success";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        // 추가 1. 내가 본(빌렸다가 반납한) 영화 조회
        case "getWatched":
            http_response_code(200);

            $res->result = getWatched($vars["account_num"]);

            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "Get movies I watched Success";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;


        // 추가 2. 영화 이름으로 movieID 조회
        case "getMovieID":
            http_response_code(200);

            $query_string = $_GET['query'];

            $res->result = getMovieID($query_string);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "Search MovieID by movie title Success";

            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;





        // 이 아래는 테스트입니다.

        case "getUsers":
            http_response_code(200);

            $res->result = getUsers();
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

        case "getUserDetail":
            http_response_code(200);

            $res->result = getUserDetail($vars["no"]);
            $res->isSuccess = TRUE;
            $res->code = 100;
            $res->message = "테스트 성공";
            echo json_encode($res, JSON_NUMERIC_CHECK);
            break;

    }
} catch (\Exception $e) {
    return getSQLErrorException($errorLogs, $e, $req);
}
