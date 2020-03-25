<?php

namespace App\Http\Controllers;

use App\Models\コーステーブル;
use App\Models\各種テーブル;
use App\Models\商品マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Facades\DB as IlluminateDB;
use SebastianBergmann\Environment\Console;

class DAI01210Controller extends Controller
{
        /**
     *  ColumSerch
     */
    public function ColSearch($vm)
    {
        $BushoCd = $vm->BushoCd;
        $sql = "
            SELECT
                行NO AS 商品区分,
                各種名称
            FROM 各種テーブル
            WHERE 各種CD=
                (
                    SELECT
                        IIF(サブ各種CD2=2, 27, 14)
                    FROM 各種テーブル
                    WHERE 各種CD=26
                    AND サブ各種CD1=$BushoCd
                )
                AND 行NO<=7
            ORDER BY 各種CD,行NO
        ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }

    /**
     * GetDataList
     */
    public function GetDataList($request)
    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;
        $CourseKbn = $request->CourseKbn;
        $CourseStart = $request->CourseStart;
        $CourseEnd = $request->CourseEnd;
        $CourseStart = !!$CourseStart ? $CourseStart : "0";
        $CourseEnd = !!$CourseEnd ? $CourseEnd : "9999";

        $sql = "
DECLARE @日付WK              DATETIME
set @日付WK  = CONVERT(datetime, '$TargetDate', $BushoCd);

WITH
部署マスタ AS (
	SELECT * FROM 各種テーブル WHERE 各種テーブル.各種CD = 26 AND 各種テーブル.サブ各種CD1 = $BushoCd
),
商品区分マスタ AS (
	SELECT * FROM 各種テーブル WHERE 各種CD = 14
),
売上データ AS (
	SELECT
	uriage.日付,
	uriage.コースＣＤ,
	uriage.商品区分,
	SUM(uriage.現金個数) AS 現金個数,
	SUM(uriage.現金金額) AS 現金金額,
	SUM(uriage.掛売個数) AS 掛売個数,
	SUM(uriage.掛売金額) AS 掛売金額,
	SUM(uriage.分配元数量) 分配元数量 ,
	uriage.売掛現金区分
	FROM [売上データ明細] uriage
	inner join 得意先マスタ tokui on tokui.得意先ＣＤ = uriage.得意先ＣＤ and tokui.部署CD = uriage.部署ＣＤ
	WHERE
	uriage.部署ＣＤ = $BushoCd
	AND (tokui.得意先ＣＤ = tokui.受注得意先ＣＤ OR tokui.受注得意先ＣＤ = 0)
	AND (日付 >= '2019/08/01' AND 日付 <= '2019/08/31')
	group by uriage.日付,uriage.コースＣＤ,uriage.商品区分, uriage.売掛現金区分
),
抽出データ AS (
	SELECT コースマスタ.コースＣＤ AS コースKEY, 商品区分マスタ.サブ各種CD2 AS 商品KEY, DAY(日付) as 日付, 掛売個数
	FROM コースマスタ
	INNER JOIN 担当者マスタ ON コースマスタ.担当者ＣＤ = 担当者マスタ.担当者ＣＤ
	LEFT JOIN 売上データ ON コースマスタ.コースＣＤ = 売上データ.コースＣＤ
	LEFT JOIN 商品区分マスタ ON 売上データ.商品区分 = 商品区分マスタ.サブ各種CD2
	WHERE
		コースマスタ.コースＣＤ >= $CourseStart
	AND コースマスタ.コースＣＤ <= $CourseEnd
	AND コースマスタ.部署ＣＤ = $BushoCd AND コースマスタ.コース区分 = $CourseKbn
),
日毎抽出データ AS (
	select * from 抽出データ
	PIVOT ( SUM( 掛売個数 ) FOR 日付 in([1], [2], [3], [4], [5], [6], [7], [8], [9], [10],
										[11], [12], [13], [14], [15], [16], [17], [18], [19], [20],
										[21], [22], [23], [24], [25], [26], [27], [28], [29], [30], [31]
										)) AS ピボットテーブル
),
日毎合計抽出データ AS (
	select コースKEY, 商品KEY, SUM(掛売個数) as 合計
	from 抽出データ
	group by コースKEY, 商品KEY
),
日毎平均抽出データ AS (
	select コースKEY, 商品KEY, SUM(掛売個数) / 31 as 平均
	from 抽出データ
	group by コースKEY, 商品KEY
),
土曜合計抽出データ AS (
	select コースKEY, 商品KEY, SUM(掛売個数) as 土曜合計
	from 抽出データ
	where DATEPART(WEEKDAY,日付) = 7
	group by コースKEY, 商品KEY
),
土曜平均抽出データ AS (
	select コースKEY, 商品KEY, SUM(掛売個数) / 31 as 土曜平均
	from 抽出データ
	where DATEPART(WEEKDAY,日付) = 7
	group by コースKEY, 商品KEY
),
日曜合計抽出データ AS (
	select コースKEY, 商品KEY, SUM(掛売個数) as 日曜合計
	from 抽出データ
	where DATEPART(WEEKDAY,日付) = 1
	group by コースKEY, 商品KEY
),
日曜平均抽出データ AS (
	select コースKEY, 商品KEY, SUM(掛売個数) / 31 as 日曜平均
	from 抽出データ
	where DATEPART(WEEKDAY,日付) = 1
	group by コースKEY, 商品KEY
),
集計データ AS (
	select コースマスタ.コースＣＤ, コースマスタ.コース名, 商品区分マスタ.サブ各種CD2 AS 商品CD, 商品区分マスタ.各種名称 AS 商品名 from
	(
	select * from コースマスタ	WHERE
		コースマスタ.コースＣＤ >= $CourseStart
	AND コースマスタ.コースＣＤ <= $CourseEnd
	AND コースマスタ.部署ＣＤ = $BushoCd AND コースマスタ.コース区分 = $CourseKbn
	) コースマスタ
	CROSS JOIN 商品区分マスタ
)

select 集計データ.*, 日毎抽出データ.*, 合計, 平均, 土曜合計, 土曜平均, 日曜合計, 日曜平均
from 集計データ
left join 日毎抽出データ on 集計データ.コースＣＤ = 日毎抽出データ.コースKEY and 集計データ.商品CD = 日毎抽出データ.商品KEY
left join 日毎合計抽出データ on 集計データ.コースＣＤ = 日毎合計抽出データ.コースKEY and 集計データ.商品CD = 日毎合計抽出データ.商品KEY
left join 日毎平均抽出データ on 集計データ.コースＣＤ = 日毎平均抽出データ.コースKEY and 集計データ.商品CD = 日毎平均抽出データ.商品KEY
left join 土曜合計抽出データ on 集計データ.コースＣＤ = 土曜合計抽出データ.コースKEY and 集計データ.商品CD = 土曜合計抽出データ.商品KEY
left join 土曜平均抽出データ on 集計データ.コースＣＤ = 土曜平均抽出データ.コースKEY and 集計データ.商品CD = 土曜平均抽出データ.商品KEY
left join 日曜合計抽出データ on 集計データ.コースＣＤ = 日曜合計抽出データ.コースKEY and 集計データ.商品CD = 日曜合計抽出データ.商品KEY
left join 日曜平均抽出データ on 集計データ.コースＣＤ = 日曜平均抽出データ.コースKEY and 集計データ.商品CD = 日曜平均抽出データ.商品KEY
order by 集計データ.コースＣＤ, 集計データ.商品CD
        ";

        $DataList = DB::select($sql);
        return $DataList;
    }

    /**
     * Search
     */
    public function Search($request) {
        return response()->json($this->GetDataList($request));
    }

    /**
     * UpdateCheck
     */
    public function UpdateCheck($request)
    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;
        $UpdateDate = $request->UpdateDate;

        $sql = "
SELECT
	MAX(最新修正日時) AS 最新修正日時
FROM
(
	SELECT
		MAX(修正日) AS 最新修正日時
	FROM モバイル_持ち出し入力
	WHERE
    	部署ＣＤ= $BushoCd
	AND	持ち出し日付 = '$TargetDate'
	UNION
	SELECT
		MAX(修正日) AS 最新修正日時
	FROM モバイル_販売入力
	WHERE
	    部署ＣＤ= $BushoCd
	AND	日付 = '$TargetDate'
	UNION
	SELECT
		MAX(修正日) AS 最新修正日時
	FROM モバイル_移動入力
	WHERE
    	部署ＣＤ= $BushoCd
	AND	日付 = '$TargetDate'
) MOBILE
        ";

        $Result = DB::selectOne($sql);

        if (!!$UpdateDate && $Result->最新修正日時 != $UpdateDate) {
            $Result->list = $this->GetDataList($request);
            return response()->json($Result);
        } else {
            return response()->json($Result);
        }
    }
}
