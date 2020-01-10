<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI01080Controller extends Controller
{

    /**
     *  ColumSerch
     */

    public function ColSearch($vm)
    {
        $BushoCd = $vm->BushoCd;
        $sql = "SELECT
                    SHOHIN.商品ＣＤ   ,
                    SHOHIN.商品名   ,
                    SHOHIN.商品略称   ,
                    SHOHIN.商品区分   ,
                    SHOHIN.売価単価   ,
                    SHOHIN.弁当区分   ,
                    SHOHIN.ｸﾞﾙｰﾌﾟ区分   ,
                    SHOHIN.副食ＣＤ   ,
                    SHOHIN.主食ＣＤ   ,
                    SHOHIN.修正担当者ＣＤ   ,
                    SHOHIN.修正日
                FROM
                    商品マスタ SHOHIN   LEFT OUTER JOIN 各種テーブル BUSHOGRP ON     BUSHOGRP.各種CD = 26
                    AND
                    (SHOHIN.部署グループ = BUSHOGRP.サブ各種CD2 OR SHOHIN.部署グループ = 9)
                WHERE
                    SHOHIN.表示ＦＬＧ = 0
                    AND SHOHIN.弁当区分 = 0
                    AND BUSHOGRP.サブ各種CD1 = $BushoCd
                ORDER BY   SHOHIN.商品区分   ,SHOHIN.商品ＣＤ";


        $DataList = DB::select($sql);

        return response()->json($DataList);
    }
    /**
     * Search
     */
    public function Search($vm)
    {
        $BushoCd = $vm->BushoCd;
        $DeliveryDate = $vm->DeliveryDate;
        $CourseCd = $vm->CourseStart ?? 0;
        $sql = "
WITH WITH_注文データ AS
 (  SELECT
 		*
 	FROM
 		注文データ
 	WHERE
 		注文区分 = 0
 	  AND 部署CD = $BushoCd
 	  AND CONVERT(varchar, 注文日付, 112) = '$DeliveryDate'
 	  AND CONVERT(varchar, 配送日, 112)   = '$DeliveryDate'
)
,WITH_モバイル_予測入力 AS
(  SELECT
		*
	FROM
		モバイル_予測入力
	WHERE
		部署CD = $BushoCd
		AND CONVERT(varchar, 日付, 112) = '$DeliveryDate'
)
,WITH_注文予測 AS
(
   ----- \x2460：注文予測両方 -----
    SELECT
    	CHU.部署ＣＤ,
    	CHU.得意先ＣＤ,
    	MOB.商品ＣＤ  AS MOB商品ＣＤ,
    	ISNULL(MOB.見込数, 0) AS 見込数 ,
    	CHU.商品CD  AS CHU商品ＣＤ,
    	ISNULL(現金個数 + 掛売個数,0) AS 注文数
    FROM
    	WITH_注文データ CHU ,
    	WITH_モバイル_予測入力 MOB
    WHERE
    	CHU.部署ＣＤ   = MOB.部署ＣＤ
    	AND  CHU.得意先ＣＤ = MOB.得意先ＣＤ
    	AND CHU.商品ＣＤ   = MOB.商品ＣＤ
 UNION
   ----- \x2461：注文データのみ -----
   SELECT
   		CHU.部署ＣＤ,
   		CHU.得意先ＣＤ,
   		NULL AS MOB商品ＣＤ,
   		NULL AS 見込数,
   		CHU.商品CD AS CHU商品ＣＤ,
   		ISNULL(現金個数 + 掛売個数,0) AS 注文数
   FROM
   		WITH_注文データ CHU
   WHERE
   		NOT EXISTS
   		(
   			SELECT
   				'X'
   			 FROM
   			 	WITH_モバイル_予測入力 MOB
   			 WHERE
   			 	CHU.部署ＣＤ   = MOB.部署ＣＤ
   			 	AND CHU.得意先ＣＤ = MOB.得意先ＣＤ
   			 	AND CHU.商品ＣＤ   = MOB.商品ＣＤ
   		 )
  UNION
   ----- \x2462：予測のみ -----
   SELECT
   		MOB.部署ＣＤ,
   		MOB.得意先ＣＤ,
   		MOB.商品ＣＤ AS MOB商品ＣＤ,
   		ISNULL(MOB.見込数, 0) AS 見込数,
   		NULL AS CHU商品ＣＤ,
   		0 AS 注文数
   FROM
   		WITH_モバイル_予測入力 MOB
   WHERE
   		NOT EXISTS
   		(
   			SELECT
   				'X'
   			FROM
   				WITH_注文データ CHU
   	     	WHERE
   	     		CHU.部署ＣＤ   = MOB.部署ＣＤ
   	     		AND CHU.得意先ＣＤ = MOB.得意先ＣＤ
   	     		AND CHU.商品ＣＤ   = MOB.商品ＣＤ
   	     )
   	)
       SELECT
    　 ct.ＳＥＱ,
   	   ct.部署ＣＤ,
   	   ct.コースＣＤ,
   	   tokui.得意先ＣＤ,
   	   tokui.得意先名,
   	   ISNULL(MOBCHU.MOB商品ＣＤ,ISNULL(MOBCHU.CHU商品ＣＤ,0)) 商品ＣＤ,
   	   ISNULL(MOBCHU.見込数, 0) 見込数,
   	   ISNULL(MOBCHU.CHU商品ＣＤ,0) as CHU商品ＣＤ ,
   	   ISNULL(MOBCHU.注文数,0)as 注文数
   	FROM
   	   コーステーブル ct
   	   LEFT JOIN 得意先マスタ tokui ON ct.得意先ＣＤ = tokui.得意先ＣＤ
   	   AND ct.部署ＣＤ = tokui.部署ＣＤ
   	   LEFT JOIN WITH_注文予測 MOBCHU ON MOBCHU.得意先CD = ct.得意先CD AND MOBCHU.部署CD = ct.部署CD
    WHERE
    	ct.部署ＣＤ   = $BushoCd
    	AND ct.コースＣＤ = $CourseCd
   order by ct.コースＣＤ,ct.ＳＥＱ
";

        $DataList = DB::select(DB::raw($sql));

        return response()->json($DataList);
    }
}
