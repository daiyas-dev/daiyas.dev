<?php

namespace App\Http\Controllers;

use App\Models\注文データ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use PDO;

class DAI08010Controller extends Controller
{

    /**
     * GetShidashiOrderNoList
     */
    public function GetShidashiOrderNoList($request)
    {
        $DeliveryDate = $request->targetDate;

        $WhereDeliveryDate = $DeliveryDate ? " AND 配達日付 = '$DeliveryDate'" : "";

        $sql = "
            WITH 仕出顧客 AS (
                SELECT
                    CHUMON.部署ＣＤ
                    ,MB.部署名
                    ,CHUMON.受注Ｎｏ
                    ,CHUMON.配達日付
                    ,CHUMON.配達時間,
                    CHUMON.得意先ＣＤ
                    ,TOK.得意先名
                    ,TOK.電話番号１
                    ,TOK.ＦＡＸ１
                    ,CHUMON.注文日付
                    ,ISNULL(TOK.住所１,'')  +  ISNULL(TOK.住所２,'') AS 住所
                    ,ISNULL(CHUMON.配達先１,'')  + ISNULL(CHUMON.配達先２,'') AS 配達先
                    ,CHUMON.エリアＣＤ
                    ,COUM.コース名 AS エリア名称
                    ,CHUMON.地域区分
                    ,KAKUSHU_TIKU.各種名称 AS 地区名称
                    ,CHUMON.配達区分
                    ,KAKUSHU_HAITATSU.各種名称 AS 配達名称
                    ,CHUMON.税区分
                    ,KAKUSHU_ZEI.各種名称 AS 税名称
                    ,RANK() OVER(PARTITION BY CHUMON.得意先ＣＤ ORDER BY CHUMON.注文日付 DESC, CHUMON.受注Ｎｏ DESC) AS RNK
                FROM
                    仕出し注文データ CHUMON
                    LEFT OUTER JOIN 得意先マスタ TOK ON
                    CHUMON.得意先ＣＤ = TOK.得意先ＣＤ
                    LEFT OUTER JOIN 各種テーブル KAKUSHU_TIKU ON
                    KAKUSHU_TIKU.各種CD = 32
                    AND KAKUSHU_TIKU.行NO = CHUMON.地域区分
                    LEFT OUTER JOIN 各種テーブル KAKUSHU_HAITATSU ON
                    KAKUSHU_HAITATSU.各種CD = 31
                    AND KAKUSHU_HAITATSU.行NO = CHUMON.配達区分
                    LEFT OUTER JOIN 各種テーブル KAKUSHU_ZEI ON
                    KAKUSHU_ZEI.各種CD = 20
                    AND KAKUSHU_ZEI.行NO = CHUMON.税区分
                    LEFT OUTER JOIN コースマスタ COUM ON
                    COUM.部署ＣＤ = CHUMON.部署ＣＤ
                    AND COUM.コースＣＤ = CHUMON.エリアＣＤ
                    LEFT OUTER JOIN 部署マスタ MB
                    ON MB.部署CD = CHUMON.部署ＣＤ
            )
            SELECT
                DISTINCT
                受注Ｎｏ AS Cd,
                得意先名 AS CdNm,
                *
            FROM
                仕出顧客
            WHERE
                RNK = 1
                $WhereDeliveryDate
            ORDER BY
                得意先ＣＤ
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pdo = null;

        return response()->json($DataList);
    }

    /**
     * Search
     */
    public function Search($request)
    {
        $Utilities = new UtilitiesController();

        return response()->json([
            [
                "CustomerInfo" => $Utilities->GetCustomerList($request)[0],
                "Order" => $this->GetOrder($request),
                "MeisaiList" => $this->GetMeisaiList($request),
            ]
        ]);
    }

    /**
     * GetOrder
     */
    public function GetOrder($request)
    {
        $BushoCd = $request->BushoCd;
        $CustomerCd = $request->CustomerCd;
        $DeliveryDate = $request->DeliveryDate;

        $JuchuNo = $request->JuchuNo;
        $WhereJuchuNo = !!$JuchuNo ? "AND 受注Ｎｏ=$JuchuNo" : "";

        $sql = "
            SELECT TOP(1)
                *
                ,担当者マスタ.担当者名 AS 修正担当者名
            FROM
                仕出し注文データ
                LEFT OUTER JOIN 担当者マスタ
                    ON 担当者マスタ.担当者ＣＤ=仕出し注文データ.修正担当者ＣＤ
            WHERE
                部署ＣＤ=$BushoCd
            AND 配達日付='$DeliveryDate'
            AND 得意先ＣＤ=$CustomerCd
            $WhereJuchuNo
            ORDER BY
                受注Ｎｏ
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $Result = $stmt->fetch(PDO::FETCH_ASSOC);
        $pdo = null;

        return $Result;
    }

    /**
     * GetMeisaiList
     */
    public function GetMeisaiList($request)
    {
        $BushoCd = $request->BushoCd;
        $CustomerCd = $request->CustomerCd;
        $DeliveryDate = $request->DeliveryDate;

        $JuchuNo = $request->JuchuNo;
        $WhereJuchuNo = !!$JuchuNo ? "AND 受注Ｎｏ=$JuchuNo" : "AND 受注Ｎｏ=注文データ.受注Ｎｏ";

        $sql = "
            WITH 注文データ AS (
                SELECT
                    受注Ｎｏ
                FROM
                    仕出し注文データ
                WHERE
                    部署ＣＤ=$BushoCd
                AND 配達日付='$DeliveryDate'
                AND 得意先ＣＤ=$CustomerCd
                $WhereJuchuNo
                ORDER BY
                    受注Ｎｏ
            )
            SELECT
                *
            FROM
                仕出し注文明細データ
            WHERE
                部署ＣＤ=$BushoCd
            AND 配達日付='$DeliveryDate'
            AND 得意先ＣＤ=$CustomerCd
            $WhereJuchuNo
            ORDER BY
                明細Ｎｏ
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        return $DataList;
    }

    /**
     * GetProductList
     */
    public function GetProductList($request)
    {
        $CustomerCd = $request->CustomerCd;
        $DeliveryDate = $request->DeliveryDate;

        $sql = "
            SELECT
                MTT.得意先ＣＤ,
                MTT.商品ＣＤ,
                PM.商品名,
                PM.商品区分,
                MTT.単価
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
                            得意先ＣＤ=$CustomerCd
                        AND 適用開始日 <= '$DeliveryDate'
                    ) TT
                    WHERE
                        RNK = 1
                ) MTT
                LEFT OUTER JOIN 商品マスタ PM
                    ON	PM.商品ＣＤ=MTT.商品ＣＤ
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
    public function GetTodayOrder($request)
    {
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
                WITH 得意先単価 AS (
                    SELECT
                        MTT.得意先ＣＤ,
                        MTT.商品ＣＤ,
                        PM.商品名,
                        PM.商品区分,
                        MTT.単価
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
                                    得意先ＣＤ=$CustomerCd
                                AND 適用開始日 <= '$DeliveryDate'
                            ) TT
                            WHERE
                                RNK = 1
                        ) MTT
                        LEFT OUTER JOIN 商品マスタ PM
                            ON	PM.商品ＣＤ=MTT.商品ＣＤ
                ),
                注文最終修正 AS (
                    SELECT
                        MTT.得意先ＣＤ,
                        CD.配送日,
                        CD.修正担当者ＣＤ,
                        TM.担当者名 AS 修正担当者名,
                        CD.修正日,
                        MAX(CD.修正日) OVER () AS 注文最終修正日
                    FROM
                        得意先単価 MTT
                        LEFT OUTER JOIN 注文データ CD
                            ON  CD.得意先ＣＤ = MTT.得意先ＣＤ
                            AND CD.商品ＣＤ = MTT.商品ＣＤ
                            AND	CD.部署ＣＤ = $BushoCd
                            AND CD.配送日 = '$DeliveryDate'
                            AND CD.注文区分=0
                        LEFT OUTER JOIN 担当者マスタ TM
                            ON  TM.担当者ＣＤ = CD.修正担当者ＣＤ
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
        $BushoCd = $request->BushoCd ?? $request->bushoCd;
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

        $sql = "
            WITH 部署ソート AS (
                SELECT
                    *
                    ,IIF(
                        部署CD=$BushoCd,
                        0,
                        CASE 部署CD
                            WHEN 101 THEN 1
                            WHEN 201 THEN 2
                            WHEN 301 THEN 3
                            WHEN 401 THEN 4
                            WHEN 901 THEN 5
                            WHEN 701 THEN 6
                            WHEN 601 THEN 7
                            WHEN 0 THEN 9999
                            ELSE 部署CD
                        END
                    ) AS ソート
                FROM
                    部署マスタ
            ),
            得意先_コース一覧 AS
            (
                SELECT
                    MB.ソート,
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
                    LEFT OUTER JOIN 部署ソート MB
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
                ソート,
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
                ISNULL(ソート, 9999),
                得意先ＣＤ
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

            $DeleteList = $params['DeleteList'];
            foreach ($DeleteList as $rec) {
                注文データ::query()
                    ->where('注文区分', $rec['注文区分'])
                    ->where('注文日付', $rec['注文日付'])
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                    ->where('配送日', $rec['配送日'])
                    ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                    ->delete();
            }

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

                if (!!isset($rec['現金個数']) && !!isset($rec['掛売個数'])) {
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
            "edited" => count($skip) > 0,
            "current" => $this->GetOrderList($request),
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
