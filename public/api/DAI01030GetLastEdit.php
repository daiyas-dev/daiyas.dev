<?php

header('Content-Type: application/json; charset=UTF-8');
$req = count($_REQUEST) > 0 ? $_REQUEST : json_decode(file_get_contents("php://input"), true);

if (!isset($req)) {
    print json_encode([], JSON_PRETTY_PRINT);
    return;
}

$BushoCd = $req['BushoCd'];
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
            SELECT TOP 1
                CD.修正担当者ＣＤ,
                TM.担当者名 AS 修正担当者名,
                CD.修正日
            FROM
                注文データ CD
                LEFT OUTER JOIN 担当者マスタ TM
                    ON  TM.担当者ＣＤ = CD.修正担当者ＣＤ
            WHERE
                CD.得意先ＣＤ = $CustomerCd
            AND CD.配送日 = '$DeliveryDate'
            ORDER BY
                修正日 DESC
        ";

$stmt = $pdo->query($sql);
$Result = $stmt->fetch(PDO::FETCH_ASSOC);
$pdo = null;

print json_encode($Result, JSON_PRETTY_PRINT);
