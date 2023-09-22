<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;
use Illuminate\Support\Facades\Log;

class DAI01010Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $BushoCd = $vm->BushoCd;
        $CourseKbn = $vm->CourseKbn;
        $DeliveryDate = $vm->DeliveryDate;
        //$DeliveryDate = Carbon::createFromFormat('Y年m月d日', $vm->DeliveryDate)->format('Ymd') ;

        //$WhereBusho = $BushoCd ? ("COU.部署ＣＤ = " . $BushoCd . " AND MOB_YOSOKU.部署CD IS NOT NULL AND") : "";
        $WhereBusho = $BushoCd ? "COU.部署ＣＤ = $BushoCd " : "";
        $WhereCourse = $CourseKbn ? ("AND COU.コース区分 = " . $CourseKbn) : "";


        $sql = "
-- 指定日付で有効なコースを取得する。一時コースが設定されていれば一時コース優先 --        
with WITH_TODAY_COURSE as(
select
 ct1.部署ＣＤ
,ct1.コースＣＤ
,cust.ＳＥＱ
,cust.得意先ＣＤ
,0 as special
from コーステーブル管理 ct1
        inner join コースマスタ cm on cm.部署ＣＤ=ct1.部署ＣＤ and cm.コース区分=$CourseKbn and cm.コースＣＤ=ct1.コースＣＤ
        inner join コーステーブル cust on cust.部署ＣＤ=ct1.部署ＣＤ and cust.コースＣＤ=ct1.コースＣＤ
where not exists(select 1 from コーステーブル管理 ct2 where ct2.部署ＣＤ=ct1.部署ＣＤ and ct2.コースＣＤ=ct1.コースＣＤ and ct2.管理ＣＤ<>0 and '$DeliveryDate'<=ct2.適用開始日 and ct2.適用終了日<='$DeliveryDate')
  and ct1.部署ＣＤ=$BushoCd
  and ct1.管理ＣＤ=0
union all
select
 ct1.部署ＣＤ
,ct1.コースＣＤ
,cust.ＳＥＱ
,cust.得意先ＣＤ
,1 as special
from コーステーブル管理 ct1
        inner join コースマスタ cm on cm.部署ＣＤ=ct1.部署ＣＤ and cm.コース区分=$CourseKbn and cm.コースＣＤ=ct1.コースＣＤ
        inner join コーステーブル一時 cust on cust.部署ＣＤ=ct1.部署ＣＤ and cust.コースＣＤ=ct1.コースＣＤ and cust.管理ＣＤ=ct1.管理ＣＤ
where ct1.管理ＣＤ<>0
  and '$DeliveryDate'<=ct1.適用開始日 and ct1.適用終了日<='$DeliveryDate'
  and ct1.部署ＣＤ=$BushoCd
)
-- WITH_TODAY_COURSEで取得した得意先のうち、重複する得意先は一時コース優先で取得 --        
,WITH_TODAY_COURSE_UNIQUE as(
select
 tod1.部署ＣＤ
,tod1.コースＣＤ
,tod1.ＳＥＱ
,tod1.得意先ＣＤ
    from WITH_TODAY_COURSE tod1
    where (tod1.special=1
            or ( tod1.special=0 and not exists(select 1 from WITH_TODAY_COURSE tod2 where tod2.部署ＣＤ=tod1.部署ＣＤ and tod2.得意先ＣＤ=tod1.得意先ＣＤ and tod2.special=1) )
        )
)

,WITH_注文データ AS (
SELECT * FROM 注文データ
		WHERE 注文区分 = 0
		    AND 部署CD = $BushoCd
		    AND 配送日 = '$DeliveryDate'
)
,WITH_モバイル_予測入力 AS (
SELECT * FROM モバイル_予測入力
		WHERE 部署CD = $BushoCd
		    AND 日付 = '$DeliveryDate'
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
    WHERE CHU.部署ＣＤ   = MOB.部署ＣＤ
    AND CHU.得意先ＣＤ = MOB.得意先ＣＤ
    AND CHU.商品ＣＤ   = MOB.商品ＣＤ

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
	    WHERE CHU.部署ＣＤ   = MOB.部署ＣＤ
	    AND CHU.得意先ＣＤ = MOB.得意先ＣＤ
	    AND CHU.商品ＣＤ   = MOB.商品ＣＤ
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
	    WHERE CHU.部署ＣＤ   = MOB.部署ＣＤ
	    AND CHU.得意先ＣＤ = MOB.得意先ＣＤ
	    AND CHU.商品ＣＤ   = MOB.商品ＣＤ)
	)
,WITH_コース別持出数 AS (
SELECT
    CT.部署ＣＤ
    , BUSYO.部署名
    , COU.コースＣＤ
    , COU.コース名
    , TOKUISAKI.得意先ＣＤ
    , TOKUISAKI.得意先名
    , ISNULL(MOB_YOSOKU.MOB商品ＣＤ, MOB_YOSOKU.CHU商品ＣＤ) as 商品ＣＤ
    , SHOHIN.商品略称 as 商品名
    , ISNULL(MOB_YOSOKU.見込数, 0) as 見込数
    , ISNULL(MOB_YOSOKU.CHU商品ＣＤ, 0) as CHU商品ＣＤ
    , ISNULL(MOB_YOSOKU.注文数, 0) as CHU注文数
    , ISNULL(HIBETSUP.製造パターン,TOKUISAKI.既定製造パターン) AS 既定製造パターン
    , SHOHIN.商品区分
    , case when ISNULL(HIBETSUP.製造パターン,TOKUISAKI.既定製造パターン) > 0
    then ISNULL(SEIZOHIN.副食ＣＤ, SHOHIN.副食ＣＤ)
    else SHOHIN.副食ＣＤ
    end as 副食ＣＤ

    , case when ISNULL(HIBETSUP.製造パターン,TOKUISAKI.既定製造パターン) > 0
    then ISNULL(SEIZOHIN.主食ＣＤ, SHOHIN.主食ＣＤ)
    else SHOHIN.主食ＣＤ
    end as 主食ＣＤ

FROM
    コースマスタ COU
    LEFT OUTER JOIN WITH_TODAY_COURSE_UNIQUE CT ON
    COU.コースＣＤ = CT.コースＣＤ AND
    COU.部署ＣＤ = CT.部署ＣＤ

    LEFT JOIN 部署マスタ BUSYO  ON
    COU.部署ＣＤ = BUSYO.部署CD

    LEFT JOIN 得意先マスタ TOKUISAKI ON
    CT.得意先ＣＤ = TOKUISAKI.得意先ＣＤ

    LEFT JOIN 日別得意先製造パターン HIBETSUP on
        HIBETSUP.部署ＣＤ = CT.部署ＣＤ
    AND CONVERT(varchar, HIBETSUP.製造日, 112) = $DeliveryDate
    AND HIBETSUP.コースＣＤ = CT.コースＣＤ
    AND HIBETSUP.得意先ＣＤ = CT.得意先ＣＤ

    LEFT JOIN WITH_注文予測 MOB_YOSOKU ON
        CT.得意先ＣＤ = MOB_YOSOKU.得意先ＣＤ
    AND CT.部署ＣＤ   = MOB_YOSOKU.部署ＣＤ

    LEFT JOIN 商品マスタ SHOHIN ON
    ISNULL(MOB_YOSOKU.CHU商品ＣＤ, MOB_YOSOKU.MOB商品ＣＤ)  = SHOHIN.商品ＣＤ

    LEFT JOIN 製造品マスタ SEIZOHIN ON
    ISNULL(MOB_YOSOKU.CHU商品ＣＤ, MOB_YOSOKU.MOB商品ＣＤ) = SEIZOHIN.商品ＣＤ
    AND SEIZOHIN.既定製造パターン = ISNULL(HIBETSUP.製造パターン,TOKUISAKI.既定製造パターン)

WHERE
    $WhereBusho
    $WhereCourse
)

SELECT
	WITH_コース別持出数.*
	,s1.商品略称 主食略称
	,s1.商品区分 主食商品区分
	,s2.商品略称 副食略称
	,s2.商品区分 副食商品区分
from WITH_コース別持出数
    LEFT JOIN 商品マスタ s1 ON s1.商品ＣＤ = WITH_コース別持出数.主食ＣＤ
    LEFT JOIN 商品マスタ s2 ON s2.商品ＣＤ = WITH_コース別持出数.副食ＣＤ
ORDER BY
    WITH_コース別持出数.部署ＣＤ
    , WITH_コース別持出数.コースＣＤ
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        $DataList = DB::select($sql);
        Log::info('DAI01010 sql\n' . $sql);//TODO:

        return response()->json($DataList);
    }
}
