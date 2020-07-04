<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI01020Controller extends Controller
{
    /**
     * GetProductList
     */
    public function GetProductList($vm)
    {
        $BushoCd = $vm->BushoCd;

        $sql = "
WITH Products AS (
SELECT distinct
  SHOHIN.商品ＣＤ
  ,SHOHIN.商品名
  ,SHOHIN.商品略称
  ,SHOHIN.商品区分
  ,SHOHIN.売価単価
  ,SHOHIN.弁当区分
  ,SHOHIN.ｸﾞﾙｰﾌﾟ区分
  ,SHOHIN.副食ＣＤ
  ,SHOHIN.主食ＣＤ
  ,SHOHIN.表示ＦＬＧ
  ,SHOHIN.修正担当者ＣＤ
  ,SHOHIN.修正日
FROM
  商品マスタ SHOHIN
  LEFT OUTER JOIN 各種テーブル BUSHOGRP ON
    BUSHOGRP.各種CD = 26
    AND ( SHOHIN.部署グループ = BUSHOGRP.サブ各種CD2  OR SHOHIN.部署グループ = 9 )
WHERE
  SHOHIN.表示ＦＬＧ = 0
  AND ( SHOHIN.弁当区分 = 0 OR SHOHIN.弁当区分 = 8 )
  AND (( 1 <= SHOHIN.商品区分 AND SHOHIN.商品区分 <= 7 ) OR SHOHIN.商品区分 = 9 )
  AND BUSHOGRP.サブ各種CD1 = $BushoCd
)
, ProductsMainSub AS (
SELECT
	IIF(s1.商品ＣＤ IS NULL, Products.商品ＣＤ, s1.商品ＣＤ) AS 商品ＣＤ
	,IIF(s1.商品名 IS NULL, Products.商品名, s1.商品名) AS 商品名
	,IIF(s1.商品略称 IS NULL, Products.商品略称, s1.商品略称) AS 商品略称
	,IIF(s1.商品区分 IS NULL, Products.商品区分, s1.商品区分) AS 商品区分
FROM Products
    LEFT JOIN 商品マスタ s1 ON s1.商品ＣＤ = Products.主食ＣＤ OR s1.商品ＣＤ = Products.副食ＣＤ
WHERE
	Products.主食ＣＤ != 0 OR Products.副食ＣＤ != 0 OR Products.表示ＦＬＧ != 1
)
SELECT DISTINCT
	商品ＣＤ,
	IIF(商品略称 IS NULL, 商品名, 商品略称) AS 表示名,
	商品区分
FROM ProductsMainSub
ORDER BY
	商品区分, 商品ＣＤ
        ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }

    /**
     * Search
     */
    public function Search($vm)
    {
        $BushoCd = $vm->BushoCd;
        $CourseKbn = $vm->CourseKbn;
        $DeliveryDate = $vm->DeliveryDate;

        $WhereCourseKbn = $CourseKbn ? ("AND コースマスタ.コース区分 = " . $CourseKbn) : "";

        $sql = "
SELECT
    コースマスタ.部署CD,
    '$DeliveryDate' AS 対象日付,
	コースマスタ.コースＣＤ,
	コースマスタ.コース名,
	モバイル_持ち出し入力.持ち出し日付,
	モバイル_持ち出し入力.商品ＣＤ,
	モバイル_持ち出し入力.個数,
	モバイル_持ち出し入力.修正日
FROM
	コースマスタ
	LEFT OUTER JOIN [モバイル_持ち出し入力]
		ON  コースマスタ.コースＣＤ = モバイル_持ち出し入力.コースＣＤ
		AND コースマスタ.部署ＣＤ = モバイル_持ち出し入力.部署ＣＤ
		AND モバイル_持ち出し入力.持ち出し日付 = '$DeliveryDate'
WHERE
    コースマスタ.部署CD = $BushoCd
$WhereCourseKbn
        ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }

    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();
        $targets = $params['targets'];

        //トランザクション開始
        DB::transaction(function() use ($targets) {
            collect($targets)->each(function ($target, $key) {
                $BushoCd = $target['部署CD'];
                $CourseCd = $target['コースＣＤ'];
                $ShohinCd = $target['商品CD'];
                $Amount = $target['個数'];
                $TargetDate = $target['対象日付'];

                $sql = "
IF EXISTS (
	SELECT
		持ち出し日付
	FROM
		モバイル_持ち出し入力
	WHERE
		持ち出し日付 = '$TargetDate'
	AND 部署CD = $BushoCd
	AND コースＣＤ = $CourseCd
	AND 商品ＣＤ = $ShohinCd
)
BEGIN
	UPDATE モバイル_持ち出し入力
	SET
		商品CD = $ShohinCd,
		個数 = $Amount,
		修正日 = GETDATE()
	WHERE
		持ち出し日付 = '$TargetDate'
	AND 部署CD = $BushoCd
	AND コースＣＤ = $CourseCd
	AND 商品ＣＤ = $ShohinCd
END
ELSE
	INSERT INTO モバイル_持ち出し入力
	VALUES ($BushoCd, $CourseCd, '$TargetDate', $ShohinCd, $Amount, GETDATE())
        ";

                DB::statement($sql);
            });
        });

        //モバイルsv更新
        $busho_cd=$params['conditions']['BushoCd'];
        $date=preg_replace('/年|月|日/','',$params['conditions']['DeliveryDate']);;
        $Message = $params['Message'];
        $ds = new DataSendWrapper();
        $ds->UpdateTakeOutInputData($busho_cd, $date, $Message);

        return response()->json([
            'result' => true,
        ]);
    }
}
