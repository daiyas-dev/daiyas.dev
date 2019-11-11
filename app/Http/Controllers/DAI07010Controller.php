<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI07010Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $BushoCd = $vm->BushoCd;
        $CourseCd = $vm->CourseCd;
        $CustomerCd = $vm->CustomerCd;
        $FirstDay = $vm->FirstDay;
        $TargetDays = $vm->TargetDays;

        $LastDay = (new Carbon($FirstDay))->addDays($TargetDays)->format('Ymd');

        $selects = collect(range(0, $TargetDays))
            ->map(function ($n) use ($FirstDay) {
                $first = new Carbon($FirstDay);
                $date = $first->addDays($n)->format('Ymd');

                $col = "
	,SUM(CASE
		 WHEN ISNULL(T2.日付,'') = '$date' THEN ISNULL(T2.現金個数,0) + ISNULL(T2.掛売個数,0)
		 ELSE 0
	 END) AS '$date'
                ";

                return $col;
            })
            ->join("\n");

        $sql = "
WITH SHOHINCD AS (
	SELECT
		T1.商品CD,
		$BushoCd AS 部署CD,
		$CourseCd AS コースCD,
		$CustomerCd AS 得意先CD
	FROM
		得意先単価マスタ T1
	WHERE
		T1.得意先CD = $CustomerCd
	UNION
	SELECT
		T2.商品CD,
		T2.部署CD,
		T2.コースCD,
		T2.得意先CD
	FROM
		売上データ明細 T2
	WHERE
		T2.日付 BETWEEN '$FirstDay' AND '$LastDay'
		AND T2.部署CD = $BushoCd
		AND T2.コースCD = $CourseCd
		AND T2.得意先CD = $CustomerCd
)
SELECT
	T1.部署CD,
	T1.コースCD,
	T1.得意先CD,
	T1.商品CD
	,ISNULL(M1.商品名,'') AS 商品名
    ,ISNULL(M3.各種名称,'') AS 食事区分名
    $selects
	,CASE
		 WHEN M2.得意先CD IS NULL THEN ISNULL(M1.売価単価,0)
		 ELSE ISNULL(M2.単価,0)
	 END AS 単価
FROM
	SHOHINCD T1
	LEFT OUTER JOIN 売上データ明細 T2 ON
		T2.日付 BETWEEN '$FirstDay' AND '$LastDay'
		AND T2.部署CD = $BushoCd
		AND T2.得意先CD = $CustomerCd
		AND T1.商品CD = T2.商品CD
	LEFT OUTER JOIN 商品マスタ M1 ON
		T1.商品CD = M1.商品CD
	LEFT OUTER JOIN 得意先単価マスタ M2 ON
		T1.商品CD = M2.商品CD
		AND M2.得意先CD = $CustomerCd
	LEFT OUTER JOIN 各種テーブル M3 ON
		M3.各種CD = 38
		AND M3.サブ各種CD1 = M1.食事区分
GROUP BY
	T1.部署CD,
	T1.コースCD,
	T1.得意先CD,
	T1.商品CD
	,M1.商品名
	,M3.各種名称
	,M2.得意先CD
	,M1.売価単価
	,M2.単価
ORDER BY
	T1.商品CD
        ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }

    /**
     * Upload
     */
    public function Upload($request)
    {
        $params = $request->all();
        $file = $request->file('file');

        //TODO: dummy
        $ret = [
            'BushoCd' => 501,
            'CourseCd' => 101,
            'CustomerCd' => 11271,
            'Array' => [
                [
                    "20191111" => "2",
                    "20191112" => "2",
                    "20191113" => "0",
                    "20191114" => "2",
                    "20191115" => "0",
                    "20191116" => "0",
                    "商品CD" => "12",
                ],
                [
                    "20191111" => "1",
                    "20191112" => "1",
                    "20191113" => "0",
                    "20191114" => "1",
                    "20191115" => "0",
                    "20191116" => "0",
                    "商品CD" => "406",
                ]
            ],
        ];

        return $ret;
    }

    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();
        $targets = $params['targets'];

        //validation
        validator()->validate($targets, [
            'targets.*.*' => 'required',
        ]);

        //トランザクション開始
        DB::transaction(function () use ($targets) {
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

        return response()->json([
            'result' => true,
        ]);
    }
}
