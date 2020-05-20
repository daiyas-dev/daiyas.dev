<?php

namespace App\Http\Controllers;

use App\Models\コーステーブル;
use App\Models\各種テーブル;
use App\Models\商品マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Facades\DB as IlluminateDB;
use PDO;
use SebastianBergmann\Environment\Console;

class DAI01210Controller extends Controller
{
    /**
     * GetProductList
     */
    public function GetProductList($request)
    {
        $BushoCd = $request->BushoCd;

        $sql = "
            WITH SUB AS (
                SELECT
                    サブ各種CD2
                FROM 各種テーブル
                WHERE 各種CD=26
                AND	サブ各種CD1=$BushoCd
            )
            SELECT TOP(7)
                各種テーブル.サブ各種CD2 AS 商品区分,
                各種テーブル.各種名称 AS 商品名
            FROM 各種テーブル, SUB
            WHERE 各種CD = (
                CASE
                    WHEN SUB.サブ各種CD2=2 THEN 27
                    WHEN SUB.サブ各種CD2 IS NOT NULL THEN 14
                    ELSE 33
                END
            )
        ";

        $DataList = DB::select($sql);
        // $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        // $user = 'daiyas';
        // $password = 'daiyas';

        // $pdo = new PDO($dsn, $user, $password);
        // $stmt = $pdo->query($sql);
        // $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // $pdo = null;

        return response()->json($DataList);
    }

    /**
     * GetDataList
     */
    public function GetDataList($request)
    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;

        $PrintOrder = $request->PrintOrder;
        $BaseTable = $PrintOrder == '0' ? "得意先" : "コース";
        $BaseCd = $PrintOrder == '0' ? "得意先ＣＤ" : "コースＣＤ";

        $CourseCd = $request->CourseCd;
        $WhereCourseCd = isset($CourseCd) ? "AND コースＣＤ = $CourseCd" : "";

        $CustomerCd = $request->CustomerCd;
        $WhereCustomerCd = isset($CustomerCd) ? "AND 得意先マスタ.得意先ＣＤ = $CustomerCd" : "";

        $RegistDate = $request->RegistDate;
        $WhereRegistDate = isset($RegistDate) ? "AND (得意先マスタ.新規登録日 >= DATEADD(dd, 1, EOMONTH ('$RegistDate' , -1)) AND 得意先マスタ.新規登録日 <= EOMONTH('$RegistDate'))" : "";

        $TantoCd = $request->TantoCd;
        $WhereTantoCd = isset($TantoCd) ? "AND 得意先マスタ.獲得営業者ＣＤ = $TantoCd" : "";

        $sql = "
            WITH
            部署SUB AS (
                SELECT サブ各種CD2 FROM 各種テーブル WHERE 各種テーブル.各種CD = 26 AND 各種テーブル.サブ各種CD1 = $BushoCd
            ),
            商品区分マスタ AS (
                SELECT TOP(7)
                    各種テーブル.サブ各種CD2 AS 商品区分,
                    各種テーブル.各種名称 AS 商品名,
                    各種テーブル.行NO
                FROM
                    各種テーブル, 部署SUB SUB
                WHERE 各種CD = (
                    CASE
                        WHEN SUB.サブ各種CD2=2 THEN 27
                        WHEN SUB.サブ各種CD2 IS NOT NULL THEN 14
                        ELSE 33
                    END
                )
            ),
            抽出対象コース AS (
                SELECT
                    コースＣＤ AS 対象CD,
                    コース名 AS 対象名,
                    コースＣＤ AS ソート順,
                    コースＣＤ AS ソート順2
                FROM
                    コースマスタ
                    INNER JOIN 担当者マスタ
                        ON コースマスタ.担当者ＣＤ = 担当者マスタ.担当者ＣＤ
                WHERE
                    部署ＣＤ = $BushoCd
                $WhereCourseCd
            ),
            抽出対象得意先 AS (
                SELECT
                    対象CD,
                    対象名,
                    ソート順,
                    ソート順2
                FROM
                (
                    SELECT DISTINCT
                        得意先マスタ.得意先ＣＤ AS 対象CD,
                        得意先マスタ.得意先名略称 AS 対象名,
                        FIRST_VALUE(コーステーブル.コースＣＤ) OVER(PARTITION BY 得意先マスタ.得意先ＣＤ ORDER BY コーステーブル.コースＣＤ) AS ソート順,
            			FIRST_VALUE(コーステーブル.ＳＥＱ) OVER(PARTITION BY 得意先マスタ.得意先ＣＤ ORDER BY コーステーブル.コースＣＤ, コーステーブル.ＳＥＱ) AS ソート順2
                    FROM
                        得意先マスタ
                        INNER JOIN コーステーブル
                            ON コーステーブル.得意先ＣＤ = 得意先マスタ.得意先ＣＤ
                        INNER JOIN 抽出対象コース
                            ON 抽出対象コース.対象CD = コーステーブル.コースＣＤ
                    WHERE
                        得意先マスタ.部署ＣＤ = $BushoCd
                    $WhereCustomerCd
                    $WhereRegistDate
                    $WhereTantoCd
                ) TMP
            ),
            抽出対象 AS (
                SELECT * FROM 抽出対象$BaseTable -- 抽出対象コース or 抽出対象得意先
            ),
            売上データ AS (
                SELECT
                    uriage.日付,
                    target.対象CD,
                    uriage.商品区分,
                    SUM(ISNULL(uriage.現金個数, 0) + ISNULL(uriage.掛売個数, 0) + ISNULL(uriage.分配元数量, 0)) AS 個数,
                    uriage.売掛現金区分
                FROM [売上データ明細] uriage
                    inner join 得意先マスタ tokui
                        on tokui.得意先ＣＤ = uriage.得意先ＣＤ and tokui.部署CD = uriage.部署ＣＤ
                    inner join 抽出対象 target
                        on target.対象CD = uriage.$BaseCd -- コースＣＤ or 得意先ＣＤ
                WHERE
                uriage.部署ＣＤ = $BushoCd
                AND (tokui.得意先ＣＤ = tokui.受注得意先ＣＤ OR tokui.受注得意先ＣＤ = 0)
                AND (日付 >= DATEADD(dd, 1, EOMONTH ('$TargetDate' , -1)) AND 日付 <= EOMONTH('$TargetDate'))
                GROUP BY
                    uriage.日付,
                    target.対象CD,
                    uriage.商品区分,
                    uriage.売掛現金区分
            ),
            抽出データ AS (
                SELECT
                    BASE.対象CD AS グループKEY,
                    商品区分マスタ.商品区分 AS 商品KEY,
                    DAY(売上データ.日付) as 日付,
                    売上データ.個数
                FROM 抽出対象 BASE
                    LEFT JOIN 売上データ
                        ON BASE.対象CD = 売上データ.対象CD
                    LEFT JOIN 商品区分マスタ
                        ON 売上データ.商品区分 = 商品区分マスタ.商品区分
            ),
            対象期間 AS
            (
                SELECT DATEADD(DAY, 1, EOMONTH ('$TargetDate' , -1)) AS STARTDATE
                UNION ALL
                SELECT DATEADD(DAY, 1, STARTDATE) FROM 対象期間
                WHERE
                    MONTH(STARTDATE) = MONTH('$TargetDate')
            ),
            対象期間曜日カウント AS
            (
                SELECT
                    DATEPART(WEEKDAY, STARTDATE) AS PART,
                    COUNT(*) AS CNT
                FROM
                    対象期間
                WHERE
                    MONTH(STARTDATE) = MONTH('$TargetDate')
                GROUP BY
                    DATEPART(WEEKDAY, STARTDATE)
            ),
            日毎抽出データ AS (
                select * from 抽出データ
                PIVOT ( SUM( 個数 ) FOR 日付 in([1], [2], [3], [4], [5], [6], [7], [8], [9], [10],
                                                    [11], [12], [13], [14], [15], [16], [17], [18], [19], [20],
                                                    [21], [22], [23], [24], [25], [26], [27], [28], [29], [30], [31]
                                                    )) AS ピボットテーブル
            ),
            日毎合計抽出データ AS (
                select グループKEY, 商品KEY, SUM(個数) as 合計
                from 抽出データ
                group by グループKEY, 商品KEY
            ),
            日毎平均抽出データ AS (
                select グループKEY, 商品KEY, SUM(個数) / (SELECT DAY(EOMONTH('$TargetDate'))) as 平均
                from 抽出データ
                group by グループKEY, 商品KEY
            ),
            土曜合計抽出データ AS (
                select グループKEY, 商品KEY, SUM(個数) as 土曜合計
                from 抽出データ
                where DATEPART(WEEKDAY, DATEADD(DAY, 日付 - 1, '$TargetDate')) = 7
                group by グループKEY, 商品KEY
            ),
            土曜平均抽出データ AS (
                select グループKEY, 商品KEY, SUM(個数) / (SELECT CNT FROM 対象期間曜日カウント WHERE PART=7) as 土曜平均
                from 抽出データ
                where DATEPART(WEEKDAY, DATEADD(DAY, 日付 - 1, '$TargetDate')) = 7
                group by グループKEY, 商品KEY
            ),
            日曜合計抽出データ AS (
                select グループKEY, 商品KEY, SUM(個数) as 日曜合計
                from 抽出データ
                where DATEPART(WEEKDAY, DATEADD(DAY, 日付 - 1, '$TargetDate')) = 1
                group by グループKEY, 商品KEY
            ),
            日曜平均抽出データ AS (
                select グループKEY, 商品KEY, SUM(個数) / (SELECT CNT FROM 対象期間曜日カウント WHERE PART=1) as 日曜平均
                from 抽出データ
                where DATEPART(WEEKDAY, DATEADD(DAY, 日付 - 1, '$TargetDate')) = 1
                group by グループKEY, 商品KEY
            ),
            集計データ AS (
                select
                    抽出対象.対象CD,
                    抽出対象.対象名,
                    商品区分マスタ.商品区分 AS 商品区分,
                    商品区分マスタ.商品名 AS 商品名,
                    商品区分マスタ.行NO,
                    抽出対象.ソート順,
                    抽出対象.ソート順2
                from
                    抽出対象
                    CROSS JOIN 商品区分マスタ
            )

            select 集計データ.*, 日毎抽出データ.*, 合計, 平均, 土曜合計, 土曜平均, 日曜合計, 日曜平均 ,過去明細有無.過去明細 as 売上データ明細有無
            from 集計データ
            left join 日毎抽出データ on 集計データ.対象CD = 日毎抽出データ.グループKEY and 集計データ.商品区分 = 日毎抽出データ.商品KEY
            left join 日毎合計抽出データ on 集計データ.対象CD = 日毎合計抽出データ.グループKEY and 集計データ.商品区分 = 日毎合計抽出データ.商品KEY
            left join 日毎平均抽出データ on 集計データ.対象CD = 日毎平均抽出データ.グループKEY and 集計データ.商品区分 = 日毎平均抽出データ.商品KEY
            left join 土曜合計抽出データ on 集計データ.対象CD = 土曜合計抽出データ.グループKEY and 集計データ.商品区分 = 土曜合計抽出データ.商品KEY
            left join 土曜平均抽出データ on 集計データ.対象CD = 土曜平均抽出データ.グループKEY and 集計データ.商品区分 = 土曜平均抽出データ.商品KEY
            left join 日曜合計抽出データ on 集計データ.対象CD = 日曜合計抽出データ.グループKEY and 集計データ.商品区分 = 日曜合計抽出データ.商品KEY
            left join 日曜平均抽出データ on 集計データ.対象CD = 日曜平均抽出データ.グループKEY and 集計データ.商品区分 = 日曜平均抽出データ.商品KEY
            left join (
                select
                    得意先ＣＤ,
                    IIF(sum(現金個数 + 掛売個数) = 0, 0, 1) as 過去明細
                from 売上データ明細
                group by 得意先ＣＤ
                ) as 過去明細有無　on 集計データ.対象CD = 得意先ＣＤ
            order by 集計データ.ソート順, 集計データ.ソート順2, 集計データ.行NO
        ";

        $DataList = DB::select($sql);
        // $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        // $user = 'daiyas';
        // $password = 'daiyas';

        // $pdo = new PDO($dsn, $user, $password);
        // $stmt = $pdo->query($sql);
        // $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // $pdo = null;

        return $DataList;
    }

    /**
     * Search
     */
    public function Search($request) {
        return response()->json($this->GetDataList($request));
    }
}
