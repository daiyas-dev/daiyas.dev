<?php

function Search() {
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
        WITH 得意先単価 AS (
            SELECT
                MTT.得意先ＣＤ,
                MTT.商品ＣＤ,
                PM.商品名,
                PM.商品区分,
                MTT.単価
            FROM
                (
                    SELECT
                        *
                    FROM (
                        SELECT
                            *
                            , RANK() OVER(PARTITION BY 得意先ＣＤ, 商品ＣＤ ORDER BY 適用開始日 DESC) AS RNK
                        FROM
                            得意先単価マスタ新
                        WHERE
                            得意先ＣＤ=$CustomerCd
                        AND 適用開始日 <= '$DeliveryDate'
                    ) TT
                    WHERE
                        RNK = 1
                ) MTT
                LEFT OUTER JOIN 商品マスタ PM
                    ON	PM.商品ＣＤ=MTT.商品ＣＤ
        ),
        注文一覧 AS (
            SELECT
                MTT.得意先ＣＤ,
                MTT.商品ＣＤ,
                IIF(CD.予備金額１ != 0, CD.予備金額１, MTT.単価) AS 単価,
                MTT.商品名,
                MTT.商品区分,
                CD.注文区分,
                CD.注文日付,
                CD.注文時間,
                CD.配送日,
                CD.明細行Ｎｏ,
                CD.入力区分,
                CD.現金個数,
                CD.現金金額,
                CD.掛売個数,
                CD.掛売金額,
                CD.予備ＣＤ１,
                CD.予備金額１,
                CD.修正担当者ＣＤ,
                CD.Web受注ID,
				WM.Web得意先ＣＤ,
                CD.修正日
            FROM
                得意先単価 MTT
                LEFT OUTER JOIN 注文データ CD
                    ON  CD.得意先ＣＤ = MTT.得意先ＣＤ
                    AND CD.商品ＣＤ = MTT.商品ＣＤ
                    AND CD.配送日 = '$DeliveryDate'
                LEFT OUTER JOIN Web受注得意先マスタ WM
                    ON  WM.得意先ＣＤ = CD.得意先ＣＤ
        )
        SELECT
            得意先ＣＤ,
            MAX(注文日付) AS 注文日付,
            MAX(配送日) AS 配送日,
            MAX(IIF(注文区分=0, 注文時間, null)) AS 注文時間,
            MIN(注文区分) AS 注文区分,
            MAX(IIF(注文区分=0, 明細行Ｎｏ, 0)) AS 明細行Ｎｏ,
            商品ＣＤ,
            商品名,
            単価,
            商品区分,
            SUM(IIF(注文区分=1, 現金個数 + 掛売個数, 0)) AS 予定数,
            SUM(IIF(注文区分=0, 現金個数, 0)) AS 現金個数,
            SUM(IIF(注文区分=0, 現金金額, 0)) AS 現金金額,
            SUM(IIF(注文区分=0, 掛売個数, 0)) AS 掛売個数,
            SUM(IIF(注文区分=0, 掛売金額, 0)) AS 掛売金額,
            MAX(IIF(注文区分 IS NULL, 1, 0)) AS 全表示,
            MAX(Web受注ID) AS Web受注ID,
            MAX(Web得意先ＣＤ) AS Web得意先ＣＤ,
            MAX(IIF(注文区分=0, 修正日, null)) AS 修正日
        FROM
            注文一覧
        GROUP BY
            得意先ＣＤ,
            商品ＣＤ,
            単価,
            商品区分,
            商品名
        ORDER BY
            商品ＣＤ
    ";

    $stmt = $pdo->query($sql);
    $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $HasShown = false;
    foreach ($DataList as $item) {
        if ($item["全表示"] == 1) {
            $HasShown = true;
        }
    }

    if (!!$HasShown) {
        $sql = "
            WITH 得意先単価 AS (
                SELECT
                    MTT.得意先ＣＤ,
                    MTT.商品ＣＤ,
                    PM.商品名,
                    PM.商品区分,
                    MTT.単価
                FROM
                    (
                        SELECT
                            *
                        FROM (
                            SELECT
                                *
                                , RANK() OVER(PARTITION BY 得意先ＣＤ, 商品ＣＤ ORDER BY 適用開始日 DESC) AS RNK
                            FROM
                                得意先単価マスタ新
                            WHERE
                                得意先ＣＤ=$CustomerCd
                            AND 適用開始日 <= '$DeliveryDate'
                        ) TT
                        WHERE
                            RNK = 1
                    ) MTT
                    LEFT OUTER JOIN 商品マスタ PM
                        ON	PM.商品ＣＤ=MTT.商品ＣＤ
            )
            SELECT DISTINCT
                注文データ.得意先ＣＤ,
                注文データ.商品ＣＤ,
                CASE WHEN 注文データ.予備金額１ IS NULL THEN 得意先単価.単価 ELSE 注文データ.予備金額１ END 単価,
                商品マスタ.商品名,
                0 タイトルフラグ
            FROM
                [注文データ]
                INNER JOIN
                    [商品マスタ] ON 商品マスタ.商品ＣＤ = 注文データ.商品ＣＤ
                INNER JOIN
                    得意先単価 ON 注文データ.商品ＣＤ = 得意先単価.商品ＣＤ
                AND
                    注文データ.得意先ＣＤ = 得意先単価.得意先ＣＤ
            WHERE 注文区分 = 0
                AND 注文データ.得意先ＣＤ = $CustomerCd
                AND 注文データ.注文区分 = 0
                AND 注文データ.配送日 >= DATEADD(DAY, -8, '$DeliveryDate')
                AND 注文データ.配送日 <=  '$DeliveryDate'
        ";

        $stmt = $pdo->query($sql);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($DataList as &$item) {
            if (in_array($item["商品ＣＤ"], $products, false)) {
                $item["全表示"] = '0';
            }
        }
    }

    $pdo = null;

    return $DataList;
}
