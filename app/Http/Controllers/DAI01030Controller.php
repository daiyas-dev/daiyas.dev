<?php

namespace App\Http\Controllers;

use App\Models\注文データ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use PDO;

class DAI01030Controller extends Controller
{

    /**
     * GetProductList
     */
    public function GetProductList($request)
    {
        $CustomerCd = $request->CustomerCd;

        $sql = "
SELECT
	PM.商品ＣＤ,
	PM.商品名,
	PM.商品区分,
	IIF(MTT.商品ＣＤ IS NOT NULL, MTT.単価, PM.売価単価) AS 売価単価
FROM
	商品マスタ PM
	LEFT JOIN 得意先単価マスタ MTT
		ON	PM.商品ＣＤ=MTT.商品ＣＤ
		AND MTT.得意先ＣＤ=$CustomerCd
WHERE
     表示ＦＬＧ=0
        ";

        $Result = collect(DB::select($sql))
            ->map(function ($product) {
                $vm = (object) $product;

                $vm->Cd = $product->商品ＣＤ;
                $vm->CdNm = $product->商品名;

                return $vm;
            })
            ->values();

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
    public function Search($request)
    {
        return response()->json($this->GetOrderList($request));
    }

    /**
     * GetOrderList
     */
    public function GetOrderList($vm)
    {
        $BushoCd = $vm->BushoCd;
        $CustomerCd = $vm->CustomerCd;
        $DeliveryDate = $vm->DeliveryDate;

        $sql = "
WITH 注文一覧 AS (
SELECT
	MTT.得意先ＣＤ,
	MTT.商品ＣＤ,
	MTT.単価,
	MP.商品名,
	MP.商品区分,
	CD.注文区分,
	CD.注文日付,
	CD.注文時間,
	CD.配送日,
	CD.明細行Ｎｏ,
	CD.入力区分,
	CD.現金個数,
	CD.現金金額,
	CD.掛売個数,
	CD.掛売金額,
	CD.予備ＣＤ１,
	CD.予備金額1,
	CD.修正担当者ＣＤ,
	CD.修正日
FROM
	得意先単価マスタ MTT
	INNER JOIN 商品マスタ MP
		ON MP.商品ＣＤ = MTT.商品ＣＤ
	LEFT OUTER JOIN 注文データ CD
		ON  CD.得意先ＣＤ = MTT.得意先ＣＤ
		AND CD.商品ＣＤ = MTT.商品ＣＤ
		AND	CD.部署ＣＤ = $BushoCd
		AND CD.配送日 = '$DeliveryDate'
WHERE
	MTT.得意先ＣＤ = $CustomerCd
)
SELECT
	得意先ＣＤ,
	注文日付,
	MAX(配送日) AS 配送日,
	MAX(IIF(注文区分=0, 注文時間, null)) AS 注文時間,
	MIN(注文区分) AS 注文区分,
	商品ＣＤ,
	商品名,
    単価,
	商品区分,
	SUM(IIF(注文区分=1, 現金個数 + 掛売個数, 0)) AS 予定数,
	SUM(IIF(注文区分=0, 現金個数, 0)) AS 現金個数,
	SUM(IIF(注文区分=0, 現金金額, 0)) AS 現金金額,
	SUM(IIF(注文区分=0, 掛売個数, 0)) AS 掛売個数,
    SUM(IIF(注文区分=0, 掛売金額, 0)) AS 掛売金額,
	SUM(IIF(注文区分 IS NULL, 1, 0)) AS 全表示,
	MAX(IIF(注文区分=0, 修正日, null)) AS 修正日
FROM
	注文一覧
GROUP BY
	得意先ＣＤ,
	商品ＣＤ,
	注文日付,
	単価,
	商品区分,
	商品名
ORDER BY
	商品ＣＤ
        ";

        $DataList = DB::select($sql);

        $HasShown = collect($DataList)->some(function ($item, $key) {
            return $item->全表示 == 1;
        });

        if (!$HasShown) {
            $sql = "
SELECT DISTINCT
    注文データ.得意先ＣＤ,
    注文データ.商品ＣＤ,
    CASE  WHEN 得意先単価マスタ.単価 IS NULL THEN 注文データ.予備金額１ ELSE 得意先単価マスタ.単価 END 単価,
    商品マスタ.商品名,
    0 タイトルフラグ
FROM
    [注文データ]
	INNER JOIN
		[商品マスタ] ON 商品マスタ.商品ＣＤ = 注文データ.商品ＣＤ
	INNER JOIN
		[得意先単価マスタ] ON 注文データ.商品ＣＤ = 得意先単価マスタ.商品ＣＤ
	AND
		注文データ.得意先ＣＤ = 得意先単価マスタ.得意先ＣＤ
WHERE 注文区分 = 0
    AND 注文データ.部署ＣＤ   = $BushoCd
    AND 注文データ.得意先ＣＤ = $CustomerCd
    AND 注文データ.注文区分 = 0
    AND CONVERT(NVARCHAR, 注文データ.配送日, 112) >= DATEADD(DAY, -8, '$DeliveryDate')
    AND CONVERT(NVARCHAR, 注文データ.配送日, 112) <  '$DeliveryDate'
            ";

            $products = DB::select($sql);

            collect($DataList)->each(function ($item, $key) use ($products) {
                if (collect($products)->contains('商品ＣＤ', $item->商品ＣＤ)) {
                    $item->全表示 = 0;
                }
            });

        }

        return $DataList;
    }

    /**
     * GetBikou()
     */
    public function GetBikou($request)
    {
        $BushoCd = $request->BushoCd;
        $CustomerCd = $request->CustomerCd;
        $DeliveryDate = $request->DeliveryDate;

        $sql = "
SELECT
	CD.備考１,
	CD.備考２,
	CD.備考３,
	CD.備考４,
	CD.備考５
FROM
	注文データ CD
WHERE
	CD.得意先ＣＤ = $CustomerCd
	AND	CD.部署ＣＤ = $BushoCd
	AND CD.配送日 = '$DeliveryDate'
        ";

        $Result = DB::select($sql);

        return response()->json($Result);
    }

    /**
     * GetLastEdit()
     */
    public function GetLastEdit($request) {
        $BushoCd = $request->BushoCd;
        $CustomerCd = $request->CustomerCd;
        $DeliveryDate = $request->DeliveryDate;

        $sql = "
WITH 注文最終修正 AS (
SELECT
	MTT.得意先ＣＤ,
	CD.配送日,
	CD.修正担当者ＣＤ,
	TM.担当者名 AS 修正担当者名,
	CD.修正日,
	MAX(CD.修正日) OVER () AS 注文最終修正日
FROM
	得意先単価マスタ MTT
	LEFT OUTER JOIN 注文データ CD
		ON  CD.得意先ＣＤ = MTT.得意先ＣＤ
		AND CD.商品ＣＤ = MTT.商品ＣＤ
		AND	CD.部署ＣＤ = $BushoCd
        AND CD.配送日 = '$DeliveryDate'
        AND CD.注文区分=0
	LEFT OUTER JOIN 担当者マスタ TM
		ON  TM.担当者ＣＤ = CD.修正担当者ＣＤ
WHERE
	MTT.得意先ＣＤ = $CustomerCd
)
SELECT
	*
FROM
	注文最終修正
WHERE
	修正日 = 注文最終修正日
        ";

        $Result = DB::selectOne($sql);

        return response()->json($Result);
    }

    /**
     * IsDeliveried()
     */
    public function IsDeliveried($request)
    {
        $CustomerCd = $request->CustomerCd;
        $DeliveryDate = $request->DeliveryDate;
        $CourseCd = $request->CourseCd;

        $WhereCourseCd = isset($CourseCd) ? "AND コースCD=$CourseCd" : "";

        $sql = "
SELECT
    実績数
FROM
    モバイル_販売入力
WHERE
    得意先CD=$CustomerCd AND
    実績入力=1 AND
    日付='$DeliveryDate'
    $WhereCourseCd
        ";

        $Result = DB::select($sql);

        return response()->json(["IsDeliveried"=>count($Result) > 0]);
    }

    /**
     * GetCustomerAndCourseList
     */
    public function GetCustomerAndCourseList($request, $KeywordOnly = false)
    {
        $BushoCd = $request->bushoCd;
        $TargetDate = $request->targetDate;
        $IsOyaOnly = $request->isOyaOnly ?? true;
        $Keyword = $request->keyword;

        $WhereOyaOnly = $IsOyaOnly ? " AND (M1.受注得意先ＣＤ = 0 OR M1.受注得意先ＣＤ = M1.得意先ＣＤ)" : "";


        $WhereCourseKbn = $TargetDate
            ? "AND MC.コース区分 =
                                            CASE
                                                WHEN (SELECT 対象日付 FROM 祝日マスタ WHERE 対象日付 = '$TargetDate') IS NOT NULL THEN 4
                                                ELSE
                                                    CASE DATEPART(WEEKDAY, '$TargetDate')
                                                        WHEN 1 THEN 3
                                                        WHEN 7 THEN 2
                                                        ELSE 1
                                                    END
                                            END"
            : "";

        $WhereKeyword = "";
        if (isset($Keyword)) {
            if (!is_numeric($Keyword) || !ctype_digit($Keyword)) {
                $WhereKeyword = "AND (
                    M1.得意先名 LIKE '$Keyword%' OR
                    M1.得意先名略称 LIKE '$Keyword%' OR
                    M1.得意先名カナ LIKE '$Keyword%' OR
                    M1.電話番号１ LIKE '$Keyword%'
                )";
            } else {
                $WhereKeyword = "AND M1.得意先CD LIKE '$Keyword%'";
            }

            if ($KeywordOnly) {
                $WhereCourseKbn = "";
            }
        }


        $OrderByBusho = !!$BushoCd ? "IIF(部署CD=$BushoCd, 0, 1)," : "";

        $sql = "
WITH 得意先_コース一覧 AS
(
	SELECT
		M1.部署CD,
		MB.部署名,
		M1.得意先CD,
		M1.得意先名,
		M1.得意先名略称,
		M1.得意先名カナ,
		M1.売掛現金区分,
		M1.電話番号１,
		M1.備考１,
		M1.備考２,
		M1.備考３,
		MC.担当者ＣＤ,
		MT.担当者名,
		MC.コース区分,
		MC.コースCD,
		MC.コース名,
		RANK() OVER(PARTITION BY M1.部署CD, M1.得意先CD ORDER BY MC.コース区分) AS RNK
	FROM
		得意先マスタ M1
		LEFT OUTER JOIN 部署マスタ MB
			ON MB.部署CD = M1.部署CD
		LEFT OUTER JOIN コーステーブル TC
			ON M1.部署CD = TC.部署CD AND M1.得意先CD = TC.得意先CD
		LEFT OUTER JOIN コースマスタ MC
			ON TC.部署CD = MC.部署CD AND TC.コースCD = MC.コースCD
		LEFT OUTER JOIN 担当者マスタ MT
			ON MT.担当者ＣＤ = MC.担当者ＣＤ
    WHERE
        0=0
        $WhereOyaOnly
        $WhereCourseKbn
        $WhereKeyword
)
SELECT
	得意先CD AS Cd,
	得意先名 AS CdNm,
	部署CD,
	部署名,
	得意先CD,
	得意先名,
    得意先名略称,
    得意先名カナ,
    売掛現金区分,
    電話番号１,
    備考１,
    備考２,
    備考３,
	担当者ＣＤ,
	担当者名,
	コース区分,
	コースCD,
	コース名
FROM
	得意先_コース一覧
WHERE
    RNK = 1
ORDER BY
    $OrderByBusho
    得意先ＣＤ
        ";

        //TODO: 高速化対応
        $dsn = 'sqlsrv:server=localhost;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll();

        $pdo = null;

        if (count($DataList) == 0 && isset($Keyword)) {
            return $this->GetCustomerAndCourseList($request, true);
        }

        return response()->json($DataList);
    }

    /**
     * Save
     */
    public function Save($request)
    {
        $skip = [];

        DB::beginTransaction();

        try {
            $params = $request->all();

            $SaveList = $params['SaveList'];

            $date = Carbon::now()->format('Y-m-d H:i:s');
            foreach ($SaveList as $rec) {
                $no = null;
                if (isset($rec['修正日']) && !!$rec['修正日']) {
                    $r = 注文データ::query()
                        ->where('注文区分', $rec['注文区分'])
                        ->where('注文日付', $rec['注文日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->where('配送日', $rec['配送日'])
                        ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                        ->get();

                    if (count($r) != 1) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => null]);
                        continue;
                    } else if ($rec['修正日'] != $r[0]->修正日) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                        continue;
                    }

                    $no = $rec['明細行Ｎｏ'];

                    注文データ::query()
                        ->where('注文区分', $rec['注文区分'])
                        ->where('注文日付', $rec['注文日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->where('配送日', $rec['配送日'])
                        ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                        ->delete();
                } else {
                    $no = 注文データ::query()
                        ->where('注文区分', $rec['注文区分'])
                        ->where('注文日付', $rec['注文日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->where('配送日', $rec['配送日'])
                        ->max('明細行Ｎｏ') + 1;
                }

                $data = $rec;
                $data['修正日'] = $date;
                $data['明細行Ｎｏ'] = $no;
                $data['備考１'] = $data['備考１'] ?? '';
                $data['備考２'] = $data['備考２'] ?? '';
                $data['備考３'] = $data['備考３'] ?? '';
                $data['備考４'] = $data['備考４'] ?? '';
                $data['備考５'] = $data['備考５'] ?? '';

                注文データ::insert($data);
            }

            if (count($skip) > 0) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            "edited" => count($skip) > 0 ? $this->GetOrderList($request) : [],
        ]);
    }

    /**
     * Delete
     */
    public function Delete($request)
    {
        $skip = [];

        DB::beginTransaction();

        try {
            $params = $request->all();

            $DeleteList = $params['DeleteList'];

            foreach ($DeleteList as $rec) {
                if (isset($rec['修正日']) && !!$rec['修正日']) {
                    $r = 注文データ::query()
                        ->where('注文区分', $rec['注文区分'])
                        ->where('注文日付', $rec['注文日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->where('配送日', $rec['配送日'])
                        ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                        ->get();

                    if (count($r) != 1) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => null]);
                        continue;
                    } else if ($rec['修正日'] != $r[0]->修正日) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                        continue;
                    }

                    注文データ::query()
                        ->where('注文区分', $rec['注文区分'])
                        ->where('注文日付', $rec['注文日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->where('配送日', $rec['配送日'])
                        ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                        ->delete();
                }
            }

            if (count($skip) > 0) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            "edited" => count($skip) > 0 ? $this->GetOrderList($request) : [],
        ]);
    }
}
