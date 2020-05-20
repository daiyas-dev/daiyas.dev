<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI05120Controller extends Controller
{
    //出力区分の指定
    const SEARCH_TYPE_BUSYOBETSU = '1';
    const SEARCH_TYPE_EIGYOBETSU = '2';
    const SEARCH_TYPE_KOKYAKUBETSU = '3';

    //営業別検索の条件
    const SEARCH_TANTO_TYPE_ALL="1";//全担当者を検索
    const SEARCH_TANTO_TYPE_TANTO="2";//指定した担当者を検索

    /**
     * Search
     */
    public function Search($vm)
    {
        $params['BushoCd']=$vm->BushoCd;
        $params['SearchType']=$vm->SearchType;
        $params['SearchTantoType']=$vm->SearchTantoType;
        $params['TantoCd']=$vm->TantoCd;

        $DateStart=preg_replace('/年|月/','',$vm->DateStart);
        $StartFirstDay=Carbon::create(substr($DateStart,0,4), substr($DateStart,4,2), "01");
        $params['StartDate']=$StartFirstDay->format('Y/m/d');

        $DateEnd=preg_replace('/年|月/','',$vm->DateEnd);
        $EndLastDay = Carbon::create(substr($DateEnd,0,4),substr($DateEnd,4,2),1)->lastOfMonth();
        $params['EndDate']= $EndLastDay->format('Y/m/d');

        if ($params['SearchType']==self::SEARCH_TYPE_BUSYOBETSU) {
            $DataList = $this->SearchBushoBetsu($params);
        }
        elseif ($params['SearchType']==self::SEARCH_TYPE_EIGYOBETSU) {
            $DataList = $this->SearchEigyoBetsu($params);
        }
        elseif ($params['SearchType']==self::SEARCH_TYPE_KOKYAKUBETSU) {
            $DataList = $this->SearchKokyakuBetsu($params);
        }
        else{
            $DataList=null;
        }
        return response()->json($DataList);
    }
    private function SearchBushoBetsu($params)
    {
        $BushoCd=$params['BushoCd'];
        $StartDate=$params['StartDate'];
        $EndDate=$params['EndDate'];

        //TODO:商品区分の検索条件は1,2,3,7が正しいと思われる。現状では1,2,3,10のものが存在している
        //TODO:検索条件でCONVERTで日付比較している処理を見直す
        $sql = "
                select
                    部署ＣＤ
                    , 部署名
                    , 年月
                    , SUM(既存_売上個数) AS 既存_売上個数
                    , SUM(既存_売上金額) AS 既存_売上金額
                    , SUM(新規_売上個数) AS 新規_売上個数
                    , SUM(新規_売上金額) AS 新規_売上金額
                    , SUM(既存_売上個数) + SUM(新規_売上個数) AS 食数合計
                    , SUM(既存_売上金額) + SUM(新規_売上金額) AS 金額合計
                from (
                    -- 既存客 ------------------------
                    select
                        URIAGE_MEISAI.部署ＣＤ
                        , BUSYO.部署名
                        , SUBSTRING( CONVERT( VARCHAR , CAST( URIAGE_MEISAI.日付 AS DATE ), 112 ), 1, 6 ) AS 年月
                        , TOKUISAKI.営業担当者ＣＤ
                        , TANTO.担当者名 as 営業担当者名
                        , (URIAGE_MEISAI.現金個数 + URIAGE_MEISAI.掛売個数) as 既存_売上個数
                        , (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額) as 既存_売上金額
                        , 0 as 新規_売上個数
                        , 0 as 新規_売上金額
                        , TOKUISAKI.新規登録日
                    from 売上データ明細 URIAGE_MEISAI
                        inner join 得意先マスタ TOKUISAKI on
                        URIAGE_MEISAI.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                        left join 部署マスタ BUSYO on
                        URIAGE_MEISAI.部署ＣＤ = BUSYO.部署CD
                        left join 担当者マスタ TANTO on
                        TOKUISAKI.営業担当者ＣＤ = TANTO.担当者ＣＤ
                        left join 担当者マスタ TANTO2 on
                        TOKUISAKI.獲得営業者ＣＤ = TANTO2.担当者ＣＤ
                    where
                        URIAGE_MEISAI.日付 >= '$StartDate'
                    and URIAGE_MEISAI.日付 <= '$EndDate'
                    and URIAGE_MEISAI.商品区分 in (1,2,3,10)
                    and URIAGE_MEISAI.部署ＣＤ = $BushoCd
                    and not (
                            TOKUISAKI.新規登録日 >= '$StartDate'
                        and TOKUISAKI.新規登録日 <= '$EndDate'
                        )
                union all
                    -- 新規客 ------------------------
                    select
                        URIAGE_MEISAI.部署ＣＤ
                        , BUSYO.部署名
                        , SUBSTRING( CONVERT( VARCHAR , CAST( URIAGE_MEISAI.日付 AS DATE ), 112 ), 1, 6 ) AS 年月
                        , TOKUISAKI.営業担当者ＣＤ
                        , TANTO.担当者名 as 営業担当者名
                        , 0 as 既存_売上個数
                        , 0 as 既存_売上金額
                        , (URIAGE_MEISAI.現金個数 + URIAGE_MEISAI.掛売個数) as 新規_売上個数
                        , (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額) as 新規_売上金額
                        , TOKUISAKI.新規登録日
                    from 売上データ明細 URIAGE_MEISAI
                        inner join 得意先マスタ TOKUISAKI on
                        URIAGE_MEISAI.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                        left join 部署マスタ BUSYO on
                        URIAGE_MEISAI.部署ＣＤ = BUSYO.部署CD
                        left join 担当者マスタ TANTO on
                        TOKUISAKI.営業担当者ＣＤ = TANTO.担当者ＣＤ
                        left join 担当者マスタ TANTO2 on
                        TOKUISAKI.獲得営業者ＣＤ = TANTO2.担当者ＣＤ
                    where
                        URIAGE_MEISAI.日付 >= '$StartDate'
                    and URIAGE_MEISAI.日付 <= '$EndDate'
                    and URIAGE_MEISAI.商品区分 in (1,2,3,7)
                    and URIAGE_MEISAI.部署ＣＤ = $BushoCd
                    and (
                                TOKUISAKI.新規登録日 >= '$StartDate'
                            and TOKUISAKI.新規登録日 <= '$EndDate'
                        )
                ) main
                group by 部署ＣＤ, 部署名, 年月
                order by 部署ＣＤ, 年月
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
    private function SearchEigyoBetsu($params)
    {
        $BushoCd=$params['BushoCd'];
        $StartDate=$params['StartDate'];
        $EndDate=$params['EndDate'];
        $SearchTantoType=$params['SearchTantoType'];
        $TantoCd=$params['TantoCd'];

        $WhereTantoCd = "";
        if (($SearchTantoType != null) && ($SearchTantoType==self::SEARCH_TANTO_TYPE_TANTO)) {
            if (($TantoCd != null) && (strlen($TantoCd)>0)) {
                $WhereTantoCd = " AND 営業担当者ＣＤ = $TantoCd";
            }
        }

        //TODO:商品区分の検索条件は1,2,3,7が正しいと思われる。現状では1,2,3,10のものが存在している
        //TODO:検索条件でCONVERTで日付比較している処理を見直す
        $sql = "
                select
                  部署ＣＤ
                , 部署名
                , 年月
                , 営業担当者ＣＤ
                , 営業担当者名
                , SUM(既存_売上個数) AS 既存_売上個数
                , SUM(既存_売上金額) AS 既存_売上金額
                , SUM(新規_売上個数) AS 新規_売上個数
                , SUM(新規_売上金額) AS 新規_売上金額
                , SUM(既存_売上個数) + SUM(新規_売上個数) AS 食数合計
                , SUM(既存_売上金額) + SUM(新規_売上金額) AS 金額合計

                from (

                -- 既存客 ------------------------
                select
                        URIAGE_MEISAI.部署ＣＤ
                    , BUSYO.部署名
                    , SUBSTRING( CONVERT( VARCHAR , CAST( URIAGE_MEISAI.日付 AS DATE ), 112 ), 1, 6 ) AS 年月
                    , TOKUISAKI.営業担当者ＣＤ
                    , TANTO.担当者名 as 営業担当者名
                    , (URIAGE_MEISAI.現金個数 + URIAGE_MEISAI.掛売個数) as 既存_売上個数
                    , (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額) as 既存_売上金額
                    , 0 as 新規_売上個数
                    , 0 as 新規_売上金額
                    , TOKUISAKI.新規登録日
                    from 売上データ明細 URIAGE_MEISAI
                    inner join 得意先マスタ TOKUISAKI on
                    URIAGE_MEISAI.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                    left join 部署マスタ BUSYO on
                    URIAGE_MEISAI.部署ＣＤ = BUSYO.部署CD
                    left join 担当者マスタ TANTO on
                    TOKUISAKI.営業担当者ＣＤ = TANTO.担当者ＣＤ
                    left join 担当者マスタ TANTO2 on
                    TOKUISAKI.獲得営業者ＣＤ = TANTO2.担当者ＣＤ
                where
                        URIAGE_MEISAI.日付 >= '$StartDate'
                    and URIAGE_MEISAI.日付 <= '$EndDate'
                    and URIAGE_MEISAI.商品区分 in (1,2,3,10)
                    and URIAGE_MEISAI.部署ＣＤ = $BushoCd
                    and not (
                            TOKUISAKI.新規登録日 >= '$StartDate'
                        and TOKUISAKI.新規登録日 <= '$EndDate'
                    )

                union all

                -- 新規客 ------------------------
                select
                        URIAGE_MEISAI.部署ＣＤ
                        , BUSYO.部署名
                        , SUBSTRING( CONVERT( VARCHAR , CAST( URIAGE_MEISAI.日付 AS DATE ), 112 ), 1, 6 ) AS 年月
                        , TOKUISAKI.営業担当者ＣＤ
                        , TANTO.担当者名 as 営業担当者名
                        , 0 as 既存_売上個数
                        , 0 as 既存_売上金額
                        , (URIAGE_MEISAI.現金個数 + URIAGE_MEISAI.掛売個数) as 新規_売上個数
                        , (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額) as 新規_売上金額
                        , TOKUISAKI.新規登録日
                    from 売上データ明細 URIAGE_MEISAI
                        inner join 得意先マスタ TOKUISAKI on
                        URIAGE_MEISAI.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                        left join 部署マスタ BUSYO on
                        URIAGE_MEISAI.部署ＣＤ = BUSYO.部署CD
                        left join 担当者マスタ TANTO on
                        TOKUISAKI.営業担当者ＣＤ = TANTO.担当者ＣＤ
                        left join 担当者マスタ TANTO2 on
                        TOKUISAKI.獲得営業者ＣＤ = TANTO2.担当者ＣＤ
                    where
                        URIAGE_MEISAI.日付 >= '$StartDate'
                    and URIAGE_MEISAI.日付 <= '$EndDate'
                    and URIAGE_MEISAI.商品区分 in (1,2,3,10)
                    and URIAGE_MEISAI.部署ＣＤ = $BushoCd
                    and (
                            TOKUISAKI.新規登録日 >= '$StartDate'
                        and TOKUISAKI.新規登録日 <= '$EndDate'
                        )
                ) main
                where 0=0
                $WhereTantoCd
                group by 部署ＣＤ, 部署名, 年月, 営業担当者ＣＤ, 営業担当者名
                order by 部署ＣＤ, 営業担当者ＣＤ, 年月
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
    private function SearchKokyakuBetsu($params)
    {
        $BushoCd=$params['BushoCd'];
        $StartDate=$params['StartDate'];
        $EndDate=$params['EndDate'];

        //TODO:商品区分の検索条件は1,2,3,7が正しいと思われる。現状では1,2,3,10のものが存在している
        //TODO:検索条件でCONVERTで日付比較している処理を見直す
        $sql = "
                select
                    部署ＣＤ
                    , 部署名
                    , 年月
                    , 得意先ＣＤ
                    , 得意先名
                    , SUM(売上個数) AS 売上個数
                    , SUM(売上金額) AS 売上金額
                    , 新規区分
                from (

                    -- 既存客 ------------------------
                    select
                        URIAGE_MEISAI.部署ＣＤ
                        , BUSYO.部署名
                        , SUBSTRING( CONVERT( VARCHAR , CAST( URIAGE_MEISAI.日付 AS DATE ), 112 ), 1, 6 ) AS 年月
                        , TOKUISAKI.得意先ＣＤ
                        , TOKUISAKI.得意先名
                        , (URIAGE_MEISAI.現金個数 + URIAGE_MEISAI.掛売個数) as 売上個数
                        , (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額) as 売上金額
                        , '既存' AS 新規区分



                    from 売上データ明細 URIAGE_MEISAI
                        inner join 得意先マスタ TOKUISAKI on
                        URIAGE_MEISAI.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                        left join 部署マスタ BUSYO on
                        URIAGE_MEISAI.部署ＣＤ = BUSYO.部署CD
                        left join 担当者マスタ TANTO on
                        TOKUISAKI.営業担当者ＣＤ = TANTO.担当者ＣＤ
                        left join 担当者マスタ TANTO2 on
                        TOKUISAKI.獲得営業者ＣＤ = TANTO2.担当者ＣＤ
                    where
                        URIAGE_MEISAI.日付 >= '$StartDate'
                    and URIAGE_MEISAI.日付 <= '$EndDate'
                    and URIAGE_MEISAI.商品区分 in (1,2,3,10)
                    and URIAGE_MEISAI.部署ＣＤ = $BushoCd
                    and not (
                            TOKUISAKI.新規登録日 >= '$StartDate'
                        and TOKUISAKI.新規登録日 <= '$EndDate'
                        )

                    union all

                    -- 新規客 ------------------------
                    select
                        URIAGE_MEISAI.部署ＣＤ
                        , BUSYO.部署名
                        , SUBSTRING( CONVERT( VARCHAR , CAST( URIAGE_MEISAI.日付 AS DATE ), 112 ), 1, 6 ) AS 年月
                        , TOKUISAKI.得意先ＣＤ
                        , TOKUISAKI.得意先名
                        , (URIAGE_MEISAI.現金個数 + URIAGE_MEISAI.掛売個数) as 売上個数
                        , (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額) as 売上金額
                        , '新規' AS 新規区分


                    from 売上データ明細 URIAGE_MEISAI
                        inner join 得意先マスタ TOKUISAKI on
                        URIAGE_MEISAI.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                        left join 部署マスタ BUSYO on
                        URIAGE_MEISAI.部署ＣＤ = BUSYO.部署CD
                        left join 担当者マスタ TANTO on
                        TOKUISAKI.営業担当者ＣＤ = TANTO.担当者ＣＤ
                        left join 担当者マスタ TANTO2 on
                        TOKUISAKI.獲得営業者ＣＤ = TANTO2.担当者ＣＤ
                    where
                        URIAGE_MEISAI.日付 >= '$StartDate'
                    and URIAGE_MEISAI.日付 <= '$EndDate'
                    and URIAGE_MEISAI.商品区分 in (1,2,3,10)
                    and URIAGE_MEISAI.部署ＣＤ = $BushoCd
                    and (
                                TOKUISAKI.新規登録日 >= '$StartDate'
                            and TOKUISAKI.新規登録日 <= '$EndDate'
                        )
                ) main

                group by 部署ＣＤ, 部署名, 年月, 得意先ＣＤ, 得意先名, 新規区分
                order by 年月, 得意先名
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
}
