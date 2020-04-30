<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI01070Controller extends Controller
{

    /**
     *  ColumSerch
     */

     public function ColSearch($vm)
     {
        $BushoCd = $vm->BushoCd;

        $sql = "
WITH 商品一覧 AS (
SELECT
   SHOHIN.商品ＣＤ
  ,SHOHIN.商品名
  ,SHOHIN.商品略称
  ,SHOHIN.商品区分
  ,SHOHIN.売価単価
  ,SHOHIN.弁当区分
  ,SHOHIN.ｸﾞﾙｰﾌﾟ区分
  ,SHOHIN.主食ＣＤ
  ,MAIN.商品略称 AS 主食商品略称
  ,MAIN.商品区分 AS 主食商品区分
  ,SHOHIN.副食ＣＤ
  ,SUB.商品略称 AS 副食商品略称
  ,SUB.商品区分 AS 副食商品区分
  ,SHOHIN.修正担当者ＣＤ
  ,SHOHIN.修正日
  ,SHOHIN.表示ＦＬＧ
FROM
	商品マスタ SHOHIN
	LEFT OUTER JOIN 各種テーブル BUSHOGRP
		ON	BUSHOGRP.各種CD = 26
		AND ( SHOHIN.部署グループ = BUSHOGRP.サブ各種CD2 OR SHOHIN.部署グループ = 9 )
	LEFT OUTER JOIN 商品マスタ MAIN
		ON	MAIN.商品ＣＤ=SHOHIN.主食ＣＤ
	LEFT OUTER JOIN 商品マスタ SUB
		ON	SUB.商品ＣＤ=SHOHIN.副食ＣＤ
WHERE
	SHOHIN.表示ＦＬＧ != 1
AND 1 <= SHOHIN.商品区分 AND SHOHIN.商品区分 <= 7
AND BUSHOGRP.サブ各種CD1 = $BushoCd
),
主食副食一覧 AS (
SELECT DISTINCT
	CASE
		WHEN 主食ＣＤ = 0 AND 副食ＣＤ = 0 THEN
			商品ＣＤ
		WHEN 副食商品略称 IS NOT NULL THEN
			副食ＣＤ
		WHEN 主食商品略称 IS NOT NULL THEN
			主食ＣＤ
	END AS 商品ＣＤ,
	CASE
		WHEN 主食ＣＤ = 0 AND 副食ＣＤ = 0 THEN
			商品区分
		WHEN 副食商品略称 IS NOT NULL THEN
			副食商品区分
		WHEN 主食商品略称 IS NOT NULL THEN
			主食商品区分
	END AS 商品区分,
	CASE
		WHEN 主食ＣＤ = 0 AND 副食ＣＤ = 0 THEN
			商品略称
		WHEN 副食商品略称 IS NOT NULL THEN
			副食商品略称
		WHEN 主食商品略称 IS NOT NULL THEN
			主食商品略称
	END AS 商品略称
FROM
	商品一覧
)
SELECT
	各種テーブル.*,
	主食副食一覧.*
FROM
	各種テーブル
	INNER JOIN 主食副食一覧
		ON 主食副食一覧.商品ＣＤ = 各種テーブル.サブ各種CD1
WHERE
	各種CD = 45
AND	サブ各種CD2 IN (
    SELECT
		サブ各種CD2
	FROM 各種テーブル
	WHERE
		各種CD = 26 AND
		サブ各種CD1 = $BushoCd
)
ORDER BY
	行NO
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
        $DeliveryDate = $vm->DeliveryDate;
        $CourseKbn = $vm->CourseKbn;

        $sql = "
WITH TAISHO_SHOHIN AS (
	SELECT
		TSHO.商品ＣＤ
		,SHO.商品名
		,SHO.商品略称
		,SHO.商品区分
	FROM
		(
			SELECT
				SHOHIN.商品ＣＤ
			FROM
				商品マスタ SHOHIN
					LEFT OUTER JOIN 各種テーブル BUSHOGRP ON
						BUSHOGRP.各種CD = 26
						AND ( SHOHIN.部署グループ = BUSHOGRP.サブ各種CD2 OR SHOHIN.部署グループ = 9 )
			WHERE
				SHOHIN.表示ＦＬＧ = 0
				AND 1 <= SHOHIN.商品区分
				AND SHOHIN.商品区分 <= 7
				AND SHOHIN.主食ＣＤ = 0
				AND SHOHIN.副食ＣＤ = 0
				AND BUSHOGRP.サブ各種CD1 = $BushoCd
			UNION
			SELECT
				SHOHIN.主食ＣＤ AS 商品ＣＤ
			FROM
				商品マスタ SHOHIN
				LEFT OUTER JOIN 各種テーブル BUSHOGRP ON
					BUSHOGRP.各種CD = 26
					AND ( SHOHIN.部署グループ = BUSHOGRP.サブ各種CD2 OR SHOHIN.部署グループ = 9 )
			WHERE
				SHOHIN.表示ＦＬＧ = 0
				AND 1 <= SHOHIN.商品区分
				AND SHOHIN.商品区分 <= 7
				AND SHOHIN.主食ＣＤ != 0
				AND BUSHOGRP.サブ各種CD1 = $BushoCd
			UNION
			SELECT
				SHOHIN.副食ＣＤ AS 商品ＣＤ
			FROM
				商品マスタ SHOHIN
				LEFT OUTER JOIN 各種テーブル BUSHOGRP ON
					BUSHOGRP.各種CD = 26
					AND ( SHOHIN.部署グループ = BUSHOGRP.サブ各種CD2 OR SHOHIN.部署グループ = 9 )
			WHERE
				SHOHIN.表示ＦＬＧ = 0
				AND 1 <= SHOHIN.商品区分
				AND SHOHIN.商品区分 <= 7
				AND SHOHIN.副食ＣＤ != 0
				AND BUSHOGRP.サブ各種CD1 = $BushoCd
		) TSHO
		INNER JOIN 商品マスタ SHO
			ON	TSHO.商品ＣＤ = SHO.商品ＣＤ
			AND	TSHO.商品ＣＤ IN
				(
					SELECT
						サブ各種CD1
					FROM 各種テーブル
					WHERE 各種CD = 45
					AND サブ各種CD2 IN (
						SELECT サブ各種CD2 FROM 各種テーブル
						WHERE 各種CD = 26 AND サブ各種CD1 = $BushoCd

					)
				)
),
MOBILE_HANBAI AS(
	SELECT
		T1.部署ＣＤ
		,T1.コースＣＤ
		,T1.商品ＣＤ
		,SUM(CASE
			WHEN T1.実績入力 = 1 THEN T1.実績数
			WHEN T1.注文入力 = 1 THEN T1.注文数
			WHEN T1.見込入力 = 1 THEN T1.見込数
			ELSE 0
		END) 販売数
	FROM
	(
		SELECT
			SHO_SHU.部署ＣＤ
			,SHO_SHU.コースＣＤ
			,SHO_SHU.得意先ＣＤ
			,SHO_SHU.主食ＣＤ AS 商品ＣＤ
			,SHO_MST.商品区分
			,SHO_SHU.商品ＣＤ AS 入力判定用商品ＣＤ
			,SHO_SHU.実績入力
			,SHO_SHU.実績数
			,SHO_SHU.注文入力
			,SHO_SHU.注文数
			,SHO_SHU.見込入力
			,SHO_SHU.見込数
		FROM
			モバイル_販売入力 SHO_SHU
			LEFT OUTER JOIN 商品マスタ SHO_MST ON
				SHO_SHU.主食ＣＤ = SHO_MST.商品ＣＤ
		WHERE
			SHO_SHU.部署ＣＤ = $BushoCd
		AND SHO_SHU.日付 = '$DeliveryDate'
		AND SHO_SHU.主食ＣＤ != 0
		AND (SHO_SHU.実績数 != 0 OR SHO_SHU.注文数 != 0 OR SHO_SHU.見込数 != 0)
		UNION ALL
		SELECT
			SHO_FUKU.部署ＣＤ
			,SHO_FUKU.コースＣＤ
			,SHO_FUKU.得意先ＣＤ
			,SHO_FUKU.副食ＣＤ AS 商品ＣＤ
			,SHO_MST.商品区分
			,SHO_FUKU.商品ＣＤ AS 入力判定用商品ＣＤ
			,SHO_FUKU.実績入力
			,SHO_FUKU.実績数
			,SHO_FUKU.注文入力
			,SHO_FUKU.注文数
			,SHO_FUKU.見込入力
			,SHO_FUKU.見込数
		FROM
			モバイル_販売入力 SHO_FUKU
			LEFT OUTER JOIN 商品マスタ SHO_MST ON
				SHO_FUKU.副食ＣＤ = SHO_MST.商品ＣＤ
		WHERE
			SHO_FUKU.部署ＣＤ = $BushoCd
		AND SHO_FUKU.日付 = '$DeliveryDate'
		AND SHO_FUKU.副食ＣＤ != 0
		AND (SHO_FUKU.実績数 != 0 OR SHO_FUKU.注文数 != 0 OR SHO_FUKU.見込数 != 0)
	) T1
	LEFT OUTER JOIN (
		SELECT
			T1.部署ＣＤ
			,T1.コースＣＤ
			,T1.得意先ＣＤ
			,T1.商品ＣＤ
			,MAX(CAST(T1.実績入力 AS INT)) AS 実績入力
			,MAX(CAST(T1.見込入力 AS INT)) AS 見込入力
		FROM
			モバイル_販売入力 T1
		WHERE
			T1.部署ＣＤ = $BushoCd
		AND T1.日付 = '$DeliveryDate'
		GROUP BY
			T1.部署ＣＤ
			,T1.コースＣＤ
			,T1.得意先ＣＤ
			,T1.商品ＣＤ
	) T2 ON
		T1.部署ＣＤ = T2.部署ＣＤ
		AND T1.コースＣＤ = T2.コースＣＤ
		AND T1.得意先ＣＤ = T2.得意先ＣＤ
		AND T1.入力判定用商品ＣＤ = T2.商品ＣＤ
	LEFT OUTER JOIN (
		SELECT
			T1.部署ＣＤ
			,T1.コースＣＤ
			,T1.得意先ＣＤ
			,MAX(CAST(T1.注文入力 AS INT)) AS 注文入力
		FROM
			モバイル_販売入力 T1
		WHERE
			T1.部署ＣＤ = $BushoCd
			AND T1.日付 = '$DeliveryDate'
		GROUP BY
			T1.部署ＣＤ
			,T1.コースＣＤ
			,T1.得意先ＣＤ
	) T3 ON
		T1.部署ＣＤ = T3.部署ＣＤ
		AND T1.コースＣＤ = T3.コースＣＤ
		AND T1.得意先ＣＤ = T3.得意先ＣＤ
	WHERE
		(
			(T2.実績入力 = 1 AND T1.実績入力 = 1 AND T1.実績数 != 0)
			OR( T2.実績入力 = 0 AND T3.注文入力 = 1 AND T1.注文入力 = 1 AND T1.注文数 != 0)
			OR (T2.実績入力 = 0 AND T3.注文入力 = 0 AND T2.見込入力 = 1 AND T1.見込入力 = 1 AND T1.見込数 != 0)
		)
	GROUP BY
		T1.部署ＣＤ
		,T1.コースＣＤ
		,T1.商品ＣＤ
),
MOBILE_IDOU_WATASHI AS(
	SELECT
		T1.部署ＣＤ
		,T1.コースＣＤ
		,T1.商品ＣＤ
		,SUM(個数) AS 個数
	FROM
		モバイル_移動入力 T1
	WHERE
		T1.部署ＣＤ = $BushoCd AND T1.日付 = '$DeliveryDate'
	GROUP BY
		T1.部署ＣＤ
		,T1.コースＣＤ
		,T1.商品ＣＤ
),
MOBILE_IDOU_UKE AS(
	SELECT
		T1.部署ＣＤ
		,T1.相手コースＣＤ AS コースＣＤ
		,T1.商品ＣＤ
		,SUM(個数) AS 個数
	FROM
		モバイル_移動入力 T1
	WHERE
		T1.部署ＣＤ = $BushoCd AND T1.日付 = '$DeliveryDate'
	GROUP BY
		T1.部署ＣＤ
		,T1.相手コースＣＤ
		,T1.商品ＣＤ
)
SELECT
	1 AS チェック
	,CONVERT(VARCHAR,COU.コースＣＤ) AS コースＣＤ
	,COU.コース名
	,SHO.商品ＣＤ
	,ISNULL(MOB_MOTI.個数,0) - ISNULL(MOB_HAN.販売数,0) + ISNULL(MOB_IDO_UKE.個数,0) - ISNULL(MOB_IDO_WATASHI.個数,0) AS 個数
FROM
	[コースマスタ] COU
		INNER JOIN [担当者マスタ] TAN ON
			COU.担当者ＣＤ = TAN.担当者ＣＤ
		CROSS JOIN TAISHO_SHOHIN SHO
		LEFT OUTER JOIN MOBILE_HANBAI MOB_HAN ON
			COU.部署ＣＤ = MOB_HAN.部署ＣＤ
			AND COU.コースＣＤ = MOB_HAN.コースＣＤ
			AND SHO.商品ＣＤ = MOB_HAN.商品ＣＤ
		LEFT OUTER JOIN モバイル_持ち出し入力 MOB_MOTI ON
			COU.部署ＣＤ = MOB_MOTI.部署ＣＤ
			AND COU.コースＣＤ = MOB_MOTI.コースＣＤ
			AND SHO.商品ＣＤ = MOB_MOTI.商品ＣＤ
			AND MOB_MOTI.持ち出し日付 = '$DeliveryDate'
		LEFT OUTER JOIN MOBILE_IDOU_UKE MOB_IDO_UKE ON
			COU.部署ＣＤ = MOB_IDO_UKE.部署ＣＤ
			AND COU.コースＣＤ = MOB_IDO_UKE.コースＣＤ
			AND SHO.商品ＣＤ = MOB_IDO_UKE.商品ＣＤ
		LEFT OUTER JOIN MOBILE_IDOU_WATASHI MOB_IDO_WATASHI ON
			COU.部署ＣＤ = MOB_IDO_WATASHI.部署ＣＤ
			AND COU.コースＣＤ = MOB_IDO_WATASHI.コースＣＤ
			AND SHO.商品ＣＤ = MOB_IDO_WATASHI.商品ＣＤ
		LEFT OUTER JOIN 部署マスタ BUSHO ON
			COU.部署ＣＤ = BUSHO.部署ＣＤ
WHERE COU.部署ＣＤ = $BushoCd AND
    COU.コース区分 = $CourseKbn
ORDER BY
	COU.部署ＣＤ
	,COU.コースＣＤ
        ";

        $DataList = DB::select(DB::raw($sql));

        return response()->json($DataList);
    }
}
