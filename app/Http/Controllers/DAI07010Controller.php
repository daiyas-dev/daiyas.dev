<?php

namespace App\Http\Controllers;

use App\Models\売上データ明細;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB as FacadesDB;

class DAI07010Controller extends Controller
{
    /**
     * GetUriageDataList
     */
    public function GetUriageDataList($request)
    {
        $BushoCd = $request->BushoCd;
        $CourseCd = $request->CourseCd;
        $CustomerCd = $request->CustomerCd;
        $FirstDay = $request->FirstDay;
        $TargetDays = $request->TargetDays;

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
                    ,MAX(CASE
                        WHEN ISNULL(T2.日付,'') = '$date' THEN T2.修正日
                        ELSE NULL
                    END) AS '修正日_$date'
                ";

                return $col;
            })
            ->join("\n");

        $sql = "
            WITH SHOHINCD AS (
                SELECT
                    T1.商品CD,
					T3.商品区分,
                    $BushoCd AS 部署CD,
                    $CourseCd AS コースCD,
                    $CustomerCd AS 得意先CD
                FROM
                    得意先単価マスタ T1
					INNER JOIN 商品マスタ T3
						ON T3.商品ＣＤ=T1.商品ＣＤ
                WHERE
                    T1.得意先CD = $CustomerCd
                UNION
                SELECT
                    T2.商品CD,
					T2.商品区分,
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
				ISNULL(T2.売掛現金区分, M4.売掛現金区分) AS 売掛現金区分,
                T1.商品CD
                ,ISNULL(M1.商品名,'') AS 商品名
				,T1.商品区分
                ,M3.サブ各種CD1 AS 食事区分
                ,ISNULL(M3.各種名称,'') AS 食事区分名
				,T2.日付
				,T2.行Ｎｏ
				,T2.明細行Ｎｏ
				,T2.現金個数
				,T2.掛売個数
                ,CASE
                    WHEN M2.得意先CD IS NULL THEN ISNULL(M1.売価単価,0)
                    ELSE ISNULL(M2.単価,0)
                END AS 単価
				,T2.修正日
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
                LEFT OUTER JOIN 得意先マスタ M4 ON
                    M4.得意先CD = $CustomerCd
            ORDER BY
                T1.商品CD
        ";

        $DataList = FacadesDB::select($sql);

        return $DataList;
    }

    /**
     * CheckSeikyu
     */
    public function CheckSeikyu($request)
    {
        $TargetDate = $request->TargetDate;
        $CustomerCd = $request->CustomerCd;

        $sql = "
            SELECT
            	IIF('$TargetDate' <= 請求日付, 1, 0) AS 請求済
            FROM
                請求データ
            WHERE 請求データ.請求先ＣＤ = $CustomerCd
            AND 請求日付 = (
                SELECT MAX(請求日付) FROM 請求データ DUM
                WHERE DUM.請求先ＣＤ = $CustomerCd )
        ";

        $Result = DB::selectOne($sql);
        return !!$Result ? $Result->請求済 : 0;
    }

    /**
     * CheckKaikei
     */
    public function CheckKaikei($request)
    {
        $TargetDate = $request->TargetDate;
        $CustomerCd = $request->CustomerCd;

        $sql = "
            SELECT
                IIF('$TargetDate' <= EOMONTH(日付), 1, 0) AS 会計処理済
            FROM
                売掛データ
            WHERE 売掛データ.請求先ＣＤ = $CustomerCd
            AND 日付 = (
                SELECT MAX(日付) FROM 売掛データ DUM
                WHERE DUM.請求先ＣＤ = $CustomerCd )
        ";

        $Result = DB::selectOne($sql);
        return !!$Result ? $Result->会計処理済 : 0;
    }

    /**
     * GetData
     */
    public function GetData($request)
    {
        return [
            [
                "UriageDataList" => $this->GetUriageDataList($request),
                "CheckSeikyu" => $this->CheckSeikyu($request),
                "CheckKaikei" => $this->CheckKaikei($request),
            ]
        ];
    }

    /**
     * Search
     */
    public function Search($request)
    {
        return response()->json($this->GetData($request));
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
        $skip = [];

        FacadesDB::beginTransaction();

        try {
            $params = $request->all();

            $SaveList = $params['SaveList'];

            $date = Carbon::now()->format('Y-m-d H:i:s');
            foreach ($SaveList as $rec) {
                if (isset($rec['修正日']) && !!$rec['修正日']) {
                    $r = 売上データ明細::query()
                        ->where('日付', $rec['日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('コースＣＤ', $rec['コースＣＤ'])
                        ->where('行Ｎｏ', $rec['行Ｎｏ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                        ->where('受注Ｎｏ', $rec['受注Ｎｏ'])
                        ->get();

                    if (count($r) != 1) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => null]);
                        continue;
                    } else if ($rec['修正日'] != $r[0]->修正日) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                        continue;
                    }

                    $rec['修正日'] = $date;
                    $rec['請求日付'] = $rec['請求日付'] ?? '';


                    //個数が0の場合は削除扱い
                    if ($rec['現金個数'] == '0' && $rec['掛売個数'] == '0') {
                        売上データ明細::query()
                            ->where('日付', $rec['日付'])
                            ->where('部署ＣＤ', $rec['部署ＣＤ'])
                            ->where('コースＣＤ', $rec['コースＣＤ'])
                            ->where('行Ｎｏ', $rec['行Ｎｏ'])
                            ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                            ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                            ->where('受注Ｎｏ', $rec['受注Ｎｏ'])
                            ->delete($rec);
                    } else {
                        売上データ明細::query()
                            ->where('日付', $rec['日付'])
                            ->where('部署ＣＤ', $rec['部署ＣＤ'])
                            ->where('コースＣＤ', $rec['コースＣＤ'])
                            ->where('行Ｎｏ', $rec['行Ｎｏ'])
                            ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                            ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                            ->where('受注Ｎｏ', $rec['受注Ｎｏ'])
                            ->update($rec);
                    }
                } else {
                    $gno = 売上データ明細::query()
                        ->where('日付', $rec['日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('コースＣＤ', $rec['コースＣＤ'])
                        ->max('行Ｎｏ') + 1;

                    $mno = 売上データ明細::query()
                        ->where('日付', $rec['日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('コースＣＤ', $rec['コースＣＤ'])
                        ->where('行Ｎｏ', $rec['行Ｎｏ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->max('明細行Ｎｏ') + 1;

                    $rec['修正日'] = $date;
                    $rec['行Ｎｏ'] = $gno;
                    $rec['明細行Ｎｏ'] = $mno;
                    $rec['請求日付'] = $rec['請求日付'] ?? '';

                    売上データ明細::insert($rec);
                }
            }

            FacadesDB::commit();

        } catch (Exception $exception) {
            FacadesDB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            "edited" => count($skip) > 0,
            "current" => $this->GetData($request),
        ]);
    }
}
