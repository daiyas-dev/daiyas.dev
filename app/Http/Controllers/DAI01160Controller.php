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

     public function ColSerch($vm)
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
        $CourseKbn = $vm->CourseKbn;
        $CourseStart = $vm->CourseStart ?? 0;
        $CourseEnd = $vm->CourseEnd ?? 9999;
        $DeliveryDate = $vm->DeliveryDate;

        $WhereBusho = $BushoCd ? " AND coutbl.部署ＣＤ =$BushoCd" : "";
        $WhereCourseStart = $CourseStart ? " AND $CourseStart <= coutbl.コースＣＤ" : "";
        $WhereCourseEnd = $CourseEnd ? " AND coutbl.コースＣＤ <= $CourseEnd" : "";
        $WhereCourseKbn = $CourseKbn ? " AND cou.コース区分 = $CourseKbn" : "";

        $sql = "
WITH 単価表示商品 AS (
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
	chumon.備考１+ chumon.備考２ + chumon.備考３ + chumon.備考４ AS 備考１,
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
	,tktanka.商品ＣＤ
	,sm.商品名
	,tktanka.単価
FROM
コーステーブル coutbl
	left join コースマスタ cou
		on cou.コースＣＤ = coutbl.コースＣＤ and cou.部署ＣＤ = coutbl.部署ＣＤ
	left join 得意先マスタ tokui
		on coutbl.得意先ＣＤ = tokui.得意先ＣＤ and coutbl.部署ＣＤ = tokui.部署ＣＤ
	left join 部署マスタ busyo
		on coutbl.部署ＣＤ = busyo.部署CD
	left join 注文データ chumon
		on coutbl.得意先ＣＤ = chumon.得意先ＣＤ and coutbl.部署ＣＤ = chumon.部署ＣＤ and chumon.注文区分 = 0 AND CONVERT(varchar, chumon.注文日付, 112) = '20190903'AND chumon.現金個数 + chumon.掛売個数 > 0
	left join 得意先単価マスタ tktanka
		on tktanka.得意先ＣＤ = tokui.得意先ＣＤ AND tktanka.商品ＣＤ IN (SELECT 単価表示商品CD FROM 単価表示商品)
	left join 商品マスタ sm
		on sm.商品ＣＤ=tktanka.商品ＣＤ
where 0=0
$WhereBusho
$WhereCourseStart
$WhereCourseEnd
$WhereCourseKbn
order by
	coutbl.部署ＣＤ,
	coutbl.コースＣＤ,
	coutbl.ＳＥＱ
";

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }
}
