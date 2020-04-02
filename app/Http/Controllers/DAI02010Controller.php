<?php

namespace App\Http\Controllers;

use App\Models\請求データ;
use App\Models\売上データ明細;
use App\Models\入金データ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use PDO;
use Symfony\Component\Console\Helper\TableRows;

class DAI02010Controller extends Controller
{

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
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;
        $CourseCd = $request->CourseCd;
        $TargetDateMax = $request->TargetDateMax;
        $Target = $request->Target;
        $CustomerList = implode(",", $request->CustomerList);

        DB::beginTransaction();

        $date = Carbon::now()->format('Y-m-d H:i:s');

        // $list = collect($this->SelectData($request))
        //     ->map(function($row) use($date) {
        //         $row->修正日 = $date;
        //         return $row;
        //     })
        //     ->values()
        //     ->toArray()
        //     ;

        try {

            //請求データ::query($list)->delete();
            //請求データ::query()->hydrate($list)->insert($list);
            //請求データ::query()->insert(["請求日付" => $TargetDate, "部署ＣＤ" => 101, "請求先ＣＤ" => 1, "修正日" => $date]);
            //請求データ::query($list)->insert();
            //DB::table('請求データ')->insert($list);

            // $this->DeleteSeikyu($request);
            // $this->DeleteUriage($request);
            // $this->DeleteNyukin($request);

            $this->UpdateSeikyu($request);
            $this->InsertSeikyu($request);
            $this->UpdateUriage($request);
            $this->UpdateNyukin($request);

            DB::rollback();

            // if ($edited) {
            //     DB::rollBack();
            // } else {
            //     DB::commit();
            // }
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
        $SimeKbn = $vm->SimeKbn;
        $SimeDate = $vm->SimeDate;
        $TargetDate = $vm->TargetDate;
        $TargetDateMax = $vm->TargetDateMax;

        $WhereSimeDate = $SimeKbn == 2
            ? "AND (M1.締日１= $SimeDate OR M1.締日２ = $SimeDate)"
            : "";
        $WhereTargetDate = $SimeKbn == 2
            ? "AND (T1.請求日付 = DATEADD(dd, $SimeDate, EOMONTH ('$TargetDate' , -1)))"
            : "AND T1.請求日付 = '$TargetDateMax'";

        $BushoCd = $vm->BushoCd;

        $CourseCd = $vm->CourseCd;
        $WehreCourseCd = !!$CourseCd ? " AND COU.コースＣＤ = $CourseCd" : "";
        $WehreCourseCd2 = str_replace('COU', 'COUT', $WehreCourseCd);

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
                $WehreCourseCd
                AND COUM.コース区分 IN (1,2,3)
                GROUP BY  COU.部署ＣＤ, COU.得意先ＣＤ
            ) COU_KEY ON
                COU_KEY.部署ＣＤ = T1.部署ＣＤ
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
        $WhereSqlCourse= !!$CourseCd ? $sqlCourse : "";

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
                $WhereSqlCourse
            WHERE
                T1.部署ＣＤ = $BushoCd
            $WhereTargetDate
            $WehreCustomerCd
            $WehreCourseCd2
        )

        SELECT
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
            ,T1.*
        FROM
            得意先マスタ M1
            LEFT OUTER JOIN 属性データ T1 ON T1.請求先ＣＤ = M1.請求先ＣＤ
        WHERE
            M1.締区分 IN ($SimeKbn)
        AND M1.部署CD = $BushoCd
        AND M1.得意先ＣＤ >= 0
        AND M1.得意先ＣＤ <= 9999999
        AND M1.売掛現金区分 IN (0,1,2)
        AND M1.得意先ＣＤ = M1.請求先ＣＤ
        ";

        $dsn = 'sqlsrv:server=localhost;database=daiyas';
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
     * SelectCustomerList
     */
    public function SelectCustomerList($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDateMax = $vm->TargetDateMax;
        $CustomerList = implode(",", $vm->CustomerList);

        $sql = "
        WITH 更新前データ1 AS
        (
        SELECT
             T1.得意先ＣＤ
            ,T1.得意先名
            ,T1.売掛現金区分
            ,T1.締区分
            ,T1.締日１
            ,T1.締日２
            ,T1.支払サイト
            ,T1.支払日
            ,T1.集金区分
            ,T1.税区分
            ,T1.部署ＣＤ
            ,B1.請求先ＣＤ
            ,B1.今回入金額
            ,B1.今回売上額
            ,B1.消費税額
            ,B1.今回請求額
            ,B1.回収予定日
            ,CASE WHEN B1.請求先ＣＤ IS NOT NULL THEN ISNULL(B1.[３回前残高] + B1.前々回残高, 0) ELSE 0 END AS num1
            ,CASE WHEN B1.請求先ＣＤ IS NOT NULL THEN ISNULL(B1.前回残高, 0) ELSE 0 END AS num2
            ,CASE WHEN B1.請求先ＣＤ IS NOT NULL THEN ISNULL(B1.今回売上額 + B1.消費税額, 0) ELSE 0 END AS num8
            ,CASE WHEN B1.請求先ＣＤ IS NULL THEN DATEADD(DAY, 1, DATEADD(MONTH, -1, '$TargetDateMax')) ELSE B1.請求日付 END AS 請求日付
            ,CASE WHEN B1.請求先ＣＤ IS NOT NULL THEN ISNULL(B1.今回入金額, 0) ELSE 0 END AS num9
            ,CASE WHEN B1.請求先ＣＤ IS NOT NULL THEN ISNULL(B1.今回請求額, 0) ELSE 0 END AS num10
            --,U1.得意先ＣＤ
            ,U1.現金個数
            ,U1.現金金額
            ,U1.現金値引
            ,U1.掛売個数
            ,U1.掛売金額
            ,U1.掛売値引
            ,CASE WHEN U1.得意先ＣＤ IS NOT NULL THEN ISNULL(U1.掛売金額 - U1.掛売値引, 0) ELSE 0 END AS num5
            ,CASE WHEN U1.得意先ＣＤ IS NOT NULL THEN ISNULL(N1.現金 + N1.小切手 + N1.振込 + N1.その他 + N1.バークレー + N1.相殺 + N1.値引, 0) ELSE 0 END AS num4
        FROM
            得意先マスタ T1

        --◆請求データ取得
        LEFT JOIN 請求データ B1 ON
            B1.部署ＣＤ = T1.部署ＣＤ
        AND B1.請求先ＣＤ = T1.請求先ＣＤ
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

        --◆売上データ明細取得
        LEFT JOIN
            (
            SELECT
                 得意先ＣＤ
                ,SUM(現金個数) AS 現金個数
                ,SUM(現金金額) AS 現金金額
                ,SUM(現金値引) AS 現金値引
                ,SUM(掛売個数) AS 掛売個数
                ,SUM(掛売金額) AS 掛売金額
                ,SUM(掛売値引) AS 掛売値引
            FROM 売上データ明細
            WHERE
                日付 >= 請求日付 AND 日付 <= '$TargetDateMax'
            AND (売掛現金区分 = 1)
            AND 部署ＣＤ = $BushoCd
            GROUP BY 得意先ＣＤ
            ) U1 ON
                U1.得意先ＣＤ = T1.得意先ＣＤ

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
                入金データ
            WHERE
            --    得意先ＣＤ = 1
            --AND 入金日付 >= 請求日付 AND 入金日付 <= '$TargetDateMax'
                入金日付 >= 請求日付 AND 入金日付 <= '$TargetDateMax'
            GROUP BY
                得意先ＣＤ
            ) N1 ON
                N1.得意先ＣＤ = T1.得意先ＣＤ
        WHERE
            T1.請求先ＣＤ IN ($CustomerList)
        ),

        更新前データ2 AS
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
            ,CASE WHEN (num1 != 0 AND num9 != 0) AND (num1 > num9) THEN num1 - num9 ELSE 0 END AS num1
            ,CASE WHEN (num2 != 0 AND num9 != 0) AND (num2 > num9) THEN num2 - num9 ELSE 0 END AS num2
            ,num10 AS num3
            ,num4
            ,num5
            ,CASE WHEN (num5 != 0) THEN num5 * 8 / 100 ELSE 0 END AS num6
            ,CASE WHEN (num8 != 0 AND num9 != 0) AND (num8 <= num9) THEN num8 - num9 ELSE 0 END AS num7
            ,num8
            ,CASE WHEN (num1 != 0 AND num9 != 0) AND (num1 <= num9) THEN num9 - num1 ELSE 0 END AS num9
            ,CASE WHEN (num1 != 0) THEN num10 - num1 ELSE 0 END AS num10
        FROM
            更新前データ1
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
            ,回収予定日
            ,num1
            ,num2
            ,CASE WHEN (num1 != 0) THEN num10 ELSE 0 END AS num3
            ,num4
            ,num5
            ,num6
            ,num7
            ,num8
            ,CASE WHEN (num2 != 0 AND num9 != 0) AND (num2 <= num9) THEN num9 - num2 ELSE 0 END AS num9
            ,num10
            ,num1 + num2 + num3 - num4 - CASE WHEN 請求先ＣＤ IS NOT NULL THEN 今回入金額 ELSE 0 END AS num11
            ,num1 + num2 + num3 - num4 AS num13
        FROM
            更新前データ2
        ),

        更新前データ4 AS
        (
        SELECT
             請求日付
            ,得意先ＣＤ
            ,部署ＣＤ
            ,請求先ＣＤ
            ,今回入金額
            ,今回売上額
            ,消費税額
            ,今回請求額
            ,回収予定日
            ,num1
            ,num2
            ,CASE WHEN (num2 != 0) THEN num10 - num2 ELSE 0 END AS num3
            ,num4
            ,num5
            ,num6
            ,num7
            ,num8
            ,num9
            ,num10
            ,num11
            ,num11 + num5 + CASE WHEN 請求先ＣＤ IS NOT NULL THEN 今回売上額 ELSE num6 END AS num12
            ,num13
            ,num13 + num5 AS num14
        FROM
            更新前データ3
        ),

        更新データ AS
        (
        SELECT
             請求日付
            ,得意先ＣＤ
            ,部署ＣＤ
            ,請求先ＣＤ
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
            ,CASE WHEN NOT(請求先ＣＤ != 0 AND 請求先ＣＤ != 得意先ＣＤ) THEN num1 + num2 + num3 - num4 - 今回入金額 ELSE num8 END AS num8
            ,CASE WHEN NOT(請求先ＣＤ != 0 AND 請求先ＣＤ != 得意先ＣＤ) THEN num8 + num5 + num6 + 今回売上額 + 消費税額 ELSE num9 END AS num9
            ,num10
            ,num11
            ,num12
            ,CASE WHEN NOT(請求先ＣＤ != 0 AND 請求先ＣＤ != 得意先ＣＤ) THEN num13 ELSE 0 END AS num13
            ,CASE WHEN NOT(請求先ＣＤ != 0 AND 請求先ＣＤ != 得意先ＣＤ) THEN num14 ELSE 0 END AS num14
        FROM
            更新前データ4
        )
        ";

        return $sql;
    }

    /**
     * SelectData
     */
    public function SelectData($vm)
    {
        $SelectData = $this->SelectCustomerList($vm);

        $sql = "
        $SelectData
        SELECT * FROM 更新データ
        ";

        return DB::select($sql);
    }

    /**
     * DeleteSeikyu
     */
    public function DeleteSeikyu($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDateMax = $vm->TargetDateMax;
        $CustomerList = implode(",", $vm->CustomerList);

        $sql = "
        DELETE 請求データ
        WHERE
            請求日付 = '$TargetDateMax'
        AND 部署ＣＤ = $BushoCd
        AND 請求先ＣＤ IN ($CustomerList)
        ";

        DB::delete($sql);
    }

    /**
     * DeleteUriage
     */
    public function DeleteUriage($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDateMax = $vm->TargetDateMax;
        $CustomerList = implode(",", $vm->CustomerList);

        $sql = "
        DELETE U1
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
        AND U1.請求日付 = '' AND ( U1.売掛現金区分 = 1 )
        ";

        DB::delete($sql);
    }

    /**
     * DeleteNyukin
     */
    public function DeleteNyukin($vm)
    {
        $TargetDateMax = $vm->TargetDateMax;
        $CustomerList = implode(",", $vm->CustomerList);

        $sql = "
        DELETE N1
        FROM 入金データ N1
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
        AND N1.得意先ＣＤ IN ($CustomerList)
        AND N1.請求日付 = ''
        ";

        DB::delete($sql);
    }

    /**
     * UpdateSeikyu
     */
    public function UpdateSeikyu($vm)
    {
        $TargetDateMax = $vm->TargetDateMax;
        $date = Carbon::now()->format('Y-m-d H:i:s');
        $CustomerList = implode(",", $vm->CustomerList);
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

        DB::update($sql);
    }

    /**
     * InsertSeikyu
     */
    public function InsertSeikyu($vm)
    {
        $TargetDateMax = $vm->TargetDateMax;
        $date = Carbon::now()->format('Y-m-d H:i:s');
        $CustomerList = implode(",", $vm->CustomerList);
        $SelectData = $this->SelectCustomerList($vm);

        $sql = "
        $SelectData
        INSERT INTO 請求データ
        SELECT
			 '$TargetDateMax' AS 請求日付
            ,部署ＣＤ
            ,CASE WHEN NOT(請求先ＣＤ != 0 AND 請求先ＣＤ != 得意先ＣＤ) THEN 得意先ＣＤ ELSE 請求先ＣＤ END AS 請求先ＣＤ
            ,U1.num1 AS [３回前残高]
            ,num2 AS 前々回残高
            ,num3 AS 前回残高
            ,num4 AS 今回入金額
            ,CASE WHEN NOT(請求先ＣＤ != 0 AND 請求先ＣＤ != 得意先ＣＤ) THEN num13 ELSE num11 END AS 差引繰越額
            ,num5 AS 今回売上額
            ,num6 AS 消費税額
            ,CASE WHEN NOT(請求先ＣＤ != 0 AND 請求先ＣＤ != 得意先ＣＤ) THEN num14 ELSE num12 END AS 今回請求額
            ,請求日付 AS 請求日範囲開始
            ,'$TargetDateMax' AS 請求日範囲終了
            ,ROW_NUMBER() OVER(ORDER BY 請求日付, 部署ＣＤ, 請求先ＣＤ) + (Select 請求番号１ + 1 From 請求番号管理 WHERE 請求管理No = 1) AS 予備金額１
            ,0 AS 予備金額２
            ,回収予定日
            ,0 AS 予備ＣＤ１
            ,0 AS 予備ＣＤ２
            ,'' AS 修正担当者ＣＤ
            ,'$date' AS 修正日
		FROM 更新データ U1
        WHERE
			NOT EXISTS
        (
        SELECT *
        FROM
            請求データ B1
        WHERE
            B1.請求先ＣＤ = U1.請求先ＣＤ
        AND B1.請求先ＣＤ IN ($CustomerList)
        )";

        DB::insert($sql);
    }

    /**
     * UpdateUriage
     */
    public function UpdateUriage($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDateMax = $vm->TargetDateMax;
        $CustomerList = implode(",", $vm->CustomerList);

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

        DB::update($sql);
    }

    /**
     * UpdateNyukin
     */
    public function UpdateNyukin($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDateMax = $vm->TargetDateMax;
        $CustomerList = implode(",", $vm->CustomerList);

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

        DB::update($sql);
    }
}
