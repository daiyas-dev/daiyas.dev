<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use App\Models\請求データ;
use App\Models\売上データ明細;
use App\Models\入金データ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Hamcrest\Arrays\IsArray;
use Illuminate\Support\Carbon;
use PDO;
use Symfony\Component\Console\Helper\TableRows;
class DAI02010Controller extends Controller
{
    public $UriageMeisaiData;

    /**
     *  LastSimeDateSearch
     */
    public function LastSimeDateSearch($vm)
    {
        $BushoCd = $vm->BushoCd;
        $SimeKbn = $vm->SimeKbn;

        $sql = "
        SELECT
            MAX(T1.請求日付) AS 請求日付
        FROM
            請求データ T1
        WHERE
            T1.部署ＣＤ = $BushoCd
        AND T1.請求先ＣＤ >= 0
        AND T1.請求先ＣＤ <= 99999
        AND T1.請求日付 = (
            SELECT
                MAX(DMY1.請求日付)
            FROM
                請求データ DMY1
            LEFT OUTER JOIN 得意先マスタ DMY2 ON DMY1.請求先ＣＤ = DMY2.得意先ＣＤ
            WHERE
                DMY1.部署ＣＤ = $BushoCd
            AND DMY2.締区分 = $SimeKbn
            AND DMY1.請求先ＣＤ >= 0
            AND DMY1.請求先ＣＤ <= 99999
        )
        ";

        //$DataList = DB::selectOne($sql);
        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        return response()->json($DataList[0]);
    }

    /**
     * Search
     */
    public function Search($vm)
    {
        return response()->json($this->GetSeikyuSimeList($vm));
    }

    /**
     * Save
     */
    public function Save($request)
    {
        $this->UriageMeisaiData=array();
        DB::beginTransaction();

        try {
            $this->DeleteSeikyu($request);
            $this->InsertSeikyu($request);
            $this->UpdateSeikyuNo($request);
            $this->UpdateUriage($request);
            $this->UpdateNyukin($request);

            DB::commit();

            //モバイルSvを更新
            $ds = new DataSendWrapper();
            if (!!$this->UriageMeisaiData)
            {
                $ds->UpdateUriageMeisaiData($this->UriageMeisaiData);
            }
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return;
    }

    /**
     * Release
     */
    public function Release($request)
    {
        $this->UriageMeisaiData=array();
        DB::beginTransaction();

        try {
            $this->DeleteSeikyu($request);
            $this->ReleaseUriage($request);
            $this->ReleaseNyukin($request);

            DB::commit();

            //モバイルSvを更新
            $ds = new DataSendWrapper();
            if (!!$this->UriageMeisaiData)
            {
                foreach ($this->UriageMeisaiData as $rec) {
                    $ds->Update('売上データ明細', $rec, true, $rec['部署ＣＤ'], $rec['得意先ＣＤ'], $rec['コースＣＤ']);
                }
            }
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return;
    }

    /**
     * GetSeikyuSimeList
     */
    public function GetSeikyuSimeList($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDateMax = $vm->TargetDateMax;

        $SelectData = $this->SelectCustomerList($vm);

        $sqlCourse = "
            LEFT OUTER JOIN (
                SELECT
                    COU.部署ＣＤ  ,
                    COU.得意先ＣＤ  ,
                    MIN(COU.コースＣＤ) AS コースＣＤ
                FROM
                    コーステーブル COU
                    LEFT OUTER JOIN コースマスタ COUM ON COU.部署ＣＤ = COUM.部署ＣＤ AND COU.コースＣＤ = COUM.コースＣＤ
                WHERE
                    COU.部署ＣＤ = $BushoCd
                AND COUM.コース区分 IN (1,2,3)
                GROUP BY  COU.部署ＣＤ, COU.得意先ＣＤ
            ) COU_KEY ON
                COU_KEY.部署ＣＤ = M1.部署ＣＤ
                AND COU_KEY.得意先ＣＤ =
                    CASE
                        WHEN M1.受注得意先ＣＤ = 0 THEN M1.得意先ＣＤ
                        ELSE M1.受注得意先ＣＤ
                    END
            LEFT OUTER JOIN [コーステーブル] COUT ON
                COUT.部署ＣＤ = COU_KEY.部署ＣＤ
                AND COUT.得意先ＣＤ = COU_KEY.得意先ＣＤ
                AND COUT.コースＣＤ = COU_KEY.コースＣＤ
            LEFT OUTER JOIN [コースマスタ] COUM ON
                COUM.部署ＣＤ = COUT.部署ＣＤ
                AND COUM.コースＣＤ = COUT.コースＣＤ
        ";

        $sql = "
        $SelectData
        ,締対象 AS (
            SELECT
				DATEADD(DAY, 0, '$TargetDateMax') AS 請求日付
                ,部署ＣＤ
                ,CASE WHEN NOT(ISNULL(請求先ＣＤ, 0) != 0 AND 請求先ＣＤ != 得意先ＣＤ) THEN 請求先ＣＤ ELSE 得意先ＣＤ END AS 請求先ＣＤ
                ,num1 AS [３回前残高]
                ,num2 AS 前々回残高
                ,num3 AS 前回残高
                ,num4 AS 今回入金額
                ,CASE WHEN NOT(ISNULL(請求先ＣＤ, 0) != 0 AND 請求先ＣＤ != 得意先ＣＤ) THEN num11 ELSE num13 END AS 差引繰越額
                ,num5 AS 今回売上額
                ,num6 AS 消費税額
                ,CASE WHEN NOT(ISNULL(請求先ＣＤ, 0) != 0 AND 請求先ＣＤ != 得意先ＣＤ) THEN num12 ELSE num14 END AS 今回請求額
                ,FORMAT(請求日付, 'yyyyMMdd') AS 請求日範囲開始
                ,'$TargetDateMax' AS 請求日範囲終了
                ,0 AS 予備金額２
                ,回収予定日
                ,0 AS 予備ＣＤ１
                ,0 AS 予備ＣＤ２
            FROM
                更新データ U1
            WHERE
                --要確認
                num1 = 0	--[３回前残高]
                AND
                num5 > 0	--今回売上額
        )
		,未分配データ AS
		(
			SELECT
				M.部署ＣＤ
				,M.得意先ＣＤ
				,日付
				,(M.現金個数 + M.掛売個数) AS 未分配
			FROM
				売上データ明細 M
			WHERE
				M.部署ＣＤ = $BushoCd
			AND M.得意先ＣＤ IN
				(
				SELECT
					受注得意先ＣＤ
				FROM 得意先マスタ
				WHERE
					得意先ＣＤ != 受注得意先ＣＤ AND 受注得意先ＣＤ != 0
				AND 受注得意先ＣＤ = M.得意先ＣＤ
				GROUP BY 受注得意先ＣＤ
				)
			AND (M.現金個数 + M.掛売個数) > 0
		)
        SELECT DISTINCT
            ST.*
            ,TM.得意先名
            ,TM.受注得意先ＣＤ
            ,TM.支払サイト
            ,TM.支払日
            ,COUM.コースＣＤ
			,COUM.コース名
            ,IIF(SD.予備金額１ IS NOT NULL, 1, 0) AS 締処理済
            ,IIF(U1.得意先ＣＤ IS NOT NULL, 1, 0) AS 未分配
        FROM
            締対象 ST
            LEFT OUTER JOIN 得意先マスタ TM
                ON  TM.得意先ＣＤ=ST.請求先ＣＤ
            LEFT OUTER JOIN (
                SELECT
                    COU.部署ＣＤ  ,
                    COU.得意先ＣＤ  ,
                    MIN(COU.コースＣＤ) AS コースＣＤ
                FROM
                    コーステーブル COU
                    LEFT OUTER JOIN コースマスタ COUM ON COU.部署ＣＤ = COUM.部署ＣＤ AND COU.コースＣＤ = COUM.コースＣＤ
                WHERE
                    COU.部署ＣＤ = $BushoCd
                AND COUM.コース区分 IN (1,2,3)
                GROUP BY  COU.部署ＣＤ, COU.得意先ＣＤ
            ) COU_KEY ON
                COU_KEY.部署ＣＤ = TM.部署ＣＤ
                AND COU_KEY.得意先ＣＤ =
                    CASE
                        WHEN TM.受注得意先ＣＤ = 0 THEN TM.得意先ＣＤ
                        ELSE TM.受注得意先ＣＤ
                    END
            LEFT OUTER JOIN [コーステーブル] COUT ON
                COUT.部署ＣＤ = COU_KEY.部署ＣＤ
                AND COUT.得意先ＣＤ = COU_KEY.得意先ＣＤ
                AND COUT.コースＣＤ = COU_KEY.コースＣＤ
            LEFT OUTER JOIN [コースマスタ] COUM ON
                COUM.部署ＣＤ = COUT.部署ＣＤ
                AND COUM.コースＣＤ = COUT.コースＣＤ
            LEFT OUTER JOIN 未分配データ U1
				ON  U1.得意先ＣＤ = TM.受注得意先ＣＤ
				AND U1.日付 >= IIF(ST.請求日範囲開始 IS NOT NULL, DATEADD(DAY, 1, ST.請求日範囲開始), TM.新規登録日)
				AND U1.日付 <= DATEADD(DAY, 0, ST.請求日範囲終了)
			LEFT OUTER JOIN 請求データ SD
				ON  SD.請求日付=ST.請求日付
                AND SD.請求先ＣＤ=ST.請求先ＣＤ
		ORDER BY
			ST.請求日付,
			ST.請求先ＣＤ
        ";

        // var_export($sql);

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // $DataList = DB::select($sql);
        $pdo = null;

        return $DataList;
    }

    /**
     * GetSeikyuSimeListX
     */
    public function GetSeikyuSimeListX($vm)
    {
        $SimeKbn = $vm->SimeKbn;
        $SimeDate = $vm->SimeDate;
        $TargetDate = $vm->TargetDate;
        $TargetDateMax = $vm->TargetDateMax;

        $WhereSimeDate = $SimeKbn == 2
            ? ($SimeDate == 0 ? "AND M1.締日１= $SimeDate" : "AND (M1.締日１= $SimeDate OR M1.締日２ = $SimeDate)")
            : "";
        $WhereTargetDate = "AND T1.請求日付 = '$TargetDateMax'";

        $BushoCd = $vm->BushoCd;

        $CourseCd = $vm->CourseCd;
        $WehreCourseCd = !!$CourseCd ? " AND COUM.コースＣＤ = $CourseCd" : "";
        //$WehreCourseCd2 = "";//str_replace('COU', 'COUT', $WehreCourseCd);

        $CustomerCd = $vm->CustomerCd;
        $WehreCustomerCd = !!$CustomerCd ? " AND T1.請求先ＣＤ = $CustomerCd" : " AND T1.請求先ＣＤ != 0";

        $sqlCourse = "
            LEFT OUTER JOIN (
                SELECT
                    COU.部署ＣＤ  ,
                    COU.得意先ＣＤ  ,
                    MIN(COU.コースＣＤ) AS コースＣＤ
                FROM
                    コーステーブル COU
                    LEFT OUTER JOIN コースマスタ COUM ON COU.部署ＣＤ = COUM.部署ＣＤ AND COU.コースＣＤ = COUM.コースＣＤ
                WHERE
                    COU.部署ＣＤ = $BushoCd
                AND COUM.コース区分 IN (1,2,3)
                GROUP BY  COU.部署ＣＤ, COU.得意先ＣＤ
            ) COU_KEY ON
                COU_KEY.部署ＣＤ = M1.部署ＣＤ
                AND COU_KEY.得意先ＣＤ =
                    CASE
                        WHEN M1.受注得意先ＣＤ = 0 THEN M1.得意先ＣＤ
                        ELSE M1.受注得意先ＣＤ
                    END
            LEFT OUTER JOIN [コーステーブル] COUT ON
                COUT.部署ＣＤ = COU_KEY.部署ＣＤ
                AND COUT.得意先ＣＤ = COU_KEY.得意先ＣＤ
                AND COUT.コースＣＤ = COU_KEY.コースＣＤ
            LEFT OUTER JOIN [コースマスタ] COUM ON
                COUM.部署ＣＤ = COUT.部署ＣＤ
                AND COUM.コースＣＤ = COUT.コースＣＤ
        ";
        //$WhereSqlCourse= !!$CourseCd ? $sqlCourse : "";
        //$WhereSqlCourse = $sqlCourse;

        $sql = "
        WITH 属性データ AS
        (
            SELECT
                 T1.*
            FROM
                請求データ T1
                INNER JOIN 得意先マスタ M1 ON
                        M1.得意先ＣＤ = T1.請求先ＣＤ
                    $WhereSimeDate
                    AND M1.締区分 IN ($SimeKbn)
            WHERE
                T1.部署ＣＤ = $BushoCd
            $WhereTargetDate
            $WehreCustomerCd
        ),
        前回請求データ AS (
            SELECT
                Z.*
            FROM (
                    SELECT
                        RANK() OVER(PARTITION BY T1.部署ＣＤ, T1.請求先ＣＤ ORDER BY T1.請求日付 DESC) AS RNK,
                        T1.部署ＣＤ,
                        T1.請求先ＣＤ,
                        T1.請求日付,
                        T1.請求日範囲開始,
                        T1.請求日範囲終了
                    FROM
                        請求データ T1
                        INNER JOIN 得意先マスタ M1 ON
                                M1.得意先ＣＤ = T1.請求先ＣＤ
                                $WhereSimeDate
                            AND M1.締区分 IN (2)
                    WHERE
                        T1.部署ＣＤ = $BushoCd
                    AND T1.請求日付 < '$TargetDateMax'
            ) Z
            WHERE Z.RNK=1
        ),
		未分配データ AS
		(
			SELECT
				M.部署ＣＤ
				,M.得意先ＣＤ
				,日付
				,(M.現金個数 + M.掛売個数) AS 未分配
			FROM
				売上データ明細 M
			WHERE
				M.部署ＣＤ = 101
			AND M.得意先ＣＤ IN
				(
				SELECT
					受注得意先ＣＤ
				FROM 得意先マスタ
				WHERE
					得意先ＣＤ != 受注得意先ＣＤ AND 受注得意先ＣＤ != 0
				AND 受注得意先ＣＤ = M.得意先ＣＤ
				GROUP BY 受注得意先ＣＤ
				)
			AND (M.現金個数 + M.掛売個数) > 0
		)

        SELECT DISTINCT
            M1.得意先ＣＤ
            ,M1.得意先名
            ,M1.売掛現金区分
            ,M1.締区分
            ,M1.締日１
            ,M1.締日２
            ,M1.支払サイト
            ,M1.支払日
            ,M1.集金区分
            ,M1.請求先ＣＤ
            ,M1.税区分
			,COUM.コースＣＤ
			,COUM.コース名
            ,T1.*
            ,Z1.請求日範囲開始 AS 前回請求日範囲開始
            ,Z1.請求日範囲終了 AS 前回請求日範囲終了
            ,IIF(Z1.請求日範囲終了 IS NOT NULL, DATEADD(DAY, 1, Z1.請求日範囲終了), M1.新規登録日) AS 今回請求日範囲開始
            ,DATEADD(DAY, 0, '$TargetDateMax') AS 今回請求日範囲終了
            ,(
				CASE M1.支払日
					WHEN 99 THEN DATEADD(MONTH, M1.支払サイト, EOMONTH('$TargetDateMax'))
					ELSE DATEADD(DAY, M1.支払日, DATEADD(MONTH, M1.支払サイト - 1, EOMONTH('$TargetDateMax')))
				END
			) AS 今回回収予定日
            ,U1.得意先ＣＤ AS 未分配得意先ＣＤ
            ,IIF(T1.予備金額１ > 0, 1, 0) AS 締処理済
            ,IIF(U1.得意先ＣＤ IS NOT NULL, 1, 0) AS 未分配
        FROM
            得意先マスタ M1
            $sqlCourse
            LEFT OUTER JOIN 属性データ T1 ON T1.請求先ＣＤ = M1.請求先ＣＤ
            LEFT OUTER JOIN 前回請求データ Z1 ON Z1.請求先ＣＤ = M1.請求先ＣＤ
            LEFT OUTER JOIN 未分配データ U1 ON U1.得意先ＣＤ = M1.受注得意先ＣＤ
				AND U1.日付 >= IIF(Z1.請求日範囲終了 IS NOT NULL, DATEADD(DAY, 1, Z1.請求日範囲終了), M1.新規登録日)
				AND U1.日付 <= DATEADD(DAY, 0, '$TargetDateMax')
        WHERE
            M1.締区分 IN ($SimeKbn)
        AND M1.部署CD = $BushoCd
        AND M1.売掛現金区分 IN (0,1,2)
        AND M1.得意先ＣＤ = M1.請求先ＣＤ
        --AND M1.３回前残高 + M1.前々回残高 + M1.前回残高 + M1.今回入金額 + M1.今回売上額 > 0
        $WehreCourseCd
        ORDER BY 得意先ＣＤ
        ";

        //var_export($sql);

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // $DataList = DB::select($sql);
        $pdo = null;

        return $DataList;
    }

    /**
     * SelectCustomerList
     */
    public function SelectCustomerList($vm)
    {
        $SimeKbn = $vm->SimeKbn;
        $SimeDate = $vm->SimeDate;
        $BushoCd = $vm->BushoCd;
        $TargetDateMax = $vm->TargetDateMax;
        $WhereSimeDate = $SimeKbn == 2
            ? ($SimeDate == 0 ? "AND 締日１= $SimeDate" : "AND (締日１= $SimeDate OR 締日２ = $SimeDate)")
            : "";

        $CustomerList = is_array($vm->CustomerList) ? implode(",", $vm->CustomerList) : $vm->CustomerList;
        $WhereCustomerList = !!$CustomerList ? "AND 請求先ＣＤ IN ($CustomerList)" : "";

        $sql = "
        WITH 更新前データ1 AS
        (
        SELECT
             T2.得意先ＣＤ
            ,T2.得意先名
            ,T2.売掛現金区分
            ,T2.締区分
            ,T2.締日１
            ,T2.締日２
            ,T2.支払サイト
            ,T2.支払日
            ,T2.集金区分
            ,T2.税区分
            ,T2.部署ＣＤ
            ,T2.請求先ＣＤ
            ,B1.今回入金額
            ,B1.今回売上額
            ,B1.消費税額
            ,B1.今回請求額
            ,B1.回収予定日
            ,CASE WHEN B1.請求先ＣＤ IS NOT NULL THEN ISNULL(B1.[３回前残高] + B1.前々回残高, 0) ELSE 0 END AS num1
            ,CASE WHEN B1.請求先ＣＤ IS NOT NULL THEN ISNULL(B1.前回残高, 0) ELSE 0 END AS num2
            ,CASE WHEN B1.請求先ＣＤ IS NOT NULL THEN ISNULL(B1.今回売上額 + B1.消費税額, 0) ELSE 0 END AS num8
            ,CASE WHEN B1.請求先ＣＤ IS NULL THEN DATEADD(DAY, 1, DATEADD(MONTH, -1, '$TargetDateMax')) ELSE DATEADD(DAY, 1, B1.請求日付) END AS 請求日付
            ,CASE WHEN B1.請求先ＣＤ IS NOT NULL THEN ISNULL(B1.今回入金額, 0) ELSE 0 END AS num9
            ,CASE WHEN B1.請求先ＣＤ IS NOT NULL THEN ISNULL(B1.今回請求額, 0) ELSE 0 END AS num10
        FROM
            (
            SELECT
                *
            FROM
                得意先マスタ
			WHERE
                締区分 IN ($SimeKbn)
            $WhereSimeDate
            $WhereCustomerList
            ) T1
        LEFT JOIN 得意先マスタ T2 ON
            T1.請求先ＣＤ = T2.請求先ＣＤ
        --◆請求データ取得
        LEFT JOIN 請求データ B1 ON
            B1.部署ＣＤ = T2.部署ＣＤ
        AND B1.請求先ＣＤ = T2.得意先ＣＤ
        AND B1.請求日付 =
            (
            SELECT
                MAX(DAMMY.請求日付)
            FROM
                請求データ DAMMY
            WHERE
                DAMMY.請求先ＣＤ = B1.請求先ＣＤ
            AND DAMMY.請求日付 < '$TargetDateMax'
            )
        WHERE T1.得意先ＣＤ = T1.請求先ＣＤ
        ),

		売上データ明細_集計 AS (
		SELECT K1.請求先ＣＤ, U1.*
		FROM
		    更新前データ1 K1
        --◆売上データ明細取得
        LEFT JOIN
            (
            SELECT
                 得意先ＣＤ
                ,日付
                ,SUM(現金個数) AS 現金個数
                ,SUM(現金金額) AS 現金金額
                ,SUM(現金値引) AS 現金値引
                ,SUM(掛売個数) AS 掛売個数
                ,SUM(掛売金額) AS 掛売金額
                ,SUM(掛売値引) AS 掛売値引
            FROM
				売上データ明細
            WHERE
                (売掛現金区分 = 1)
            AND 部署ＣＤ = $BushoCd
            GROUP BY 得意先ＣＤ, 日付
            ) U1 ON
                U1.得意先ＣＤ = K1.得意先ＣＤ
            AND U1.日付 >= K1.請求日付 AND U1.日付 <= '$TargetDateMax'
		),

		入金データ_集計 AS (
		--◆入金データ取得
		SELECT K1.請求先ＣＤ, N1.*
		FROM
		    更新前データ1 K1
        LEFT JOIN
            (
            SELECT
                得意先ＣＤ,
				入金日付,
                SUM(現金) as 現金,
                SUM(小切手) as 小切手,
                SUM(振込) as 振込,
                SUM(バークレー) as バークレー,
                SUM(その他) as その他,
                SUM(相殺) as 相殺,
                SUM(値引) as 値引
            FROM
                入金データ
            GROUP BY
                得意先ＣＤ, 入金日付
            ) N1 ON
                N1.得意先ＣＤ = K1.得意先ＣＤ
            AND N1.入金日付 >= K1.請求日付 AND N1.入金日付 <= '$TargetDateMax'
		),

        更新前データ2 AS
        (
        SELECT
             請求日付
            ,部署ＣＤ
            ,K1.請求先ＣＤ
            ,K1.得意先ＣＤ
            ,今回入金額
            ,今回売上額
            ,消費税額
            ,今回請求額
            ,支払日
			,締日１
			,締日２
			,支払サイト
            ,税区分
            ,ISNULL(U1.現金個数, 0) AS 現金個数
            ,ISNULL(U1.現金金額, 0) AS 現金金額
            ,ISNULL(U1.現金値引, 0) AS 現金値引
            ,ISNULL(U1.掛売個数, 0) AS 掛売個数
            ,ISNULL(U1.掛売金額, 0) AS 掛売金額
            ,ISNULL(U1.掛売値引, 0) AS 掛売値引
			,ISNULL(U2.掛売金額合計, 0) AS 掛売金額合計
            ,ISNULL(N1.現金, 0) AS 現金
			,ISNULL(N1.小切手, 0) AS 小切手
			,ISNULL(N1.振込, 0) AS 振込
			,ISNULL(N1.その他, 0) AS その他
			,ISNULL(N1.バークレー, 0) AS バークレー
			,ISNULL(N1.相殺, 0) AS 相殺
			,ISNULL(N1.値引, 0) AS 値引
			,num1
			,num2
			,num8
			,num9
			,num10
        FROM
            更新前データ1 K1
        --◆売上データ明細取得
        LEFT JOIN
            (
            SELECT
                得意先ＣＤ,
                SUM(現金個数) AS 現金個数,
                SUM(現金金額) AS 現金金額,
                SUM(現金値引) AS 現金値引,
                SUM(掛売個数) AS 掛売個数,
                SUM(掛売金額) AS 掛売金額,
                SUM(掛売値引) AS 掛売値引
            FROM
				売上データ明細_集計
            GROUP BY
                得意先ＣＤ
            ) U1 ON
                U1.得意先ＣＤ = K1.得意先ＣＤ
        --◆売上データ明細合計取得
        LEFT JOIN
            (
            SELECT
                請求先ＣＤ,
                SUM(ISNULL(掛売金額, 0)) AS 掛売金額合計
            FROM
                売上データ明細_集計
            GROUP BY
                請求先ＣＤ
            ) U2 ON
                U2.請求先ＣＤ = K1.請求先ＣＤ
        --◆入金データ取得
        LEFT JOIN
            (
			SELECT
                得意先ＣＤ,
                SUM(現金) as 現金,
                SUM(小切手) as 小切手,
                SUM(振込) as 振込,
                SUM(バークレー) as バークレー,
                SUM(その他) as その他,
                SUM(相殺) as 相殺,
                SUM(値引) as 値引
			FROM
                入金データ_集計
            GROUP BY
                得意先ＣＤ
            ) N1 ON
                N1.得意先ＣＤ = K1.得意先ＣＤ
        ),

        更新前データ3 AS
        (
        SELECT
             請求日付
            ,部署ＣＤ
            ,請求先ＣＤ
            ,得意先ＣＤ
            ,今回入金額
            ,今回売上額
            ,消費税額
            ,今回請求額
            ,支払日
            --,CASE WHEN (締日１ = 99 OR 締日２ = 99) THEN DATEADD(MONTH, 支払サイト, '$TargetDateMax') ELSE '$TargetDateMax' END AS 回収予定日
            ,(
				CASE 支払日
					WHEN 99 THEN DATEADD(MONTH, 支払サイト, EOMONTH('$TargetDateMax'))
					ELSE DATEADD(DAY, 支払日, DATEADD(MONTH, 支払サイト - 1, EOMONTH('$TargetDateMax')))
				END
			) AS 回収予定日
            ,税区分
            ,CASE WHEN (num1 != 0 AND num9 != 0) THEN CASE WHEN (num1 > num9) THEN num1 - num9 ELSE 0 END ELSE num1 END AS num1
			,num2
            ,num10 AS num3
			,CASE WHEN 得意先ＣＤ IS NOT NULL THEN CASE WHEN (請求先ＣＤ = 得意先ＣＤ) THEN 掛売金額合計 - 掛売値引 ELSE 掛売金額 - 掛売値引 END END AS num5
            ,CASE WHEN 得意先ＣＤ IS NOT NULL THEN ISNULL(現金 + 小切手 + 振込 + その他 + バークレー + 相殺 + 値引, 0) ELSE 0 END AS num4
            ,num8
            ,CASE WHEN (num1 != 0 AND num9 != 0) THEN CASE WHEN (num1 > num9) THEN 0 ELSE num9 - num1 END ELSE num9 END AS num9
            ,num10 -- - 掛売値引
        FROM
            更新前データ2
        ),

        更新前データ4 AS
        (
        SELECT
             請求日付
            ,部署ＣＤ
            ,請求先ＣＤ
            ,得意先ＣＤ
            ,今回入金額
            ,今回売上額
            ,消費税額
            ,今回請求額
            --,CASE WHEN (支払日 = 99) THEN EOMONTH(回収予定日) ELSE DATEADD(DAY, 支払日, EOMONTH(DATEADD(MONTH, -1, 回収予定日))) END AS 回収予定日
            ,回収予定日 AS 回収予定日
            ,num1
            ,CASE WHEN (num2 != 0 AND num9 != 0) THEN CASE WHEN (num2 > num9) THEN num2 - num9 ELSE 0 END ELSE num2 END AS num2
			,num3
            ,num4
            ,num5
            ,CASE WHEN (税区分 = '0' AND num5 != 0) THEN num5 * 8 / 100 ELSE 0 END AS num6
            ,num8
            ,CASE WHEN (num2 != 0 AND num9 != 0) THEN CASE WHEN (num2 > num9) THEN 0 ELSE num9 - num2 END ELSE num9 END AS num9
            ,CASE WHEN (num1 != 0) THEN num10 - num1 ELSE num10 END AS num10
        FROM
            更新前データ3
        ),

        更新前データ5 AS
        (
        SELECT
             請求日付
            ,部署ＣＤ
            ,請求先ＣＤ
            ,得意先ＣＤ
            ,今回入金額
            ,今回売上額
            ,消費税額
            ,今回請求額
            ,回収予定日
            ,num1
            ,num2
            ,CASE WHEN (num1 != 0) THEN num10 ELSE num3 END AS num3
            ,num4
            ,num5
            ,num6
            ,CASE WHEN (num8 != 0 AND num9 != 0) AND (num8 <= num9) THEN num8 - num9 ELSE 0 END AS num7
            ,CASE WHEN NOT(ISNULL(請求先ＣＤ, 0) != 0 AND 請求先ＣＤ != 得意先ＣＤ) THEN num8 ELSE num1 + num2 + num3 - num4 - 今回入金額 END AS num8
            ,num9
            ,num10
        FROM
            更新前データ4
        ),

        更新前データ6 AS
        (
        SELECT
             請求日付
            ,部署ＣＤ
            ,請求先ＣＤ
            ,得意先ＣＤ
            ,今回入金額
            ,今回売上額
            ,消費税額
            ,今回請求額
            ,回収予定日
            ,num1
            ,num2
            ,CASE WHEN (num2 != 0) THEN num10 - num2 ELSE num3 END AS num3
            ,num4
            ,num5
            ,num6
            ,num7
            ,num8
            ,CASE WHEN NOT(ISNULL(請求先ＣＤ, 0) != 0 AND 請求先ＣＤ != 得意先ＣＤ) THEN num9 ELSE num8 + num5 + num6 + 今回売上額 + 消費税額 END AS num9
            ,num10
        FROM
            更新前データ5
        ),

        更新前データ7 AS
        (
		SELECT
             CASE WHEN (num1 != 0 OR num2 != 0 OR (num3 != 0 OR num5 != 0) OR num4 != 0) THEN DENSE_RANK() OVER(ORDER BY 請求先ＣＤ) END AS 請求SEQ
            ,請求日付
            ,部署ＣＤ
            ,LAG(請求先ＣＤ) OVER(ORDER BY 請求先ＣＤ, 得意先ＣＤ) AS 請求先ＣＤ_LAG
            ,請求先ＣＤ
            ,得意先ＣＤ
            ,今回入金額
            ,今回売上額
            ,消費税額
            ,今回請求額
            ,回収予定日
            ,num1
            ,num2
            ,num3
            ,num4
            ,num5
            ,num6
            ,num7
            ,num8
            ,num9
            ,num10
            ,num1 + num2 + num3 - num4 - CASE WHEN 請求先ＣＤ IS NOT NULL AND 請求日付 = '$TargetDateMax' THEN 今回入金額 ELSE 0 END AS num11
            ,num1 + num2 + num3 - num4 AS num13
        FROM
            更新前データ6
        ),

        更新データ AS
        (
		SELECT
            -- 請求SEQ
             CASE WHEN ISNULL(請求先ＣＤ_LAG, 0) = ISNULL(請求先ＣＤ, 0) AND 請求先ＣＤ != 0 THEN LAG(請求SEQ) OVER(ORDER BY 請求先ＣＤ, 得意先ＣＤ) ELSE 請求SEQ END AS 請求SEQ
            ,請求日付
            ,部署ＣＤ
            ,請求先ＣＤ
            ,得意先ＣＤ
            ,今回入金額
            ,今回売上額
            ,消費税額
            ,今回請求額
            ,回収予定日
            ,num1
            ,num2
            ,num3
            ,num4
            ,num5
            ,num6
            ,num7
            ,num8
            ,num9
            ,num10
            ,num11
            ,num11 + num5 + num6 AS num12
            ,CASE WHEN NOT(ISNULL(請求先ＣＤ, 0) != 0 AND 請求先ＣＤ != 得意先ＣＤ) THEN 0 ELSE num13 END AS num13
            ,CASE WHEN NOT(ISNULL(請求先ＣＤ, 0) != 0 AND 請求先ＣＤ != 得意先ＣＤ) THEN 0 ELSE num13 + num5 END AS num14
        FROM
            更新前データ7
        WHERE
            請求先ＣＤ IS NOT NULL
        )
        ";

        return $sql;
    }

    /**
     * SelectData
     */
    // public function SelectData($vm)
    // {
    //     $SelectData = $this->SelectCustomerList($vm);

    //     $sql = "
    //     $SelectData
    //     SELECT * FROM 更新データ
    //     ";

    //     return DB::select($sql);
    // }

    /**
     * DeleteSeikyu
     */
    public function DeleteSeikyu($vm)
    {
        $SimeKbn = $vm->SimeKbn;
        $SimeDate = $vm->SimeDate;
        $BushoCd = $vm->BushoCd;
        $TargetDateMax = $vm->TargetDateMax;
        $CustomerList = is_array($vm->CustomerList) ? implode(",", $vm->CustomerList) : $vm->CustomerList;

        $WhereSimeDate = $SimeKbn == 2
            ? ($SimeDate == 0 ? "AND 締日１= $SimeDate" : "AND (締日１= $SimeDate OR 締日２ = $SimeDate)")
            : "";

        $sql = "
        DELETE FROM 請求データ
        WHERE
            請求日付 = '$TargetDateMax'
        AND 請求先ＣＤ IN (
            SELECT T2.得意先ＣＤ FROM
            (
            SELECT
                得意先ＣＤ,
                請求先ＣＤ
            FROM
                得意先マスタ
            WHERE
                締区分 IN ($SimeKbn)
            $WhereSimeDate
            AND 請求先ＣＤ IN ($CustomerList)
			) T1
			LEFT JOIN 得意先マスタ T2 ON
				T1.請求先ＣＤ = T2.請求先ＣＤ
        )
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        // $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        // DB::delete($sql);
    }

    /**
     * ReleaseUriage
     */
    public function ReleaseUriage($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDateMax = $vm->TargetDateMax;
        $CustomerList = is_array($vm->CustomerList) ? implode(",", $vm->CustomerList) : $vm->CustomerList;

        $sql = "
        UPDATE 売上データ明細
        SET 請求日付 = ''
        WHERE
            日付 <= '$TargetDateMax'
        AND 部署ＣＤ = $BushoCd
        AND 得意先ＣＤ IN($CustomerList)
        AND 請求日付 = '$TargetDateMax'
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);

        //モバイルSv更新用データを取得
        $sql2 = "
                    SELECT * FROM 売上データ明細
                    WHERE
                        日付 <= '$TargetDateMax'
                    AND 部署ＣＤ = $BushoCd
                    AND 得意先ＣＤ IN($CustomerList)
                    AND 請求日付 = ''
                ";
        $stmt = $pdo->query($sql2);
        if (!!$stmt) {
            $this->UriageMeisaiData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $pdo = null;
    }

    /**
     * DeleteNyukin
     */
    public function ReleaseNyukin($vm)
    {
        $TargetDateMax = $vm->TargetDateMax;
        $CustomerList = is_array($vm->CustomerList) ? implode(",", $vm->CustomerList) : $vm->CustomerList;

        $sql = "
        UPDATE 入金データ
        SET 請求日付 = ''
        WHERE
            入金日付 <= '$TargetDateMax'
        AND 得意先ＣＤ IN($CustomerList)
        AND 請求日付 = '$TargetDateMax'
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        //$DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        // DB::delete($sql);
    }

    /**
     * UpdateSeikyu
     */
    public function UpdateSeikyu($vm)
    {
        $TargetDateMax = $vm->TargetDateMax;
        $date = Carbon::now()->format('Y-m-d H:i:s');
        $SelectData = $this->SelectCustomerList($vm);

        $sql = "
        $SelectData
        UPDATE B1
        SET
            [３回前残高] = num1
            ,前々回残高 = num2
            ,前回残高 = num3
            ,今回入金額 = B1.今回入金額 + num4
            ,差引繰越額 = CASE WHEN NOT(U1.請求先ＣＤ != 0 AND U1.請求先ＣＤ != U1.得意先ＣＤ) THEN num8 ELSE num11 END
            ,今回売上額 = B1.今回売上額 + num5
            ,消費税額 = B1.消費税額 + num6
            ,今回請求額 = CASE WHEN NOT(U1.請求先ＣＤ != 0 AND U1.請求先ＣＤ != U1.得意先ＣＤ) THEN num9 ELSE num12 END
            ,請求日範囲開始 = U1.請求日付
            ,請求日範囲終了 = '$TargetDateMax'
            ,予備金額１ = U2.予備金額１
            ,回収予定日 = U1.回収予定日
            ,修正担当者ＣＤ = ''
            ,修正日 = '$date'
        FROM
            更新データ U1
        INNER JOIN
            (
                SELECT
                     請求日付
                    ,部署ＣＤ
                    ,請求先ＣＤ
                    ,ROW_NUMBER() OVER(ORDER BY 請求日付, 部署ＣＤ, 請求先ＣＤ) + (Select 請求番号１ + 1 From 請求番号管理 WHERE 請求管理No = 1) AS 予備金額１
                FROM 更新データ
            ) U2 ON
                U1.請求日付 = U2.請求日付
            AND U1.部署ＣＤ = U2.部署ＣＤ
            AND U1.請求先ＣＤ = U2.請求先ＣＤ
        LEFT JOIN 請求データ B1 ON
            B1.請求日付 = U1.請求日付
        AND B1.部署ＣＤ = U1.部署ＣＤ
        AND B1.請求先ＣＤ = U1.得意先ＣＤ
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        //$DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        // DB::update($sql);
    }

    /**
     * InsertSeikyu
     */
    public function InsertSeikyu($vm)
    {
        $TargetDateMax = $vm->TargetDateMax;
        $date = Carbon::now()->format('Y-m-d H:i:s');
        $SelectData = $this->SelectCustomerList($vm);
        $TantoCd = $vm->TantoCd;

        $sql = "
        $SelectData
        INSERT INTO 請求データ
        SELECT
			 '$TargetDateMax' AS 請求日付
            ,部署ＣＤ
            ,CASE WHEN NOT(ISNULL(請求先ＣＤ, 0) != 0 AND 請求先ＣＤ != 得意先ＣＤ) THEN 請求先ＣＤ ELSE 得意先ＣＤ END AS 請求先ＣＤ
            ,num1 AS [３回前残高]
            ,num2 AS 前々回残高
            ,num3 AS 前回残高
            ,num4 AS 今回入金額
            ,CASE WHEN NOT(ISNULL(請求先ＣＤ, 0) != 0 AND 請求先ＣＤ != 得意先ＣＤ) THEN num11 ELSE num13 END AS 差引繰越額
            ,num5 AS 今回売上額
            ,num6 AS 消費税額
            ,CASE WHEN NOT(ISNULL(請求先ＣＤ, 0) != 0 AND 請求先ＣＤ != 得意先ＣＤ) THEN num12 ELSE num14 END AS 今回請求額
            ,FORMAT(請求日付, 'yyyyMMdd') AS 請求日範囲開始
            ,'$TargetDateMax' AS 請求日範囲終了
            ,CASE WHEN 請求SEQ IS NOT NULL THEN DENSE_RANK() OVER(ORDER BY 請求SEQ) + (Select 請求番号１ From 請求番号管理 WHERE 請求管理No = 1) - (CASE WHEN (FIRST_VALUE(請求SEQ) OVER (ORDER BY 請求SEQ ASC) IS NULL) THEN 1 ELSE 0 END) ELSE 0 END AS 予備金額１
            ,0 AS 予備金額２
            ,回収予定日
            ,0 AS 予備ＣＤ１
            ,0 AS 予備ＣＤ２
            ,'$TantoCd' AS 修正担当者ＣＤ
            ,'$date' AS 修正日
        FROM
            更新データ U1
        ";

        // var_export($sql);

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        //$DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        // DB::insert($sql);
    }

    /**
     * UpdateUriage
     */
    public function UpdateUriage($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDateMax = $vm->TargetDateMax;
        $CustomerList = is_array($vm->CustomerList) ? implode(",", $vm->CustomerList) : $vm->CustomerList;

        $sql = "
        UPDATE U1 SET 請求日付 = '$TargetDateMax'
        FROM 売上データ明細 U1
        LEFT JOIN 得意先マスタ T1 ON T1.得意先ＣＤ = U1.得意先ＣＤ
        WHERE
            U1.日付 >=
            (
            SELECT
                DATEADD(DAY, 1, MAX(B1.請求日付))
            FROM
                請求データ B1
            WHERE
                B1.請求先ＣＤ = T1.請求先ＣＤ
            AND B1.請求日付 < '$TargetDateMax'
            )
        AND U1.日付 <= '$TargetDateMax'
        AND U1.部署ＣＤ = $BushoCd
        AND U1.得意先ＣＤ IN ($CustomerList)
        AND U1.請求日付 = ''
        AND (U1.売掛現金区分 = 1)
        ";
        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);

        //モバイルSv更新用データを取得
        $sql2 = "
                    SELECT U1.*
                    FROM 売上データ明細 U1
                    LEFT JOIN 得意先マスタ T1 ON T1.得意先ＣＤ = U1.得意先ＣＤ
                    WHERE
                        U1.日付 >=
                        (
                        SELECT
                            DATEADD(DAY, 1, MAX(B1.請求日付))
                        FROM
                            請求データ B1
                        WHERE
                            B1.請求先ＣＤ = T1.請求先ＣＤ
                        AND B1.請求日付 < '$TargetDateMax'
                        )
                    AND U1.日付 <= '$TargetDateMax'
                    AND U1.部署ＣＤ = $BushoCd
                    AND U1.得意先ＣＤ IN ($CustomerList)
                    AND U1.請求日付 = '$TargetDateMax'
                    AND (U1.売掛現金区分 = 1)
                ";
        $stmt = $pdo->query($sql2);
        if (!!$stmt) {
            $this->UriageMeisaiData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $pdo = null;
    }

    /**
     * UpdateNyukin
     */
    public function UpdateNyukin($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDateMax = $vm->TargetDateMax;
        $CustomerList = is_array($vm->CustomerList) ? implode(",", $vm->CustomerList) : $vm->CustomerList;

        $sql = "
        UPDATE N1 SET 請求日付 = '$TargetDateMax'
        FROM
            入金データ N1
            LEFT JOIN 得意先マスタ T1 ON T1.得意先ＣＤ = N1.得意先ＣＤ
        WHERE
            N1.入金日付 >=
            (
            SELECT
                DATEADD(DAY, 1, MAX(B1.請求日付))
            FROM
                請求データ B1
            WHERE
                B1.請求先ＣＤ = T1.請求先ＣＤ
            AND B1.請求日付 < '$TargetDateMax'
            )
        AND N1.入金日付 <= '$TargetDateMax'
        AND N1.部署ＣＤ = $BushoCd
        AND N1.得意先ＣＤ IN ($CustomerList)
        AND N1.請求日付 = ''
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        //$DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        // DB::update($sql);
    }

    /**
     * UpdateSeikyuNo
     */
    public function UpdateSeikyuNo($vm)
    {
        $TargetDateMax = $vm->TargetDateMax;
        $SelectData = $this->SelectCustomerList($vm);

        $sql = "
        $SelectData
        UPDATE 請求番号管理
        SET 請求番号１ = 請求番号１ + (SELECT MAX(ISNULL(請求SEQ, 0)) FROM 更新データ)
        WHERE 請求管理No = 1
        ";

        $dsn = 'sqlsrv:server=localhost;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        //$DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        // DB::update($sql);
    }
}
