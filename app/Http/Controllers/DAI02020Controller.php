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

class DAI02020Controller extends Controller
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
                ,SEIKYU.今回請求額
                ,SEIKYU.請求日範囲開始
                ,SEIKYU.請求日範囲終了
                ,TOK.得意先名
				,TOK.締日１
				,TOK.締日２
                ,ISNULL(COUM.担当者ＣＤ,0) AS 担当者ＣＤ
                ,ISNULL(KAKU_SHIHA.各種名称,'') AS 支払方法
                ,SEIKYU.回収予定日 AS 集金日
                ,ISNULL(KAKU_SYUKIN.各種名称,'') AS 集金区分
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
}
