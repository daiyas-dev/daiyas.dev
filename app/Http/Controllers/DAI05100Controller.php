<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI05100Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;
        $SaveDateStart = $vm->SaveDateStart;
        $SaveDateEnd = $vm->SaveDateEnd;
        $Customer = $vm->Customer;
        $ShowSyonin = $vm->ShowSyonin;
        $EigyoTantoCd = $vm->EigyoTantoCd;
        $GetEigyoTantoCd = $vm->GetEigyoTantoCd;
        $BushoOption = $vm->BushoOption;
        $BushoCd = $vm->BushoCd;

        $WehreSaveDate = $Customer == "1" ? "AND CONVERT(VARCHAR, TOKUISAKI.新規登録日, 112) >= '$SaveDateStart' AND CONVERT(VARCHAR, TOKUISAKI.新規登録日, 112) <= '$SaveDateEnd'" : "";
        $WehreShowSyonin = $ShowSyonin == "1" ? "AND TOKUISAKI.状態区分 IN (10, 20)" : "";
        $WehreEigyoTantoCd = !!$EigyoTantoCd  ? "AND TOKUISAKI.営業担当者ＣＤ = $EigyoTantoCd" : "";
        $WehreGetEigyoTantoCd = !!$GetEigyoTantoCd  ? "AND TOKUISAKI.獲得営業者ＣＤ = $GetEigyoTantoCd" : "";
        $WhereBusho = $BushoOption == "2" && !!$BushoCd ? "AND TOKUISAKI.部署ＣＤ = $BushoCd" : "";
        $OrderByBusho = $BushoOption == "0" ? "" : "部署ＣＤ2,";


        $sql = "
        WITH 売上データ AS
        (
        SELECT
            URIAGE_MEISAI.部署ＣＤ
            , BUSYO.部署ＣＤ as 部署ＣＤ2
            , BUSYO.部署名
            , URIAGE_MEISAI.日付
            , (STR(TOKUISAKI.営業担当者ＣＤ) + STR(TOKUISAKI.獲得営業者ＣＤ)) AS 担当者ＣＤ
            , TOKUISAKI.営業担当者ＣＤ
            , TANTO.担当者名 AS 営業担当者名
            , TOKUISAKI.獲得営業者ＣＤ
            , TANTO2.担当者名 AS 獲得営業者名
            , TOKUISAKI.得意先ＣＤ
            , TOKUISAKI.得意先名
            , URIAGE_MEISAI.現金個数
            , URIAGE_MEISAI.掛売個数
            , (URIAGE_MEISAI.現金個数 + URIAGE_MEISAI.掛売個数) AS 売上個数
            , URIAGE_MEISAI.現金金額
            , URIAGE_MEISAI.掛売金額
            , (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額) AS 売上金額
            , TOKUISAKI.新規登録日
            , TOKUISAKI.状態区分
        FROM
            得意先マスタ TOKUISAKI
            LEFT JOIN 売上データ明細 URIAGE_MEISAI ON
                    URIAGE_MEISAI.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                AND CONVERT(VARCHAR, URIAGE_MEISAI.日付, 112) >= '$DateStart'
                AND CONVERT(VARCHAR, URIAGE_MEISAI.日付, 112) <= '$DateEnd'
                AND URIAGE_MEISAI.商品区分 IN (1, 2, 3, 7)
            LEFT JOIN 部署マスタ BUSYO ON
                    TOKUISAKI.部署ＣＤ = BUSYO.部署CD
            LEFT JOIN 担当者マスタ TANTO ON
                    TOKUISAKI.営業担当者ＣＤ = TANTO.担当者ＣＤ
            LEFT JOIN 担当者マスタ TANTO2 ON
                    TOKUISAKI.獲得営業者ＣＤ = TANTO2.担当者ＣＤ
        WHERE
            0 = 0
            $WehreSaveDate
            $WehreShowSyonin
            $WehreEigyoTantoCd
            $WehreGetEigyoTantoCd
            $WhereBusho
        --ORDER BY
        --    $OrderByBusho TOKUISAKI.営業担当者ＣＤ, TOKUISAKI.獲得営業者ＣＤ, TOKUISAKI.得意先ＣＤ, URIAGE_MEISAI.日付
        ),

        売上データ日付 AS
        (
        SELECT
            部署ＣＤ2
            ,部署名
            ,営業担当者ＣＤ
            ,営業担当者名
            ,獲得営業者ＣＤ
            ,獲得営業者名
            ,得意先ＣＤ
            ,得意先名
            ,日付
            ,SUM(現金個数 + 掛売個数) AS 食数合計
            ,SUM(現金金額 + 掛売金額) AS 売上金額
        FROM
            売上データ
        GROUP BY
            部署ＣＤ2
            ,部署名
            ,営業担当者ＣＤ
            ,営業担当者名
            ,獲得営業者ＣＤ
            ,獲得営業者名
            ,得意先ＣＤ
            ,得意先名
            ,日付
        )

        SELECT
            部署ＣＤ2
            ,部署名
            ,営業担当者ＣＤ
            ,営業担当者名
            ,獲得営業者ＣＤ
            ,獲得営業者名
            ,得意先ＣＤ
            ,得意先名
            ,COUNT(日付) AS 稼働日
            ,SUM(食数合計) AS 食数合計
            ,SUM(食数合計) / COUNT(日付) AS 食数平均
            ,SUM(売上金額) AS 売上金額
        FROM
            売上データ日付
        GROUP BY
            部署ＣＤ2
            ,部署名
            ,営業担当者ＣＤ
            ,営業担当者名
            ,獲得営業者ＣＤ
            ,獲得営業者名
            ,得意先ＣＤ
            ,得意先名
        ORDER BY
            $OrderByBusho
            営業担当者ＣＤ, 獲得営業者ＣＤ, 得意先ＣＤ
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
