<?php

header('Content-Type: application/json; charset=UTF-8');
$req = count($_REQUEST) > 0 ? $_REQUEST : json_decode(file_get_contents("php://input"), true);

if (!isset($req)) {
    print json_encode([], JSON_PRETTY_PRINT);
    return;
}

$CustomerCd = $req['CustomerCd'];
$DeliveryDate = $req['DeliveryDate'];

if (!isset($CustomerCd) || !is_numeric($CustomerCd) || !ctype_digit($CustomerCd)) {
    print json_encode([], JSON_PRETTY_PRINT);
    return;
}

$dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
$user = 'daiyas';
$password = 'daiyas';

$pdo = new PDO($dsn, $user, $password);

$sql = "
    SELECT
        実績数
    FROM
        モバイル_販売入力
    WHERE
        得意先CD=$CustomerCd AND
        実績入力=1 AND
        日付='$DeliveryDate'
";

$stmt = $pdo->query($sql);
$Result = $stmt->fetchAll(PDO::FETCH_ASSOC);
$pdo = null;

$Result = ["IsDeliveried" => count($Result) > 0];

print json_encode($Result, JSON_PRETTY_PRINT);
