<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI01160Controller extends Controller
{

    /**
     *  ColumSerch
     */

     public function ColSearch($vm)
     {
        $BushoCd = $vm->BushoCd;
        $sql = "SELECT
                    *
                    FROM 各種テーブル
                    where 各種CD=
                    (select
                    IIF(サブ各種CD2=1,24,IIF(サブ各種CD2=2,25,IIF(サブ各種CD2=3,41,NULL)))
                    FROM 各種テーブル
                    WHERE
                    各種CD = 26
                    and サブ各種CD1 =". $BushoCd.")
                    order by 各種CD,行NO";


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

        $IsIncludeJuchu = $vm->IsIncludeJuchu;
        $Biko = ($IsIncludeJuchu == 1) ? "chumon.備考１+ chumon.備考２ + chumon.備考３ + chumon.備考４" : "tokui.備考１" ;

        $sql = "
WITH コース区分判定 AS (
	SELECT
		(CASE
			WHEN
				(
					SELECT
						対象日付
					FROM
						祝日マスタ
					WHERE
						CONVERT(VARCHAR, 対象日付, 112)='$DeliveryDate') IS NOT NULL
			THEN
				'4'
			ELSE
				CASE DATEPART (WEEKDAY , '$DeliveryDate')
					WHEN '1' THEN '3'
					WHEN '7' THEN '2'
					ELSE '1'
				END
		END) AS 区分
),
得意先単価マスタデータ AS (
    SELECT
        *
    FROM (
        SELECT
            *
            , RANK() OVER(PARTITION BY 得意先ＣＤ, 商品ＣＤ ORDER BY 適用開始日 DESC) AS RNK
        FROM
            得意先単価マスタ新
        WHERE
        適用開始日 <= '$DeliveryDate'
    ) TT
    WHERE
        RNK = 1
),
得意先別注文データ AS (
	select
		chumon.*
	from 注文データ chumon
	where
		部署ＣＤ=$BushoCd
		and chumon.注文区分 = 0
        AND chumon.注文日付 = '$DeliveryDate'
        AND chumon.現金個数 + chumon.掛売個数 > 0
),
単価表示商品 AS (
	SELECT
		サブ各種CD1 AS 単価表示商品CD
	FROM 各種テーブル
	WHERE 各種CD=(
		SELECT
			IIF(サブ各種CD2=1,24,IIF(サブ各種CD2=2,25,IIF(サブ各種CD2=3,41,NULL)))
		FROM 各種テーブル
		WHERE
			各種CD = 26
		AND サブ各種CD1 =$BushoCd
	)
	AND サブ各種CD2 =1
),
得意先単価AGG AS (
	SELECT
		T1.得意先ＣＤ,
		'{' + STUFF(
			(
				SELECT
					',\"' + CAST(T2.商品ＣＤ AS VARCHAR(5)) + '\":' + CAST(T2.単価 AS VARCHAR(10))
				FROM (
					SELECT
						*
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
								得意先ＣＤ=T1.得意先ＣＤ
							AND 適用開始日 <= '$DeliveryDate'
                        ) TT
                        WHERE
                            RNK = 1
                        ) TT
						INNER JOIN 	単価表示商品 TP
							on TT.商品ＣＤ = TP.単価表示商品CD
				) AS T2
				WHERE
					T2.得意先ＣＤ = T1.得意先ＣＤ
				ORDER BY 得意先ＣＤ
					FOR XML PATH(''), TYPE
			).value('.', 'VARCHAR(MAX)'),
			1,
			1,
			''
		) + '}'
		AS 得意先単価JSON
	FROM (
		SELECT
			*
		FROM
            得意先単価マスタデータ TT
			INNER JOIN 	単価表示商品 TP
				on TT.商品ＣＤ = TP.単価表示商品CD
	) AS T1
	GROUP BY
		T1.得意先ＣＤ
)
SELECT
	coutbl.ＳＥＱ 順,
	busyo.部署名,
	busyo.電話番号 AS 会社電話番号,
	busyo.会社名称,
	busyo.FAX AS 会社FAX,
	coutbl.部署ＣＤ,
	coutbl.コースＣＤ,
	cou.コース名,
	coutbl.得意先ＣＤ,
	tokui.得意先名略称 AS 得意先名,
	tokui.受注方法,
	case
		when tokui.受注方法 = 3 then 'F'
		when tokui.受注方法 = 1 then 'T'
		when tokui.受注方法 = 2 then 'W'
		when tokui.受注方法 = 0 then 'K'
		else
			''
	END 受注方法表示,
	tokui.電話番号１ 電話番号,
	tokui.締区分,
	case
		when
			tokui.締日１ > 0 and tokui.締日１ != 99
		then
			convert(varchar, tokui.締日１)
		when
			tokui.締日１ > 0 and tokui.締日１ = 99
		then '末'
		when tokui.締日１ = 0
		then '日'
	end 締方法,
	$Biko AS 備考１,
	chumon.商品ＣＤ,
	chumon.予備金額１ as 単価,
	isNULL(chumon.現金個数, 0) + isnull(chumon.掛売個数, 0) 個数,
	isNULL(chumon.現金金額, 0) + isnull(chumon.掛売金額, 0) 金額,
	case
		when
		tokui.ふりかけ区分 = 1
		then 'ﾌ'
	end ふりかけ,
	case
		when tokui.味噌汁区分 = 1 then 'ﾐ'
	end みそしる
	,TTAGG.得意先単価JSON
FROM
コーステーブル coutbl
	left join コースマスタ cou
		on cou.コースＣＤ = coutbl.コースＣＤ and cou.部署ＣＤ = coutbl.部署ＣＤ
	INNER JOIN コース区分判定 CKJ
		ON CKJ.区分 = cou.コース区分
	left join 得意先マスタ tokui
		on coutbl.得意先ＣＤ = tokui.得意先ＣＤ and coutbl.部署ＣＤ = tokui.部署ＣＤ
	left join 部署マスタ busyo
		on coutbl.部署ＣＤ = busyo.部署CD
	left join 得意先別注文データ chumon
        on coutbl.得意先ＣＤ = chumon.得意先ＣＤ
	left join 得意先単価AGG TTAGG
        on TTAGG.得意先ＣＤ = coutbl.得意先ＣＤ
where 0=0
AND coutbl.部署ＣＤ =$BushoCd
order by
	coutbl.部署ＣＤ,
	coutbl.コースＣＤ,
	coutbl.ＳＥＱ
";

        $DataList = DB::select(DB::raw($sql));

        return response()->json($DataList);
    }
}
