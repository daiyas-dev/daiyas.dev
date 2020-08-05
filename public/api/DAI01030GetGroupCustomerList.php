<?php

header('Content-Type: application/json; charset=UTF-8');
$req = count($_REQUEST) > 0 ? $_REQUEST : json_decode(file_get_contents("php://input"), true);

if (!isset($req)) {
    print json_encode([], JSON_PRETTY_PRINT);
    return;
}

$CustomerCd = $req['CustomerCd'];

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
    得意先ＣＤ AS Cd,
    得意先名 AS CdNm,
    *
FROM 得意先マスタ
WHERE 得意先ＣＤ IN (
SELECT
	DISTINCT(Tel_CustNo) AS 得意先ＣＤ
FROM C_TelToCust
WHERE Tel_CustNo != $CustomerCd
AND Tel_TelNo IN (
	SELECT Tel_TelNo
	FROM C_TelToCust
	WHERE Tel_CustNo = $CustomerCd
	AND Tel_DelFlg = 0
)
AND Tel_DelFlg = 0
)
        ";

$stmt = $pdo->query($sql);
$Result = $stmt->fetchAll(PDO::FETCH_ASSOC);
$pdo = null;

print json_encode($Result, JSON_PRETTY_PRINT);
