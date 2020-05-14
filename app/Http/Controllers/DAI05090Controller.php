<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI05090Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $BushoCd = $vm->BushoCd;
        $WhereBushoCd = isset($BushoCd) ? "AND TOKUISAKI.部署ＣＤ=$BushoCd" : "";

        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;

        $SaveDateStart = $vm->SaveDateStart;
        $SaveDateEnd = $vm->SaveDateEnd;

        $Customer = $vm->Customer;
        $ShowSyonin = $vm->ShowSyonin;

        $WehreCustomer = $Customer == "1" ? "AND TOKUISAKI.新規登録日 >= '$SaveDateStart' AND TOKUISAKI.新規登録日 <= '$SaveDateEnd'" : "";
        $WehreShowSyonin = $ShowSyonin == "1" ? "AND TOKUISAKI.状態区分 IN (10, 20)" : "";


        $sql = "
        WITH 売上データ AS
        (
        select
            URIAGE_MEISAI.部署ＣＤ
            , BUSYO.部署ＣＤ as 部署ＣＤ2
            , BUSYO.部署名
            , URIAGE_MEISAI.日付
            , (STR(TOKUISAKI.営業担当者ＣＤ) + STR(TOKUISAKI.獲得営業者ＣＤ)) as 担当者ＣＤ
            , TOKUISAKI.営業担当者ＣＤ
            , TANTO.担当者名 as 営業担当者名
            , TOKUISAKI.獲得営業者ＣＤ
            , TANTO2.担当者名 as 獲得営業者名
            , TOKUISAKI.得意先ＣＤ
            , TOKUISAKI.得意先名
            , URIAGE_MEISAI.現金個数
            , URIAGE_MEISAI.掛売個数
            , (URIAGE_MEISAI.現金個数 + URIAGE_MEISAI.掛売個数) as 売上個数
            , URIAGE_MEISAI.現金金額
            , URIAGE_MEISAI.掛売金額
            , (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額) as 売上金額
            , TOKUISAKI.新規登録日
            , TOKUISAKI.状態区分
        from

            -- 2015/08/25 売上の無い得意先も表示する S.Nakahara Rep Start
            --売上データ明細 URIAGE_MEISAI
            --inner join 得意先マスタ TOKUISAKI on
            --URIAGE_MEISAI.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
            --
            得意先マスタ TOKUISAKI
            left join 売上データ明細 URIAGE_MEISAI on
                URIAGE_MEISAI.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
            and URIAGE_MEISAI.日付 >= '$DateStart'
            and URIAGE_MEISAI.日付 <= '$DateEnd'
            and URIAGE_MEISAI.商品区分 in (1,2,3,7)
            -- 2015/08/25 売上の無い得意先も表示する S.Nakahara Rep End

            left join 部署マスタ BUSYO on
                TOKUISAKI.部署ＣＤ = BUSYO.部署CD
            left join 担当者マスタ TANTO on
                TOKUISAKI.営業担当者ＣＤ = TANTO.担当者ＣＤ
            left join 担当者マスタ TANTO2 on
                TOKUISAKI.獲得営業者ＣＤ = TANTO2.担当者ＣＤ
        where
            -- 2015/08/25 売上の無い得意先も表示する S.Nakahara Rep Start
            --CONVERT(VARCHAR, URIAGE_MEISAI.日付, 112) >= '20190401'
            --and CONVERT(VARCHAR, URIAGE_MEISAI.日付, 112) <= '20190430'
            --
            -- 2015/05/28 商品区分は(1,2,3,7)が対象 S.Nakahara Add Start 10⇒7に修正(別府)
            --and URIAGE_MEISAI.商品区分 in (1,2,3,7)
            -- 2015/05/28 商品区分は(1,2,3,7)が対象 S.Nakahara Add End
            0 = 0
            -- 2015/08/25 売上の無い得意先も表示する S.Nakahara Rep End
            $WhereBushoCd
            $WehreCustomer
            $WehreShowSyonin
        ),

		日毎集計データ AS
		(
		SELECT
             部署ＣＤ
            ,部署ＣＤ2
            ,部署名
            ,営業担当者ＣＤ
			,営業担当者名
			,獲得営業者ＣＤ
			,獲得営業者名
			,得意先ＣＤ
			,得意先名
			,DAY(日付) AS 日
			,IIF(DATEPART(WEEKDAY,日付) >= 2 AND DATEPART(WEEKDAY,日付) <= 6, 1, 0) AS 平日
			,SUM(IIF(DATEPART(WEEKDAY,日付) >= 2 AND DATEPART(WEEKDAY,日付) <= 6, 売上個数, 0)) AS 平日売上個数
			,SUM(売上個数) AS 売上個数
			,SUM(ISNULL(売上個数, 0)) AS 得意先合計
			,SUM(ISNULL(売上金額, 0)) AS 得意先売上金額
		FROM
			売上データ
		GROUP BY
			部署ＣＤ, 部署ＣＤ2, 部署名, 営業担当者ＣＤ, 営業担当者名, 獲得営業者ＣＤ, 獲得営業者名, 得意先ＣＤ, 得意先名, 日付
		),

		集計データ AS
		(
		SELECT
             部署ＣＤ
			,営業担当者ＣＤ
			,営業担当者名
			,獲得営業者ＣＤ
			,獲得営業者名
			,得意先ＣＤ
			,得意先名
			,SUM(IIF(ISNULL(売上個数, 0) > 0, 1, 0)) AS 得意先売上日数
			,SUM(平日) AS 得意先平日日数
			,SUM(平日売上個数) AS 得意先平日合計
			,SUM(得意先合計) AS 得意先合計
			,SUM(得意先売上金額) AS 得意先売上金額
		FROM
			日毎集計データ
		GROUP BY
			部署ＣＤ, 部署ＣＤ2, 営業担当者ＣＤ, 営業担当者名, 獲得営業者ＣＤ, 獲得営業者名, 得意先ＣＤ, 得意先名
		),

		ピボット集計データ AS
		(
		SELECT
             部署ＣＤ
            ,部署ＣＤ2
            ,部署名
            ,営業担当者ＣＤ
			,営業担当者名
			,獲得営業者ＣＤ
			,獲得営業者名
			,得意先ＣＤ
			,得意先名
			,日
			,売上個数
		FROM
			日毎集計データ
		),

        日毎抽出データ AS
		(
        SELECT * FROM ピボット集計データ
        PIVOT ( SUM( 売上個数 ) FOR 日 in([1], [2], [3], [4], [5], [6], [7], [8], [9], [10],
                                            [11], [12], [13], [14], [15], [16], [17], [18], [19], [20],
                                            [21], [22], [23], [24], [25], [26], [27], [28], [29], [30], [31]
                                            )) AS ピボットテーブル
        )

        SELECT
            D.*,
			S.得意先平日合計,
			IIF(S.得意先平日日数 != 0, S.得意先平日合計 / S.得意先平日日数, 0) AS 得意先平日平均,
			S.得意先平日日数,
            S.得意先合計,
            IIF(S.得意先売上日数 != 0, S.得意先合計 / S.得意先売上日数, 0) AS 得意先平均,
            S.得意先売上日数,
            S.得意先売上金額
		FROM
			日毎抽出データ D
		LEFT JOIN 集計データ S ON
			D.営業担当者ＣＤ = S.営業担当者ＣＤ
		AND	D.獲得営業者ＣＤ = S.獲得営業者ＣＤ
		AND	D.得意先ＣＤ = S.得意先ＣＤ
        ORDER BY
            D.部署ＣＤ2, D.営業担当者ＣＤ, D.獲得営業者ＣＤ, D.得意先ＣＤ
        ";

        //$DataList = DB::select($sql);
        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        return response()->json($DataList);
    }
}
