<?php

namespace App\Http\Controllers;

use App\Models\入金データ;
use App\Models\管理マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use PDO;

class DAI02030Controller extends Controller
{

    /**
     * GetSimeDateList
     */
    public function GetSimeDateList($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDate = $vm->TargetDate;

        $sql = "
			SELECT DISTINCT
				FORMAT(SEIKYU.請求日付, 'dd') AS 締日
			FROM
				請求データ SEIKYU
				INNER JOIN [得意先マスタ] TOK
					ON	TOK.得意先ＣＤ = SEIKYU.請求先ＣＤ
					AND TOK.得意先ＣＤ = TOK.請求先ＣＤ
					AND TOK.締区分 = 2
					AND (TOK.売掛現金区分 = 1 OR TOK.売掛現金区分 = 0)
            WHERE
            	SEIKYU.部署ＣＤ=$BushoCd
			AND	(SEIKYU.請求日付 >= DATEADD(DD, 1, EOMONTH ('$TargetDate' , -1)) AND SEIKYU.請求日付 <= EOMONTH('$TargetDate'))
			AND SEIKYU.請求日付 != EOMONTH('$TargetDate')
            AND (
                ISNULL(SEIKYU.[３回前残高], 0) + ISNULL(SEIKYU.前々回残高, 0) + ISNULL(SEIKYU.前回残高, 0) != 0
                OR
                SEIKYU.今回売上額 != 0
            )
            UNION
            SELECT
                99
        ";

        $Result = collect(DB::select($sql))
            ->map(function ($val) {
                $obj = (object) [];

                $obj->Cd = $val->締日 * 1;
                $obj->CdNm = $val->締日 == 99 ? '末日' : ($val->締日 * 1 . '日締');

                return $obj;
            })
            ->values();

        return response()->json($Result);
    }

    /**
     * Search
     */
    public function Search($vm)
    {
        return response()->json($this->GetSeikyuList($vm));
    }

    /**
     * GetSeikyuList
     */
    public function GetSeikyuList($vm)
    {
        $SimeKbn = $vm->SimeKbn;
        $TargetDate = $vm->TargetDate;
        $TargetDateMax = $vm->TargetDate;

        $WhereTargetDate = $SimeKbn == 2
            ? "AND (SEIKYU.請求日付 >= DATEADD(dd, 1, EOMONTH ('$TargetDate' , -1)) AND SEIKYU.請求日付 <= EOMONTH('$TargetDate'))"
            : "AND SEIKYU.請求日付 = '$TargetDate'";

        $BushoCd = $vm->BushoCd;

        $CourseCd = $vm->CourseCd;
        $WehreCourseCd = !!$CourseCd ? " AND COUM.コースＣＤ = $CourseCd" : "";

        $CustomerCd = $vm->CustomerCd;
        $WehreCustomerCd = !!$CustomerCd ? " AND SEIKYU.請求先ＣＤ = $CustomerCd" : " AND SEIKYU.請求先ＣＤ != 0";

        $sql = "
            SELECT
                SEIKYU.部署ＣＤ
                ,SEIKYU.請求先ＣＤ
				,SEIKYU.予備金額１ AS 請求番号
                ,SEIKYU.請求日付
                ,ISNULL(COUT.コースＣＤ,0) AS コースＣＤ
                ,ISNULL(COUT.ＳＥＱ,0) AS ＳＥＱ
                ,COUM.コース名
                ,COUM.コース区分
                ,TANM.担当者名
                ,SEIKYU.[３回前残高]
                ,SEIKYU.前々回残高
                ,SEIKYU.前回残高
                ,SEIKYU.今回入金額
                ,SEIKYU.差引繰越額
                ,SEIKYU.今回売上額
                ,SEIKYU.消費税額
                ,SEIKYU.今回請求額
                ,SEIKYU.請求日範囲開始
                ,SEIKYU.請求日範囲終了
                ,TOK.得意先名
                ,TOK.得意先名略称
				,TOK.締日１
				,TOK.締日２
				,TOK.郵便番号
				,TOK.住所１
				,TOK.電話番号１
				,TOK.ＦＡＸ１
                ,ISNULL(COUM.担当者ＣＤ,0) AS 担当者ＣＤ
                ,ISNULL(KAKU_SHIHA.各種名称,'') AS 支払方法
                ,SEIKYU.回収予定日 AS 集金日
                ,ISNULL(KAKU_SYUKIN.各種名称,'') AS 集金区分
                ,TOK.支払サイト
                ,TOK.支払日
            FROM
                [請求データ] SEIKYU
                INNER JOIN [得意先マスタ] TOK
                    ON	TOK.得意先ＣＤ = SEIKYU.請求先ＣＤ
                    AND TOK.得意先ＣＤ = TOK.請求先ＣＤ
                    AND TOK.締区分 = $SimeKbn
                    AND (TOK.売掛現金区分 = 1 OR TOK.売掛現金区分 = 0)
                LEFT OUTER JOIN (
                    SELECT
                        COU.部署ＣＤ
                        ,COU.得意先ＣＤ
                        ,MIN(COU.コースＣＤ) AS コースＣＤ
                    FROM
                        コーステーブル COU
                        LEFT OUTER JOIN コースマスタ COUM
                            ON	COU.部署ＣＤ = COUM.部署ＣＤ
                            AND COU.コースＣＤ = COUM.コースＣＤ
                    WHERE
                        COUM.コース区分 =
                            (
                                SELECT
                                    MIN(DMY_COUM.コース区分)
                                FROM
                                    コーステーブル DMY_COUT
                                    LEFT OUTER JOIN コースマスタ DMY_COUM ON
                                        DMY_COUT.部署ＣＤ = DMY_COUM.部署ＣＤ
                                        AND DMY_COUT.コースＣＤ = DMY_COUM.コースＣＤ
                                        AND DMY_COUM.コース区分 IN (1,2,3)
                                WHERE
                                    COU.部署ＣＤ = DMY_COUT.部署ＣＤ
                                    AND COU.得意先ＣＤ = DMY_COUT.得意先ＣＤ
                            )
                    AND COU.部署ＣＤ = $BushoCd
                    $WehreCourseCd
                    AND COUM.コース区分 IN (1,2,3)
                    GROUP BY
                        COU.部署ＣＤ
                        ,COU.得意先ＣＤ
                ) COU_KEY
                    ON	COU_KEY.部署ＣＤ = SEIKYU.部署ＣＤ
                    AND COU_KEY.得意先ＣＤ =
                        CASE
                            WHEN TOK.受注得意先ＣＤ = 0 THEN TOK.得意先ＣＤ
                            ELSE TOK.受注得意先ＣＤ
                        END
                LEFT OUTER JOIN [コーステーブル] COUT
                    ON	COUT.部署ＣＤ = COU_KEY.部署ＣＤ
                    AND COUT.得意先ＣＤ = COU_KEY.得意先ＣＤ
                    AND COUT.コースＣＤ = COU_KEY.コースＣＤ
                LEFT OUTER JOIN [コースマスタ] COUM
                    ON	 COUM.部署ＣＤ = COUT.部署ＣＤ
                    AND COUM.コースＣＤ = COUT.コースＣＤ
                LEFT OUTER JOIN 各種テーブル KAKU_SHIHA
                    ON	KAKU_SHIHA.各種CD = 6
                    AND KAKU_SHIHA.行NO = TOK.支払方法１
                LEFT OUTER JOIN 各種テーブル KAKU_SYUKIN
                    ON	KAKU_SYUKIN.各種CD = 5
                    AND KAKU_SYUKIN.行NO = TOK.集金区分
                LEFT OUTER JOIN 担当者マスタ TANM
                    ON	TANM.担当者ＣＤ = COUM.担当者ＣＤ
            WHERE
                SEIKYU.部署ＣＤ = $BushoCd
            --AND (
            --    ISNULL(SEIKYU.[３回前残高], 0) + ISNULL(SEIKYU.前々回残高, 0) + ISNULL(SEIKYU.前回残高, 0) != 0
            --    OR
            --    SEIKYU.今回売上額 != 0
            --)
            $WhereTargetDate
            $WehreCustomerCd
            ORDER BY
	            COUT.コースＣＤ
	            ,COUT.ＳＥＱ
	            ,SEIKYU.請求先ＣＤ
	            ,SEIKYU.請求日付
		";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        // $DataList = DB::select(DB::raw($sql));

        return $DataList;
    }

    /**
     * GetMeisaiList
     */
    public function GetMeisaiList($vm)
    {
        $SeikyuNoArray = implode(",", $vm->SeikyuNoArray);

        $sql = "
            WITH 請求明細 AS (
                SELECT
                    IIF(得意先マスタ.請求先ＣＤ=0, 得意先マスタ.得意先ＣＤ, 得意先マスタ.請求先ＣＤ) AS 請求先ＣＤ,
                    売上データ明細.部署ＣＤ,
                    売上データ明細.得意先ＣＤ,
					得意先マスタ.得意先名,
                    売上データ明細.コースＣＤ,
                    コースマスタ.コース名,
                    コースマスタ.コース区分,
                    売上データ明細.日付 AS 伝票日付,
            		FORMAT(売上データ明細.日付, 'MM/dd') AS 日付,
                    NULL AS 伝票Ｎｏ,
                    売上データ明細.商品ＣＤ,
                    売上データ明細.食事区分,
                    食事区分名称.各種略称 AS 食事区分名,
                    商品マスタ.商品名 + IIF(売上データ明細.掛売値引 > 0, '(値引)', '') AS 商品名,
                    SUM(売上データ明細.掛売個数) AS 数量,
                    ISNULL(売上データ明細.予備金額１, 0) AS 単価,
                    SUM(売上データ明細.掛売金額 - ISNULL(売上データ明細.掛売値引, 0)) AS 金額,
                    NULL AS 入金金額,
                    売上データ明細.備考 AS 備考
                FROM
                    [売上データ明細]
                    INNER JOIN [商品マスタ]
                        ON 商品マスタ.商品ＣＤ = 売上データ明細.商品ＣＤ
                    INNER JOIN [得意先マスタ]
                        ON 得意先マスタ.得意先ＣＤ = 売上データ明細.得意先ＣＤ
                    LEFT OUTER JOIN コースマスタ
                        ON  コースマスタ.部署ＣＤ = 売上データ明細.部署ＣＤ
                        AND コースマスタ.コースＣＤ = 売上データ明細.コースＣＤ
                    LEFT OUTER JOIN 各種テーブル 食事区分名称
                        ON  食事区分名称.各種CD = 38
                        AND 食事区分名称.サブ各種CD1 = 売上データ明細.食事区分
                WHERE
                    売上データ明細.売掛現金区分 = 1
                GROUP BY
                    IIF(得意先マスタ.請求先ＣＤ=0, 得意先マスタ.得意先ＣＤ, 得意先マスタ.請求先ＣＤ),
                    売上データ明細.部署ＣＤ,
                    売上データ明細.得意先ＣＤ,
					得意先マスタ.得意先名,
                    売上データ明細.コースＣＤ,
                    コースマスタ.コース名,
                    コースマスタ.コース区分,
                    売上データ明細.日付,
                    売上データ明細.商品ＣＤ,
                    売上データ明細.食事区分,
                    食事区分名称.各種略称,
                    商品マスタ.商品名 + IIF(売上データ明細.掛売値引 > 0, '(値引)', ''),
                    ISNULL(売上データ明細.予備金額１, 0),
                    売上データ明細.備考
                UNION
                SELECT
                    IIF(得意先マスタ.請求先ＣＤ=0, 得意先マスタ.得意先ＣＤ, 得意先マスタ.請求先ＣＤ) AS 請求先ＣＤ,
                    入金データ.部署ＣＤ,
                    入金データ.得意先ＣＤ,
					得意先マスタ.得意先名,
                    NULL AS コースＣＤ,
                    NULL AS コース名,
                    NULL AS コース区分,
                    入金データ.入金日付 AS 伝票日付,
                    FORMAT(入金データ.入金日付, 'MM/dd') AS 日付,
                    入金データ.伝票Ｎｏ,
                    NULL AS 商品ＣＤ,
                    NULL AS 食事区分,
                    NULL AS 食事区分名,
                    (
                        '入金'
                            + IIF(入金データ.現金 > 0, '(現金)', '')
                            + IIF(入金データ.小切手 > 0, '(小切手)', '')
                            + IIF(入金データ.振込 > 0, '(振込)', '')
                            + IIF(入金データ.バークレー > 0, '(振替)', '')
                            + IIF(入金データ.その他 > 0, '(ﾁｹｯﾄ入金)', '')
                            + IIF(入金データ.相殺 > 0, '(振込料)', '')
                            + IIF(入金データ.値引 > 0, '(値引)', '')
                    ) AS 商品名,
                    NULL AS 数量,
                    NULL AS 単価,
                    NULL AS 金額,
                    (
                        ISNULL(入金データ.現金, 0)
                            + ISNULL(入金データ.小切手, 0)
                            + ISNULL(入金データ.振込, 0)
                            + ISNULL(入金データ.バークレー, 0)
                            + ISNULL(入金データ.その他, 0)
                            + ISNULL(入金データ.相殺, 0)
                            + ISNULL(入金データ.値引, 0)
                    ) AS 入金金額,
                    入金データ.備考
                FROM
                    [入金データ]
                    INNER JOIN [得意先マスタ]
                        ON 得意先マスタ.得意先ＣＤ = 入金データ.得意先ＣＤ
            )
            SELECT
                MEISAI.*
            FROM
                請求明細 MEISAI
                INNER JOIN 請求データ SEIKYU
                    ON  MEISAI.部署ＣＤ = SEIKYU.部署ＣＤ
                    AND MEISAI.請求先ＣＤ = SEIKYU.請求先ＣＤ
                    AND	MEISAI.伝票日付 >= SEIKYU.請求日範囲開始
                    AND	MEISAI.伝票日付 <= SEIKYU.請求日範囲終了
            WHERE
                SEIKYU.予備金額１ IN ($SeikyuNoArray)
			AND (MEISAI.伝票Ｎｏ IS NOT NULL OR MEISAI.金額 > 0)
            ORDER BY
                MEISAI.得意先ＣＤ
                ,MEISAI.伝票日付
                ,MEISAI.伝票Ｎｏ
		";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $ttl = ini_get('max_execution_time');
        set_time_limit(0);

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($DataList as $key => $data) {
            if ( $data["単価"] === 0 or $data["単価"] === '0') {
                $sql = "
                    SELECT
                        売価単価
                        ,MTT.単価
                    FROM 商品マスタ
                    LEFT JOIN
                        (SELECT
                            *
                        FROM (
                            SELECT
                                *
                                , RANK() OVER(PARTITION BY 得意先ＣＤ, 商品ＣＤ ORDER BY 適用開始日 DESC) AS RNK
                            FROM
                                得意先単価マスタ新
                            WHERE
                                得意先ＣＤ= {$data["得意先ＣＤ"]}
                            AND 商品ＣＤ = {$data["商品ＣＤ"]}
                            AND 適用開始日 <= '{$data["伝票日付"]}'
                        ) TT
                        WHERE
                            RNK = 1
                        ) MTT ON
                    MTT.商品ＣＤ = 商品マスタ.商品ＣＤ
                    WHERE 商品マスタ.商品ＣＤ = {$data["商品ＣＤ"]}
                ";
                $stmt = $pdo->query($sql);
                $TankaDataList = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!!$TankaDataList && $TankaDataList[0]["単価"] > 0) {
                    $DataList[$key]["単価"] = $TankaDataList[0]["単価"];
                } else {
                    $DataList[$key]["単価"] = $TankaDataList[0]["売価単価"];
                }
            }
        }
        $pdo = null;

        set_time_limit($ttl);
        // $DataList = DB::select(DB::raw($sql));

        return $DataList;
    }
}
