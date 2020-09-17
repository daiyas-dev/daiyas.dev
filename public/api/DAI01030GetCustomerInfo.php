<?php

header('Content-Type: application/json; charset=UTF-8');
$req = count($_REQUEST) > 0 ? $_REQUEST : json_decode(file_get_contents("php://input"), true);

if (!isset($req)) {
    print json_encode(null, JSON_PRETTY_PRINT);
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
        M1.部署ＣＤ,
        MB.部署名,
        M1.得意先ＣＤ,
        M1.得意先名,
        M1.得意先名略称,
        M1.得意先名カナ,
        M1.売掛現金区分,
        M1.電話番号１,
        M1.備考１,
        M1.備考２,
        M1.備考３,
        MC.コースＣＤ,
        MC.コース名,
        MC.コース区分,
        MC.管理ＣＤ,
        MC.一時フラグ,
        MC.担当者ＣＤ,
        MT.担当者名
    FROM
        得意先マスタ M1
        LEFT OUTER JOIN 部署マスタ MB
            ON MB.部署ＣＤ = M1.部署ＣＤ
        LEFT OUTER JOIN 祝日マスタ MH
            ON  MH.対象日付 = '$DeliveryDate'
            AND (対象部署ＣＤ IS NULL OR 対象部署ＣＤ LIKE '%' + CONVERT(varchar,MB.部署ＣＤ) + '%')
        LEFT OUTER JOIN (
            SELECT
                CT.部署ＣＤ
                ,CT.コースＣＤ
                ,CT.管理ＣＤ
                ,CTC.一時フラグ
                ,CM.コース名
                ,CM.コース区分
                ,CM.担当者ＣＤ
                ,CT.得意先ＣＤ
            FROM
                (
                    SELECT
                        部署ＣＤ, コースＣＤ, 0 AS 管理ＣＤ, ＳＥＱ, 得意先ＣＤ, 修正担当者ＣＤ, 修正日
                    FROM
                        コーステーブル
                    UNION ALL
                    SELECT
                        部署ＣＤ, コースＣＤ, 管理ＣＤ, ＳＥＱ, 得意先ＣＤ, 修正担当者ＣＤ, 修正日
                    FROM
                        コーステーブル一時
                ) CT
                    INNER JOIN (
                        SELECT
                            *
                        FROM (
                            SELECT
                                部署ＣＤ
                                ,コースＣＤ
                                ,管理ＣＤ
                                ,一時フラグ
                                ,RANK() OVER(PARTITION BY 部署ＣＤ, コースＣＤ ORDER BY 一時フラグ DESC) AS RNK
                            FROM
                                コーステーブル管理
                            WHERE
                                適用開始日 <= '$DeliveryDate' AND 適用終了日 >= '$DeliveryDate'
                        ) X
                        WHERE
                            RNK = 1
                    ) CTC
                        ON  CTC.部署ＣＤ=CT.部署ＣＤ
                        AND CTC.コースＣＤ=CT.コースＣＤ
                        AND CTC.管理ＣＤ=CT.管理ＣＤ
                LEFT JOIN コースマスタ CM
                    ON  CM.部署ＣＤ = CTC.部署ＣＤ
                    AND CM.コースＣＤ = CTC.コースＣＤ
        ) MC
            ON  MC.部署ＣＤ = M1.部署CD
            AND MC.得意先ＣＤ = M1.得意先ＣＤ
            AND MC.コース区分 = IIF(MH.対象日付 IS NOT NULL, 4, CASE DATEPART(WEEKDAY, '$DeliveryDate') WHEN 1 THEN 3 WHEN 7 THEN 2 ELSE 1 END)
        LEFT OUTER JOIN 担当者マスタ MT
            ON MT.担当者ＣＤ = MC.担当者ＣＤ
    WHERE
        M1.得意先CD = $CustomerCd
    AND (M1.受注得意先ＣＤ = 0 OR M1.受注得意先ＣＤ = M1.得意先ＣＤ)
";

$stmt = $pdo->query($sql);
$Result = $stmt->fetch(PDO::FETCH_ASSOC);
$pdo = null;

print json_encode($Result, JSON_PRETTY_PRINT);
