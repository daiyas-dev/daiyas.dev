<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI05110Controller extends Controller
{
    /**
     * SearchB
     */
    public function SearchB($vm)
    {
        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;

        $ShowSyonin = $vm->ShowSyonin;
        $Customer = $vm->Customer;
        $BushoCd = $vm->BushoCd;

        //TODO: 新規得意先の判断は、現行:指定期間初月に獲得した得意先, 指定期間内に獲得した得意先では？
        $WehreCustomer = $Customer == "1" ? "AND TOKUISAKI.新規登録日 >= '$DateStart' AND TOKUISAKI.新規登録日 <= DATEADD(DAY,-1,DATEADD(MONTH,1,'$DateStart'))" : "";
        // $WehreCustomer = $Customer == "1" ? "AND TOKUISAKI.新規登録日 >= '$DateStart' AND TOKUISAKI.新規登録日 <= '$DateEnd'" : "";

        $WehreShowSyonin = $ShowSyonin == "1" ? "AND TOKUISAKI.状態区分 IN (10, 20)" : "";
        $WehreCustomer = $Customer == "1" ? "AND TOKUISAKI.新規登録日 >= '$DateStart' AND TOKUISAKI.新規登録日 <= '$DateEnd'" : "";
        $WhereBushoCd = isset($BushoCd) ? "AND URIAGE_MEISAI.部署ＣＤ=$BushoCd" : "";


        $sql = "
            SELECT
                *
            FROM (
                SELECT DISTINCT
                    URIAGE_MEISAI.部署ＣＤ
                    , BUSYO.部署名
                    , (STR(TOKUISAKI.営業担当者ＣＤ) + STR(TOKUISAKI.獲得営業者ＣＤ)) AS 担当者ＣＤ
                    , TOKUISAKI.営業担当者ＣＤ
                    , TANTO.担当者名 AS 営業担当者名
                    , TOKUISAKI.獲得営業者ＣＤ
                    , TANTO2.担当者名 AS 獲得営業者名
                    , TOKUISAKI.得意先ＣＤ
                    , TOKUISAKI.得意先名
                    , DATEPART(MONTH, URIAGE_MEISAI.日付) AS 月
                    , SUM(URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額)
                        OVER(PARTITION BY URIAGE_MEISAI.得意先ＣＤ, DATEPART(MONTH, URIAGE_MEISAI.日付))
                        AS 金額
                    , TOKUISAKI.新規登録日
                FROM
                    売上データ明細 URIAGE_MEISAI
                    INNER JOIN 得意先マスタ TOKUISAKI ON
                    URIAGE_MEISAI.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                    LEFT JOIN 部署マスタ BUSYO ON
                    URIAGE_MEISAI.部署ＣＤ = BUSYO.部署CD
                    LEFT JOIN 担当者マスタ TANTO ON
                    TOKUISAKI.営業担当者ＣＤ = TANTO.担当者ＣＤ
                    LEFT JOIN 担当者マスタ TANTO2 ON
                    TOKUISAKI.獲得営業者ＣＤ = TANTO2.担当者ＣＤ
                WHERE
                        URIAGE_MEISAI.商品区分 IN (1,2,3,7)
                    --AND URIAGE_MEISAI.日付 >= '$DateStart' AND URIAGE_MEISAI.日付 <= '$DateEnd'
                    AND URIAGE_MEISAI.日付 >= '$DateStart' AND URIAGE_MEISAI.日付 <= DATEADD(DAY,-1,DATEADD(MONTH,6,'$DateStart'))
                    $WehreCustomer
                    $WehreShowSyonin
                    $WhereBushoCd
            ) X
            ORDER BY
                営業担当者ＣＤ,
                獲得営業者ＣＤ,
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
    //TODO
    // /**
    //  * Search
    //  */
    // public function Search($vm)
    // {
    //     $DateStart = $vm->DateStart;
    //     $DateEnd = $vm->DateEnd;
    //     $Customer = $vm->Customer;
    //     $ShowSyonin = $vm->ShowSyonin;

    //     $WehreCustomer = $Customer == "1" ? "AND CONVERT(VARCHAR, TOKUISAKI.新規登録日, 112) BETWEEN '$DateStart' AND DATEADD(DAY,-1,DATEADD(MONTH,1,'$DateStart'))" : "";
    //     $WehreShowSyonin = $ShowSyonin == "1" ? "AND TOKUISAKI.状態区分 IN (10, 20)" : "";

    //     $sql = "
    //     WITH 売上データ AS
    //     (
    //     select
    //         URIAGE_MEISAI.部署ＣＤ
    //         , BUSYO.部署名
    //         , (STR(TOKUISAKI.営業担当者ＣＤ) + STR(TOKUISAKI.獲得営業者ＣＤ)) as 担当者ＣＤ
    //         , TOKUISAKI.営業担当者ＣＤ
    //         , TANTO.担当者名 as 営業担当者名
    //         , TOKUISAKI.獲得営業者ＣＤ
    //         , TANTO2.担当者名 as 獲得営業者名
    //         , TOKUISAKI.得意先ＣＤ
    //         , TOKUISAKI.得意先名
    //         , SUM(CASE
    //             WHEN CONVERT(VARCHAR, URIAGE_MEISAI.日付, 112) BETWEEN
    //                 DATEADD(MONTH,0,'$DateStart') AND
    //                 DATEADD(DAY,-1,DATEADD(MONTH,1,'$DateStart'))
    //                 THEN
    //                 (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額)
    //             ELSE 0
    //             END) AS MONTH_1_金額
    //         , SUM(CASE
    //             WHEN CONVERT(VARCHAR, URIAGE_MEISAI.日付, 112) BETWEEN
    //                 DATEADD(MONTH,1,'$DateStart') AND
    //                 DATEADD(DAY,-1,DATEADD(MONTH,2,'$DateStart'))
    //                 THEN
    //                 (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額)
    //             ELSE 0
    //             END) AS MONTH_2_金額
    //         , SUM(CASE
    //             WHEN CONVERT(VARCHAR, URIAGE_MEISAI.日付, 112) BETWEEN
    //                 DATEADD(MONTH,2,'$DateStart') AND
    //                 DATEADD(DAY,-1,DATEADD(MONTH,3,'$DateStart'))
    //                 THEN
    //                 (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額)
    //             ELSE 0
    //             END) AS MONTH_3_金額
    //         , SUM(CASE
    //             WHEN CONVERT(VARCHAR, URIAGE_MEISAI.日付, 112) BETWEEN
    //                 DATEADD(MONTH,3,'$DateStart') AND
    //                 DATEADD(DAY,-1,DATEADD(MONTH,4,'$DateStart'))
    //                 THEN
    //                 (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額)
    //             ELSE 0
    //             END) AS MONTH_4_金額
    //         , SUM(CASE
    //             WHEN CONVERT(VARCHAR, URIAGE_MEISAI.日付, 112) BETWEEN
    //                 DATEADD(MONTH,4,'$DateStart') AND
    //                 DATEADD(DAY,-1,DATEADD(MONTH,5,'$DateStart'))
    //                 THEN
    //                 (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額)
    //             ELSE 0
    //             END) AS MONTH_5_金額
    //         , SUM(CASE
    //             WHEN CONVERT(VARCHAR, URIAGE_MEISAI.日付, 112) BETWEEN
    //                 DATEADD(MONTH,5,'$DateStart') AND
    //                 DATEADD(DAY,-1,DATEADD(MONTH,6,'$DateStart'))
    //                 THEN
    //                 (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額)
    //             ELSE 0
    //             END) AS MONTH_6_金額
    //         , SUM(CASE
    //             WHEN CONVERT(VARCHAR, URIAGE_MEISAI.日付, 112) BETWEEN
    //                 DATEADD(MONTH,0,'$DateStart') AND
    //                 DATEADD(DAY,0,DATEADD(MONTH,0,'$DateEnd'))
    //                 THEN
    //                 (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額)
    //             ELSE 0
    //             END) AS 累計_金額
    //         , CASE
    //             WHEN TOKUISAKI.新規登録日 BETWEEN
    //                 '$DateStart' AND
    //                 DATEADD(DAY,-1,DATEADD(MONTH,1,'$DateStart'))
    //                 THEN 1
    //             ELSE 0
    //             END AS MONTH_1_新規客件数
    //         , CASE
    //             WHEN TOKUISAKI.新規登録日 BETWEEN
    //                 DATEADD(MONTH,1,'$DateStart') AND
    //                 DATEADD(DAY,-1,DATEADD(MONTH,2,'$DateStart'))
    //                 THEN 1
    //             ELSE 0
    //             END AS MONTH_2_新規客件数
    //         , CASE
    //             WHEN TOKUISAKI.新規登録日 BETWEEN
    //                 DATEADD(MONTH,2,'$DateStart') AND
    //                 DATEADD(DAY,-1,DATEADD(MONTH,3,'$DateStart'))
    //                 THEN 1
    //             ELSE 0
    //             END AS MONTH_3_新規客件数
    //         , CASE
    //             WHEN TOKUISAKI.新規登録日 BETWEEN
    //                 DATEADD(MONTH,3,'$DateStart') AND
    //                 DATEADD(DAY,-1,DATEADD(MONTH,4,'$DateStart'))
    //                 THEN 1
    //             ELSE 0
    //             END AS MONTH_4_新規客件数
    //         , CASE
    //             WHEN TOKUISAKI.新規登録日 BETWEEN
    //                 DATEADD(MONTH,4,'$DateStart') AND
    //                 DATEADD(DAY,-1,DATEADD(MONTH,5,'$DateStart'))
    //                 THEN 1
    //             ELSE 0
    //             END AS MONTH_5_新規客件数
    //         , CASE
    //             WHEN TOKUISAKI.新規登録日 BETWEEN
    //                 DATEADD(MONTH,5,'$DateStart') AND
    //                 DATEADD(DAY,-1,DATEADD(MONTH,6,'$DateStart'))
    //                 THEN 1
    //             ELSE 0
    //             END AS MONTH_6_新規客件数
    //         , CASE
    //             WHEN TOKUISAKI.新規登録日 BETWEEN
    //                 '$DateStart' AND
    //                 DATEADD(DAY,-1,DATEADD(MONTH,6,'$DateStart'))
    //                 THEN 1
    //             ELSE 0
    //             END AS 累計_新規客件数
    //     from
    //         売上データ明細 URIAGE_MEISAI
    //         inner join 得意先マスタ TOKUISAKI on
    //         URIAGE_MEISAI.得意先ＣＤ = TOKUISAKI.得意先ＣＤ --AND URIAGE_MEISAI.部署ＣＤ = TOKUISAKI.部署ＣＤ
    //         left join 部署マスタ BUSYO on
    //         URIAGE_MEISAI.部署ＣＤ = BUSYO.部署CD
    //         left join 担当者マスタ TANTO on
    //         TOKUISAKI.営業担当者ＣＤ = TANTO.担当者ＣＤ
    //         left join 担当者マスタ TANTO2 on
    //         TOKUISAKI.獲得営業者ＣＤ = TANTO2.担当者ＣＤ
    //     where
    //             URIAGE_MEISAI.商品区分 IN (1,2,3,7)
    //         AND URIAGE_MEISAI.日付 >= '$DateStart' AND URIAGE_MEISAI.日付 <= '$DateEnd'
    //         $WehreCustomer
    //         $WehreShowSyonin
    //     GROUP BY
    //          URIAGE_MEISAI.部署ＣＤ
    //         ,BUSYO.部署名
    //         ,(STR(TOKUISAKI.営業担当者ＣＤ) + STR(TOKUISAKI.獲得営業者ＣＤ))
    //         ,TOKUISAKI.営業担当者ＣＤ
    //         ,TANTO.担当者名
    //         ,TOKUISAKI.獲得営業者ＣＤ
    //         ,TANTO2.担当者名
    //         ,TOKUISAKI.得意先ＣＤ
    //         ,TOKUISAKI.得意先名
    //         ,TOKUISAKI.新規登録日
    //     )

    //     SELECT * FROM 売上データ
    //     order by
    //         営業担当者ＣＤ,獲得営業者ＣＤ,得意先ＣＤ
    //     ";

    //     //$DataList = DB::select($sql);
    //     $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
    //     $user = 'daiyas';
    //     $password = 'daiyas';

    //     $pdo = new PDO($dsn, $user, $password);
    //     $stmt = $pdo->query($sql);
    //     $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //     $pdo = null;

    //     return response()->json($DataList);
    // }
}
