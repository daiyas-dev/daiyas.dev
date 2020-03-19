<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI01230Controller extends Controller
{
    /**
     *  ColumSearch
     */

    public function ColSearch($vm)
    {
        $BushoCd = $vm->BushoCd;
        $FactoryCdStart = $vm->FactoryCdStart;
        $FactoryCdEnd = $vm->FactoryCdEnd;

        $WhereBusho = !!$BushoCd ? "AND 部署CD = $BushoCd" : "";

        $sql = "
            SELECT
                部署CD
                ,部署名
                ,工場ＣＤ

            FROM 部署マスタ
            WHERE
                0=0
                $WhereBusho
                AND 工場ＣＤ >= $FactoryCdStart
                AND 工場ＣＤ <= $FactoryCdEnd
            ORDER by 工場ＣＤ　
        ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }

    /**
     * Search
     */
    public function Search($vm)
    {
        $DeliveryDate = $vm->DeliveryDate;
        $vm->targetDate = $DeliveryDate;

        $Utilities = new UtilitiesController();
        $CourseKbn = $Utilities->SearchCourseKbnFromDate($vm)->コース区分;

        $sql = "
WITH WITH_注文データ AS (
SELECT * FROM 注文データ
        WHERE 注文区分 = 0
            AND 注文日付 = '$DeliveryDate'
            AND 配送日 = '$DeliveryDate'
)
,WITH_モバイル_予測入力 AS (
SELECT
    部署ＣＤ,  得意先ＣＤ ,日付, 商品ＣＤ, SUM(見込数) as 見込数
    FROM
    モバイル_予測入力
GROUP BY
    部署ＣＤ,  得意先ＣＤ ,日付, 商品ＣＤ
HAVING 日付 = '$DeliveryDate'
)

,WITH_注文予測 AS (

----- ①：注文予測両方 -----
SELECT CHU.部署ＣＤ
        ,CHU.得意先ＣＤ
        ,MOB.商品ＣＤ  AS MOB商品ＣＤ
        ,ISNULL(MOB.見込数, 0) AS 見込数
        ,CHU.商品CD  AS CHU商品ＣＤ
        ,ISNULL(現金個数 + 掛売個数,0) AS 注文数
    FROM WITH_注文データ CHU ,WITH_モバイル_予測入力 MOB
    WHERE CHU.部署ＣＤ = MOB.部署ＣＤ
    AND CHU.得意先ＣＤ = MOB.得意先ＣＤ
    AND CHU.商品ＣＤ = MOB.商品ＣＤ

UNION

----- ②：注文データのみ -----
SELECT CHU.部署ＣＤ
        ,CHU.得意先ＣＤ
        ,NULL AS MOB商品ＣＤ
        ,NULL AS 見込数
        ,CHU.商品CD AS CHU商品ＣＤ
        ,ISNULL(現金個数 + 掛売個数,0) AS 注文数
    FROM WITH_注文データ CHU
    WHERE
    NOT EXISTS
    (SELECT 'X'
        FROM WITH_モバイル_予測入力 MOB
        WHERE CHU.部署ＣＤ = MOB.部署ＣＤ
        AND CHU.得意先ＣＤ = MOB.得意先ＣＤ
        AND CHU.商品ＣＤ = MOB.商品ＣＤ
    )

UNION

----- ③：予測のみ -----
SELECT MOB.部署ＣＤ
        ,MOB.得意先ＣＤ
        ,MOB.商品ＣＤ AS MOB商品ＣＤ
        ,ISNULL(MOB.見込数, 0) AS 見込数
        ,NULL AS CHU商品ＣＤ
        ,0 AS 注文数
    FROM WITH_モバイル_予測入力 MOB
    WHERE
    NOT EXISTS
    (SELECT 'X'
        FROM WITH_注文データ CHU
        WHERE CHU.部署ＣＤ = MOB.部署ＣＤ
        AND CHU.得意先ＣＤ = MOB.得意先ＣＤ
        AND CHU.商品ＣＤ = MOB.商品ＣＤ)
    )
,WITH_コース別持出数 AS (
SELECT
    BUSYO.部署ＣＤ
    , BUSYO.部署名
    , TOKUISAKI.得意先ＣＤ
    , TOKUISAKI.得意先名
    , ISNULL(MOB.MOB商品ＣＤ,MOB.CHU商品ＣＤ) as  商品ＣＤ
    , SHOHIN.商品名
    , ISNULL(MOB.見込数, 0) as 見込数
    , ISNULL(HIBETSUP.製造パターン,TOKUISAKI.既定製造パターン) AS 既定製造パターン

    , case when ISNULL(HIBETSUP.製造パターン,TOKUISAKI.既定製造パターン) > 0
    then ISNULL(SEIZOHIN.副食ＣＤ, SHOHIN.副食ＣＤ)
    else SHOHIN.副食ＣＤ
    end as 副食ＣＤ

    , case when ISNULL(HIBETSUP.製造パターン,TOKUISAKI.既定製造パターン) > 0
    then ISNULL(SEIZOHIN.主食ＣＤ, SHOHIN.主食ＣＤ)
    else SHOHIN.主食ＣＤ
    end as 主食ＣＤ

    , ISNULL(MOB.CHU商品ＣＤ,0) as CHU商品ＣＤ
    , ISNULL(MOB.注文数,0) as CHU注文数

    , SHOHIN.部署グループ
    , BUSYO.工場ＣＤ
FROM
    コースマスタ COU
    INNER JOIN コーステーブル CT ON
        COU.コースＣＤ = CT.コースＣＤ
        AND COU.部署ＣＤ = CT.部署ＣＤ
        AND COU.コース区分 = $CourseKbn

    LEFT JOIN 部署マスタ BUSYO  ON
            COU.部署ＣＤ = BUSYO.部署CD

    LEFT JOIN 得意先マスタ TOKUISAKI ON
            CT.得意先ＣＤ = TOKUISAKI.得意先ＣＤ

    LEFT JOIN 日別得意先製造パターン HIBETSUP on
        HIBETSUP.部署ＣＤ = CT.部署ＣＤ
    AND HIBETSUP.製造日 = '$DeliveryDate'
    AND HIBETSUP.コースＣＤ = CT.コースＣＤ
    AND HIBETSUP.得意先ＣＤ = CT.得意先ＣＤ

    left outer join WITH_注文予測 MOB on MOB.部署ＣＤ = CT.部署ＣＤ AND MOB.得意先ＣＤ = TOKUISAKI.得意先ＣＤ

    left join 商品マスタ SHOHIN on
    ISNULL(MOB.MOB商品ＣＤ,MOB.CHU商品ＣＤ) = SHOHIN.商品ＣＤ

    left join 製造品マスタ SEIZOHIN on
    SEIZOHIN.商品ＣＤ = ISNULL(MOB.MOB商品ＣＤ,MOB.CHU商品ＣＤ)

    AND SEIZOHIN.既定製造パターン = ISNULL(HIBETSUP.製造パターン,TOKUISAKI.既定製造パターン)
)

SELECT
    WITH_コース別持出数.*
    ,s0.商品区分 商品区分
	,s1.商品名 主食名
	,s2.商品名 副食名
FROM WITH_コース別持出数
    LEFT JOIN 商品マスタ s0 ON s0.商品ＣＤ = WITH_コース別持出数.商品ＣＤ
    LEFT JOIN 商品マスタ s1 ON s1.商品ＣＤ = WITH_コース別持出数.主食ＣＤ
    LEFT JOIN 商品マスタ s2 ON s2.商品ＣＤ = WITH_コース別持出数.副食ＣＤ
WHERE
    WITH_コース別持出数.主食ＣＤ IS NOT NULL OR WITH_コース別持出数.副食ＣＤ IS NOT NULL
                    ";

        // $DataList = DB::select($sql);
        $dsn = 'sqlsrv:server=localhost;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        return response()->json($DataList);
    }
}
