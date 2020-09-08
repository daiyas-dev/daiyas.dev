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
    SELECT
        TK.得意先ＣＤ
        ,ISNULL(CD.特記_社内用, TK.備考１) AS 備考社内
        ,ISNULL(CD.特記_配送用, TK.備考２) AS 備考配送
        ,ISNULL(CD.特記_通知用, TK.備考３) AS 備考通知
        ,CD.注文区分
    FROM
        得意先マスタ TK
        LEFT OUTER JOIN 注文データ CD
            ON  CD.得意先ＣＤ = TK.得意先ＣＤ
            AND CD.配送日 = '$DeliveryDate'
            AND CD.注文区分=0
    WHERE
        TK.得意先ＣＤ = $CustomerCd
    ORDER BY
        CD.注文区分 DESC
        ,CD.商品ＣＤ
";

$stmt = $pdo->query($sql);
$Result = $stmt->fetchAll(PDO::FETCH_ASSOC);
$pdo = null;

print json_encode($Result, JSON_PRETTY_PRINT);
