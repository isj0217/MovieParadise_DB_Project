<?php

// 1. 회원가입
function createUser($id, $last_name, $first_name, $address, $city, $state, $zipcode, $telephone, $email, $credit_card, $account_type)
{
    $pdo = pdoSqlConnect();
    $query = "INSERT INTO CUSTOMER(customerID, lastName, firstName, address, city, states, zipCode, telephone, email, creditCard, accountType) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$id, $last_name, $first_name, $address, $city, $state, $zipcode, $telephone, $email, $credit_card, $account_type]);

    $st = null;
    $pdo = null;
}

function isDuplicatedUser($id)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from CUSTOMER where customerID=?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}


// 2. 로그인 - accountNum 반환
function getAccountNumFromId($id)
{
    $pdo = pdoSqlConnect();
    $query = "select accountNum from CUSTOMER where customerID = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$id]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}


// 3. 현재 빌린 상태인 영화 제목 조회
function getCurrentlyHeld($account_num)
{
    $pdo = pdoSqlConnect();
    $query = "select movieName
from MOVIE
where movieID in (
    select movieID
    from ORDERS
             inner join CUSTOMER on ORDERS.customerID = CUSTOMER.customerID
    where CUSTOMER.accountNum = ?
      and returnDate is null);";

    $st = $pdo->prepare($query);
    $st->execute([$account_num]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


// 4. 내 무비큐 조회
function getMovieQueue($account_num)
{
    $pdo = pdoSqlConnect();
    $query = "select movieName from MOVIE where movieID in(
select movieID
from MOVIE_QUEUE
where customerID in (
    select customerID
    from CUSTOMER
    where accountNum = ?)
  and isRented = 0);";

    $st = $pdo->prepare($query);
    $st->execute([$account_num]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


// 5. Account type 조회 (L인지 U인지)
function getAccountType($account_num)
{
    $pdo = pdoSqlConnect();
    $query = "select accountType as accountType from CUSTOMER where accountNum = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$account_num]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}


// 6. 장르별로 대여 가능한 영화 조회
function getAvailableGenre($genre)
{
    $pdo = pdoSqlConnect();
    $query = "select movieName from MOVIE where movieType = ? and numCopies > 0;";

    $st = $pdo->prepare($query);
    $st->execute([$genre]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


// 7. 영화 제목 검색
function searchMovieName($query_string)
{
    $pdo = pdoSqlConnect();
    $query = "select movieName
from MOVIE
where movieName like concat('%', ?, '%');";

    $st = $pdo->prepare($query);
    $st->execute([$query_string]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


// 8. 배우이름으로 영화 제목 검색
function searchMovieNameFromActor($query_string)
{
    $pdo = pdoSqlConnect();
    $query = "select movieName from MOVIE where movieID in(
select movieID from APPEARANCE where actorID in(
select actorID from ACTOR where actorName like concat('%', ?, '%')));";

    $st = $pdo->prepare($query);
    $st->execute([$query_string]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}


// 9. 영화에 평점 매기기
function postRating($customerID, $movieID, $rating)
{
    $pdo = pdoSqlConnect();
    $query = "insert into RATES_ON_MOVIES (customerID, movieID, rating) VALUES(?, ?, ?);";

    $st = $pdo->prepare($query);
    $st->execute([$customerID, $movieID, $rating]);

    $st = null;
    $pdo = null;
}

function isDuplicatedRating($customerID, $movieID)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from RATES_ON_MOVIES where customerID = ? AND movieID = ?) as exist;";

    $st = $pdo->prepare($query);
    $st->execute([$customerID, $movieID]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return intval($res[0]['exist']);
}


// 추가 1. 내가 본(빌렸다가 반납한) 영화 조회
function getWatched($account_num)
{
    $pdo = pdoSqlConnect();
    $query = "select movieName from MOVIE where movieID in(
select distinct movieID from ORDERS where returnDate is not null and customerID in(
select customerID from CUSTOMER where accountNum = ?));";

    $st = $pdo->prepare($query);
    $st->execute([$account_num]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}



// 추가 2. 영화 이름으로 movieID 조회
function getMovieID($query_string)
{
    $pdo = pdoSqlConnect();
    $query = "select movieID from MOVIE where movieName = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$query_string]);

    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}


// 이 아래는 테스트입니다.

function getCurrentlyHeldNum($id)
{
    $pdo = pdoSqlConnect();
    $query = "select count(movieID) as num_of_rent from ORDERS where customerID = ? AND returnDate is null;";

    $st = $pdo->prepare($query);
    $st->execute([$id]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}


function getUsers()
{
    $pdo = pdoSqlConnect();
    $query = "select lastName from CUSTOMER;";

    $st = $pdo->prepare($query);
    $st->execute([]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

function getUserDetail($no)
{
    $pdo = pdoSqlConnect();
    $query = "select * from CUSTOMER where accountNum = ?;";

    $st = $pdo->prepare($query);
    $st->execute([$no]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

function isValidUserIdx($userIdx)
{
    $pdo = pdoSqlConnect();
    $query = "select EXISTS(select * from CUSTOMER where customerID = ?) exist;";

    $st = $pdo->prepare($query);
    $st->execute([$userIdx]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0]['exist'];
}
