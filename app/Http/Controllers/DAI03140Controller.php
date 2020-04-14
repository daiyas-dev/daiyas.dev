<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI03140Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;

        $BushoArray = $vm->BushoArray;
        $WhereBusho1="";
        $WhereBusho2="";
        if($BushoArray !=null && is_array($BushoArray) && 0<count($BushoArray))
        {
            $BushoList = implode(',',$BushoArray);
            $WhereBusho1=" AND M2.部署CD IN( $BushoList )";
            $WhereBusho2=" AND N1.部署ＣＤ IN( $BushoList )";
        }

        $TokuiCd = $vm->TokuiCd;
        $WhereTokui="";
        if($TokuiCd !=null)
        {
            $WhereTokui=" AND D1.請求先ＣＤ = $TokuiCd";
        }

        $sql = "
            WITH 得意先請求先マスタ AS (
                SELECT
                     TOK.部署ＣＤ
                     ,TOK.得意先ＣＤ
                     ,SEIKYU.部署ＣＤ AS 請求部署ＣＤ
                     ,SEIKYU.請求先ＣＤ
                FROM 得意先マスタ SEIKYU
                    LEFT OUTER JOIN
                    (
                        SELECT DISTINCT 部署ＣＤ, 得意先ＣＤ, IsNull(Nullif(請求先ＣＤ, 0), 得意先ＣＤ) AS 請求先ＣＤ FROM 得意先マスタ
                    ) TOK ON SEIKYU.得意先ＣＤ = TOK.請求先ＣＤ
            )
            SELECT
                 D1.部署CD AS 部署ＣＤ
                ,D1.部署名
                ,D1.得意先ＣＤ
                ,D1.得意先名
                ,前月末金額
                ,今月売上額 + ISNULL(売上入金,0) AS 売上合計
                ,今月入金額 + ISNULL(売上入金,0) AS 入金合計
                ,売掛残
                ,今月売上額 + ISNULL(売上入金,0) - ISNULL(その他売上,0) AS 売上金額
                ,ISNULL(その他売上,0) AS その他売上
                ,ISNULL(現金,0) + ISNULL(売上入金2,0) AS 現金
                ,ISNULL(振込,0) AS 振込
                ,ISNULL(振替,0) AS 振替
                ,ISNULL(金券,0) AS 金券
                ,ISNULL(その他入金,0) AS その他入金
                ,ISNULL(請求データ.消費税額,0) AS 消費税額
            FROM
                (
                    SELECT M2.部署CD
                        ,M1.部署名
                        ,M2.得意先ＣＤ
                        ,M2.得意先名
                        ,ISNULL(D1.前月残高,0) AS 前月末金額,ISNULL(D1.今月入金額,0) AS 今月入金額
                        ,ISNULL(D1.今月売上額,0) AS 今月売上額,ISNULL(D1.今月残高,0) AS 売掛残
                    FROM
                        得意先マスタ M2
                        LEFT OUTER JOIN 部署マスタ M1 ON M1.部署CD = M2.部署CD
                        LEFT OUTER JOIN 売掛データ D1 ON M2.部署CD = D1.部署ＣＤ AND M2.得意先ＣＤ=D1.請求先ＣＤ AND D1.日付='$DateStart'
                    WHERE 0=0
                    $WhereBusho1
                    $WhereTokui
                ) D1
                LEFT OUTER JOIN (
                    SELECT M2.請求部署ＣＤ as 部署ＣＤ,M2.請求先ＣＤ as 得意先ＣＤ,SUM(U1.現金金額 - U1.現金値引) + SUM(U1.掛売金額 - U1.掛売値引) as 売上入金
                    from 売上データ明細 U1
                        left join 商品マスタ M1 ON M1.商品ＣＤ = U1.商品ＣＤ
                        LEFT OUTER JOIN 得意先請求先マスタ M2 ON M2.得意先ＣＤ = U1.得意先ＣＤ AND M2.部署ＣＤ=U1.部署ＣＤ
                    where U1.日付>='$DateStart'
                    AND U1.日付<='$DateEnd'
                    AND U1.売掛現金区分 in(0,3)
                    AND U1.分配元数量 = 0
                    AND U1.商品区分 <> 9
                    group by M2.請求部署ＣＤ,M2.請求先ＣＤ
                ) U1 ON U1.部署ＣＤ = D1.部署ＣＤ AND U1.得意先ＣＤ = D1.得意先ＣＤ

                LEFT OUTER JOIN (
                    SELECT  M2.請求部署ＣＤ as 部署ＣＤ,M2.請求先ＣＤ as 得意先ＣＤ
                        ,SUM(U2.現金金額 - U2.現金値引) + SUM(U2.掛売金額 - U2.掛売値引) AS その他売上
                    from 売上データ明細 U2
                        left join 商品マスタ M1 ON M1.商品ＣＤ = U2.商品ＣＤ
                        LEFT OUTER JOIN 得意先請求先マスタ M2 ON M2.得意先ＣＤ = U2.得意先ＣＤ AND M2.部署ＣＤ=U2.部署ＣＤ
                    where U2.日付>='$DateStart'
                    AND U2.日付<='$DateEnd'
                    AND U2.売掛現金区分 in(0,1,3)
                    AND U2.分配元数量 = 0
                    AND U2.商品区分 <> 9
                    AND M1.弁当区分 = 10
                    group by M2.請求部署ＣＤ,M2.請求先ＣＤ,M1.弁当区分
                ) U2 ON U2.部署ＣＤ = D1.部署ＣＤ AND U2.得意先ＣＤ = D1.得意先ＣＤ

                LEFT OUTER JOIN (
                    SELECT M2.請求部署ＣＤ as 部署ＣＤ,M2.請求先ＣＤ as 得意先ＣＤ,SUM(U3.現金金額 - U3.現金値引) + SUM(U3.掛売金額 - U3.掛売値引) as 売上入金2
                    from 売上データ明細 U3
                        left join 商品マスタ M1 ON M1.商品ＣＤ = U3.商品ＣＤ
                        LEFT OUTER JOIN 得意先請求先マスタ M2 ON M2.得意先ＣＤ = U3.得意先ＣＤ AND M2.部署ＣＤ=U3.部署ＣＤ
                    where U3.日付>='$DateStart'
                    AND U3.日付<='$DateEnd'
                    AND U3.売掛現金区分 in(0,3)
                    group by M2.請求部署ＣＤ,M2.請求先ＣＤ
                ) U3 ON U3.部署ＣＤ = D1.部署ＣＤ AND U3.得意先ＣＤ = D1.得意先ＣＤ

                LEFT OUTER JOIN (
                    SELECT N1.部署ＣＤ,N1.得意先ＣＤ,SUM(現金 + 小切手) as 現金
                        ,SUM(振込 + 相殺) AS 振込,SUM(バークレー) as 振替
                        ,SUM(その他) AS 金券,SUM(値引) AS その他入金
                    from 入金データ N1
                    where N1.入金日付 >='$DateStart'
                    AND N1.入金日付<='$DateEnd'
                    $WhereBusho2
                    group by N1.部署ＣＤ,N1.得意先ＣＤ
                ) N1 ON D1.部署ＣＤ = N1.部署ＣＤ AND N1.得意先ＣＤ = D1.得意先ＣＤ

                LEFT OUTER JOIN
                (
                    SELECT 部署CD,請求先CD,SUM(ISNULL(消費税額,0)) as 消費税額 FROM 請求データ
                    WHERE 請求データ.請求日付 >='$DateStart'
                    AND 請求データ.請求日付<='$DateEnd'
                    GROUP BY 部署CD,請求先CD
                ) 請求データ ON D1.部署ＣＤ = 請求データ.部署ＣＤ AND 請求データ.請求先ＣＤ = D1.得意先ＣＤ

            where D1.前月末金額 != 0
               or D1.今月売上額 != 0
               or 今月入金額 != 0
               or D1.売掛残 != 0
               or U1.売上入金 != 0
               or U2.その他売上 != 0
               or N1.現金 != 0
               or N1.振込 != 0
               or N1.振替 != 0
               or N1.金券 != 0
               or N1.その他入金 != 0
            ORDER BY
               D1.部署CD
              ,D1.得意先ＣＤ
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
