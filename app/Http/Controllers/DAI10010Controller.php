<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use App\Models\売上データ明細;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use PDO;

class DAI10010Controller extends Controller
{

    /**
     * GetProductList
     */
    public function GetProductList($request)
    {
        $CustomerCd = $request->CustomerCd;
        $TargetDate = $request->TargetDate;
        //指定日付の得意先単価を取得するSQL
        $sql_mtt="(
                    SELECT
                        *
                    FROM (
                        SELECT
                            *
                            , RANK() OVER(PARTITION BY 得意先ＣＤ, 商品ＣＤ ORDER BY 適用開始日 DESC) AS RNK
                        FROM
                            得意先単価マスタ新
                        WHERE
                            得意先ＣＤ=$CustomerCd
                        AND 適用開始日 <= '$TargetDate'
                    ) TT
                    WHERE
                        RNK = 1
                ) MTT";

        //売上データ明細に得意先単価未登録の商品が存在するか確認
        /*
        $sql_62="SELECT COUNT(*)AS CNT FROM 売上データ明細 UDM
                    WHERE UDM.日付='$TargetDate'
                    AND UDM.得意先ＣＤ=$CustomerCd
                    AND NOT EXISTS(SELECT 1 FROM $sql_mtt WHERE MTT.商品ＣＤ=UDM.商品ＣＤ)
                ";
        $exists_prod62 = DB::selectOne($sql_62)->CNT;
        */
        $exists_prod62=1;

        $sql = "
            SELECT
                MTT.商品ＣＤ AS Cd,
                PM.商品名 AS CdNm,
                MTT.得意先ＣＤ,
                MTT.商品ＣＤ,
                PM.商品名,
                PM.商品区分,
                PM.主食ＣＤ,
                PM.副食ＣＤ,
                MTT.単価 AS 売価単価
            FROM
                $sql_mtt
                LEFT OUTER JOIN 商品マスタ PM
                    ON	PM.商品ＣＤ=MTT.商品ＣＤ
        ";
        if (0<$exists_prod62) {
            //得意先単価未登録の商品も追加
            $sql.="
                UNION SELECT
                     M.商品ＣＤ
                    ,M.商品名
                    ,$CustomerCd
                    ,M.商品ＣＤ
                    ,M.商品名
                    ,M.商品区分
                    ,M.主食ＣＤ
                    ,M.副食ＣＤ
                    ,M.売価単価
                FROM 商品マスタ M
                WHERE ((M.弁当区分 in(0,8,9) AND M.表示ＦＬＧ=0) OR M.商品ＣＤ IN(25,39))
                AND NOT EXISTS(SELECT 1 FROM $sql_mtt WHERE MTT.商品ＣＤ=M.商品ＣＤ)
            ";
        }

        $Result = DB::select($sql);

        return response()->json($Result);
    }

    /**
     * GetSeikyuData
     */
    public function GetSeikyuData($request)
    {
        $CustomerCd = $request->CustomerCd;
        $sql = "
            SELECT TOP(1)
                請求日付
            FROM
                請求データ
            WHERE 請求データ.請求先ＣＤ = $CustomerCd
            ORDER BY 請求日付 DESC
            ";
        $Result = DB::select($sql);
        return response()->json($Result);
    }

    //2020/10/09 追加 売掛データ(月次集計からの登録)
    public function GetUrikakeData($request)
    {
        $CustomerCd = $request->CustomerCd;
        $TargetDate = $request->TargetDate;
        $TargetDate = new \DateTime($TargetDate);
        $TargetDate = date_format($TargetDate, 'Y-m-01');
        $sql = "
            SELECT TOP(1)
                日付
            FROM
                売掛データ
            WHERE 売掛データ.請求先ＣＤ = $CustomerCd
            AND 日付 = '$TargetDate'
            ORDER BY 日付 DESC
            ";
        $Result = DB::select($sql);
        return response()->json($Result);
    }

    /**
     * GetTodayOrder
     */
    public function GetTodayOrder($request) {
        $TantoCd = $request->TantoCd;

        $sql = "
SELECT
	ROW_NUMBER() OVER (ORDER BY CONVERT(varchar, 修正日, 108) desc) AS no,
	注文区分,
	注文日付,
	部署ＣＤ,
	得意先CD,
	配送日,
	COUNT(得意先CD) AS 件数,
	CONVERT(varchar, 修正日, 108) 修正時間
FROM
	注文データ
WHERE
	CONVERT(varchar, 修正日, 112) = CONVERT(date, GETDATE())
	AND 修正担当者CD=$TantoCd
GROUP BY
	注文区分,
	注文日付,
	部署ＣＤ,
	得意先CD,
	配送日,
	CONVERT(varchar, 修正日, 108)
ORDER BY
	CONVERT(varchar, 修正日, 108) DESC
        ";

        $Result = DB::select($sql);

        return response()->json($Result);

    }

    /**
     * Search
     */
    public function Search($vm)
    {
        $pvm = clone $vm;

        if (isset($vm->ParentCustomerCd)) {
            $pvm->CustomerCd = $vm->ParentCustomerCd;
        }

        return response()->json(
            [
                "SalesList" => $this->GetSalesList($vm),
                "ProductList" => $this->GetProductList($pvm)->original,
                "UrikakeList" => $this->GetUrikakeData($vm)->original,
            ]
        );
    }

    /**
     * GetSalesList
     */
    public function GetSalesList($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDate = $vm->TargetDate;
        $CustomerCd = $vm->CustomerCd;
        $CourseCd = $vm->CourseCd;
        $WhereCourseCd = isset($CourseCd) ? " AND 売上データ明細.コースＣＤ = $CourseCd " : "";

        $sql = "
SELECT
	売上データ明細.コースＣＤ,
	コースマスタ.コース名,
	コーステーブル.ＳＥＱ,
	コーステーブル.得意先ＣＤ,
	得意先マスタ.得意先名略称 AS 得意先名,
	支払方法.行NO AS 売掛現金区分,
	支払方法.各種名称 AS 売掛現金区分名称,
	得意先マスタ.売掛現金区分 AS 得意先売掛現金区分,
	得意先支払方法.各種名称 AS 得意先売掛現金区分名称,
	売上データ明細.日付,
	売上データ明細.行Ｎｏ,
	売上データ明細.明細行Ｎｏ,
	売上データ明細.受注Ｎｏ,
	売上データ明細.商品ＣＤ,
	売上データ明細.現金個数,
	売上データ明細.現金金額,
	売上データ明細.現金値引,
	売上データ明細.現金値引事由ＣＤ,
	売上データ明細.掛売個数,
	売上データ明細.掛売金額,
	売上データ明細.掛売値引,
	売上データ明細.掛売値引事由ＣＤ,
	売上データ明細.請求日付,
	売上データ明細.予備金額１,
	売上データ明細.予備金額２,
	売上データ明細.予備ＣＤ２,
	売上データ明細.売掛現金区分 AS 売上売掛現金区分,
	売上支払方法.各種名称 AS 売上売掛現金区分名称,
    商品マスタ.商品区分,
    商品マスタ.商品名,
	売上データ明細.備考,
	売上データ明細.分配元数量,
	売上データ明細.修正日,
    CASE WHEN 売上データ明細.食事区分 IS NULL OR 売上データ明細.食事区分 = 0 THEN 商品マスタ.食事区分 ELSE 売上データ明細.食事区分 END 食事区分,
    CASE WHEN 売上データ明細.主食ＣＤ = 0 THEN 商品マスタ.主食ＣＤ ELSE 売上データ明細.主食ＣＤ END 主食ＣＤ,
    CASE WHEN 売上データ明細.副食ＣＤ = 0 THEN 商品マスタ.副食ＣＤ ELSE 売上データ明細.副食ＣＤ END 副食ＣＤ,
    請求.請求日付,
    請求.今回請求額
FROM
	[得意先マスタ]
		LEFT OUTER JOIN 各種テーブル 支払方法
			ON 支払方法.各種CD=1
		LEFT OUTER JOIN [売上データ明細]
			ON 売上データ明細.日付 = '$TargetDate'
			AND 売上データ明細.部署ＣＤ = 得意先マスタ.部署ＣＤ
			AND 売上データ明細.得意先ＣＤ = 得意先マスタ.得意先ＣＤ
			AND (
				(支払方法.行NO = 0 AND 売上データ明細.売掛現金区分 = 0)
				OR
				(支払方法.行NO != 0 AND 売上データ明細.売掛現金区分 != 0)
			)
		LEFT OUTER JOIN [コースマスタ]
			ON コースマスタ.コースＣＤ = 売上データ明細.コースＣＤ
			AND コースマスタ.部署ＣＤ = 売上データ明細.部署ＣＤ
		LEFT OUTER JOIN [コーステーブル]
			ON コーステーブル.コースＣＤ = コースマスタ.コースＣＤ
			AND コーステーブル.得意先ＣＤ = 売上データ明細.得意先ＣＤ
			AND コーステーブル.部署ＣＤ = コースマスタ.部署ＣＤ
		LEFT OUTER JOIN 各種テーブル 得意先支払方法
			ON 得意先支払方法.各種CD=1
			AND 得意先支払方法.行NO = 得意先マスタ.売掛現金区分
		LEFT OUTER JOIN 各種テーブル 売上支払方法
			ON 売上支払方法.各種CD=1
			AND 売上支払方法.行NO = 売上データ明細.売掛現金区分
		LEFT OUTER JOIN [商品マスタ]
            ON 商品マスタ.商品ＣＤ = 売上データ明細.商品ＣＤ
        LEFT OUTER JOIN (
            SELECT TOP(1)
                請求先ＣＤ,
                請求日付,
                今回請求額
            FROM
                請求データ
            WHERE 請求データ.請求先ＣＤ = $CustomerCd
            ORDER BY 請求日付 DESC
        ) 請求
            ON 請求.請求先ＣＤ = 得意先マスタ.得意先ＣＤ
WHERE
    得意先マスタ.部署ＣＤ = $BushoCd
AND 得意先マスタ.得意先ＣＤ = $CustomerCd
ORDER BY
	売上データ明細.商品ＣＤ
        ";

        $DataList = DB::select(DB::raw($sql));

        return $DataList;
    }

    /**
     * Save
     */
    public function Save($request)
    {
        $skip = [];

        $params = $request->all();
        $SaveList = $params['SaveList'];
        $DeleteList = $params['DeleteList'];

        //モバイルsv更新用
        $MUpdateList = [];
        $MInsertList = [];
        $MDeleteList = [];

        DB::beginTransaction();

        try {
            $date = Carbon::now()->format('Y-m-d H:i:s');

            foreach ($DeleteList as $rec) {
                売上データ明細::query()
                    ->where('日付', $rec['日付'])
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('コースＣＤ', $rec['コースＣＤ'])
                    ->where('行Ｎｏ', $rec['行Ｎｏ'])
                    ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                    ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                    ->where('受注Ｎｏ', $rec['受注Ｎｏ'])
                    ->delete();

                //モバイルsv更新用
                array_push($MDeleteList, $rec);
            }

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

                    売上データ明細::query()
                        ->where('日付', $rec['日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('コースＣＤ', $rec['コースＣＤ'])
                        ->where('行Ｎｏ', $rec['行Ｎｏ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                        ->where('受注Ｎｏ', $rec['受注Ｎｏ'])
                        ->update($rec);

                        //モバイルsv更新用
                        array_push($MUpdateList, $rec);

                } else {
                    $no = 売上データ明細::query()
                        ->where('日付', $rec['日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('コースＣＤ', $rec['コースＣＤ'])
                        ->where('行Ｎｏ', $rec['行Ｎｏ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->max('明細行Ｎｏ') + 1;

                    $rec['修正日'] = $date;
                    $rec['明細行Ｎｏ'] = $no;
                    $rec['請求日付'] = $rec['請求日付'] ?? '';

                    売上データ明細::insert($rec);

                    //モバイルsv更新用
                    array_push($MInsertList, $rec);
                }
            }

            DB::commit();

            //モバイルsv更新
            $ds = new DataSendWrapper();
            foreach ($MDeleteList as $rec) {
                $ds->Delete('売上データ明細', $rec, true, $rec['部署ＣＤ'], null, $rec['コースＣＤ']);
            }
            foreach ($MUpdateList as $rec) {
                $ds->InsertUriageMeisaiData($rec);
            }
            foreach ($MInsertList as $rec) {
                $ds->InsertUriageMeisaiData($rec);
            }

        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            "edited" => count($skip) > 0 ? $this->GetSalesList($request) : [],
        ]);
    }
}
