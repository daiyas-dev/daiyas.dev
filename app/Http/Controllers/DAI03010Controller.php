<?php

namespace App\Http\Controllers;

use App\Models\売掛データ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI03010Controller extends Controller
{
    /**
     *  LastUpdateDateSearch
     */
    public function LastUpdateDateSearch($vm)
    {
        $BushoCd = $vm->BushoCd;
        if($BushoCd==null){
            $sql_busho1="";
            $sql_busho2="";
        }else{
            $sql_busho1=" AND DAMMY.部署ＣＤ = $BushoCd";
            $sql_busho2=" AND 部署ＣＤ = $BushoCd";
        }
        $sql = "
            SELECT TOP 1 日付
            FROM 売掛データ
            WHERE  日付 =
                ( SELECT MAX(DAMMY.日付) FROM 売掛データ DAMMY
                    WHERE  DAMMY.請求先ＣＤ >= 0
                    AND    DAMMY.請求先ＣＤ <= 9999999
                    $sql_busho1
                )
            $sql_busho2
            ";

        //$DataList = DB::selectOne($sql);
        $dsn = 'sqlsrv:server=localhost;database=daiyas';
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
        $BushoCd = $vm->BushoCd;
        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;

        if($BushoCd==null){
            $sql_busho1="";
            $sql_busho2="";
            $sql_busho3="";
        }else{
            $sql_busho1=" AND 得意先マスタ.部署ＣＤ = $BushoCd";
            $sql_busho2=" AND 得意先マスタ.部署ＣＤ = $BushoCd";
            $sql_busho3=" AND 部署ＣＤ=$BushoCd";
        }

        $sql = "
            WITH WITH_売上データ明細 AS(
                SELECT 得意先ＣＤ, 部署ＣＤ,
                SUM(現金個数) AS 現金個数,SUM(現金金額) AS 現金金額,SUM(現金値引) AS 現金値引
                ,SUM(掛売個数) AS 掛売個数,SUM(掛売金額) AS 掛売金額,SUM(掛売値引) AS 掛売値引
                FROM 売上データ明細
                WHERE 日付 >= '$DateStart'
                AND 日付 <= '$DateEnd'
                AND ( 売上データ明細.売掛現金区分 IN(1,2) AND  売上データ明細.商品区分 <> 9
                    OR 売上データ明細.売掛現金区分 = 0 AND 売上データ明細.商品区分 = 9 )
                GROUP BY 得意先ＣＤ, 部署ＣＤ
            )
            ,WITH_請求データ AS(
                SELECT 部署ＣＤ,請求先ＣＤ, SUM(消費税額) as 消費税額
                FROM 請求データ
                WHERE 請求日付 >= '$DateStart'
                AND 請求日付 <= '$DateEnd'
                GROUP BY 部署ＣＤ,請求先ＣＤ
            )
            ,WITH_入金データ AS(
                SELECT 得意先ＣＤ, 部署ＣＤ,
                SUM(現金) AS 現金,SUM(小切手) AS 小切手,SUM(振込) AS 振込,SUM(バークレー) AS バークレー
                ,SUM(その他) AS その他,SUM(相殺) AS 相殺,SUM(値引) AS 値引
                FROM 入金データ
                WHERE 入金日付 >= '$DateStart'
                AND 入金日付 <= '$DateEnd'
                GROUP BY 得意先ＣＤ, 部署ＣＤ
            )
            ,WITH_得意先別売上入金サマリ AS(
                SELECT
                得意先マスタ.部署ＣＤ AS 部署ＣＤ
                ,得意先マスタ.請求先ＣＤ AS 請求先ＣＤ
                ,SUM(
                    (ISNULL(WITH_売上データ明細.現金金額,0)-ISNULL(WITH_売上データ明細.現金値引,0))
                        +ISNULL(WITH_入金データ.現金,0)
                        +ISNULL(WITH_入金データ.小切手,0)
                        +ISNULL(WITH_入金データ.振込,0)
                        +ISNULL(WITH_入金データ.バークレー,0)
                        +ISNULL(WITH_入金データ.その他,0)
                        +ISNULL(WITH_入金データ.相殺,0)
                        +ISNULL(WITH_入金データ.値引,0)
                    )AS 今月入金額_SUM2
                ,SUM((ISNULL(WITH_売上データ明細.掛売金額,0)-ISNULL(WITH_売上データ明細.掛売値引,0)) + (CASE WHEN 得意先マスタ.税区分=0 THEN ISNULL(WITH_請求データ.消費税額,0) ELSE 0 END)) AS 今月売上額_SUM3
                FROM 得意先マスタ
                LEFT JOIN WITH_売上データ明細 ON WITH_売上データ明細.部署ＣＤ=得意先マスタ.部署ＣＤ AND WITH_売上データ明細.得意先ＣＤ=得意先マスタ.得意先ＣＤ
                LEFT JOIN WITH_請求データ     ON WITH_請求データ.部署ＣＤ=得意先マスタ.部署ＣＤ     AND WITH_請求データ.請求先ＣＤ=得意先マスタ.請求先ＣＤ
                LEFT JOIN WITH_入金データ     ON WITH_入金データ.部署ＣＤ=得意先マスタ.部署ＣＤ     AND WITH_入金データ.得意先ＣＤ=得意先マスタ.得意先ＣＤ
                WHERE (得意先マスタ.売掛現金区分 = 0 OR 得意先マスタ.売掛現金区分 = 1 OR 得意先マスタ.売掛現金区分 = 2)
                $sql_busho1
                GROUP BY 得意先マスタ.部署ＣＤ
                        ,得意先マスタ.請求先ＣＤ
            )
            ,WITH_得意先マスタ AS(
                SELECT
                得意先マスタ.得意先ＣＤ        AS 得意先ＣＤ
                ,得意先マスタ.得意先名          AS 得意先名
                ,得意先マスタ.売掛現金区分      AS 売掛現金区分
                ,得意先マスタ.締区分            AS 締区分
                ,得意先マスタ.部署ＣＤ          AS 部署ＣＤ
                ,得意先マスタ.締日１            AS 締日１
                ,得意先マスタ.締日２            AS 締日２
                ,得意先マスタ.支払サイト        AS 支払サイト
                ,得意先マスタ.支払日            AS 支払日
                ,得意先マスタ.集金区分          AS 集金区分
                ,得意先マスタ.税区分            AS 税区分
                ,得意先マスタ.請求先ＣＤ        AS 請求先ＣＤ
                FROM 得意先マスタ
                WHERE (得意先マスタ.売掛現金区分 = 0 OR 得意先マスタ.売掛現金区分 = 1 OR 得意先マスタ.売掛現金区分 = 2)
                AND 得意先マスタ.得意先ＣＤ = 得意先マスタ.請求先ＣＤ
                $sql_busho2
            )
            ,WITH_請求得意先別売上入金サマリ AS(
                SELECT
                WITH_得意先マスタ.部署ＣＤ
                ,WITH_得意先マスタ.得意先ＣＤ
                ,WITH_得意先マスタ.得意先名
                ,SUM(WITH_得意先別売上入金サマリ.今月入金額_SUM2) AS 今月入金額
                ,SUM(WITH_得意先別売上入金サマリ.今月売上額_SUM3) AS 今月売上額
                FROM WITH_得意先マスタ
                    LEFT JOIN WITH_得意先別売上入金サマリ ON WITH_得意先マスタ.請求先ＣＤ=WITH_得意先別売上入金サマリ.請求先ＣＤ
                GROUP BY
                WITH_得意先マスタ.部署ＣＤ
                ,WITH_得意先マスタ.得意先ＣＤ
                ,WITH_得意先マスタ.得意先名
            )
            ,WITH_当月売掛データ AS(
                SELECT WITH_得意先マスタ.得意先ＣＤ
                    ,売掛データ.*
                FROM WITH_得意先マスタ
                    ,売掛データ
                WHERE 日付 = '$DateStart'
                AND 売掛データ.部署ＣＤ=WITH_得意先マスタ.部署ＣＤ
                AND 売掛データ.請求先ＣＤ=WITH_得意先マスタ.請求先ＣＤ
            )
            ,WITH_前回売掛集計状況 AS(
                SELECT 請求先ＣＤ AS 前回集計_請求先ＣＤ
                    ,MAX(日付)AS 最終集計日
                FROM 売掛データ
                WHERE 日付< '$DateStart'
                $sql_busho3
                GROUP BY 請求先ＣＤ
            )
            ,WITH_前回売掛データ AS(
                SELECT WITH_得意先マスタ.得意先ＣＤ
                    ,売掛データ.*
                FROM WITH_得意先マスタ
                        ,売掛データ
                    ,WITH_前回売掛集計状況
                WHERE 売掛データ.請求先ＣＤ=WITH_前回売掛集計状況.前回集計_請求先ＣＤ
                AND 売掛データ.日付 = WITH_前回売掛集計状況.最終集計日
                AND 売掛データ.部署ＣＤ=WITH_得意先マスタ.部署ＣＤ
                AND 売掛データ.請求先ＣＤ=WITH_得意先マスタ.請求先ＣＤ
            )
            SELECT
             CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN 0 ELSE 1 END AS 当月集計済ＣＤ
            ,CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN NULL ELSE '済' END AS 当月集計済
            ,CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN WITH_請求得意先別売上入金サマリ.部署ＣＤ ELSE WITH_当月売掛データ.部署ＣＤ END AS 部署ＣＤ
            ,CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN サマリ部署.部署名 ELSE 売掛データ部署.部署名 END AS 部署名
            ,CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN WITH_請求得意先別売上入金サマリ.得意先ＣＤ ELSE WITH_当月売掛データ.請求先ＣＤ END AS 請求先ＣＤ
            ,CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN WITH_請求得意先別売上入金サマリ.得意先名 ELSE 売掛データ得意先.得意先名 END AS 請求先名
            ,CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN ISNULL(WITH_前回売掛データ.今月残高,0) ELSE WITH_当月売掛データ.前月残高   END AS 前月残高
            ,CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN WITH_請求得意先別売上入金サマリ.今月入金額 ELSE WITH_当月売掛データ.今月入金額 END AS 今月入金額
            ,CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN ISNULL(WITH_前回売掛データ.今月残高,0)-WITH_請求得意先別売上入金サマリ.今月入金額 ELSE WITH_当月売掛データ.差引繰越額 END AS 差引繰越額
            ,CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN WITH_請求得意先別売上入金サマリ.今月売上額 ELSE WITH_当月売掛データ.今月売上額 END AS 今月売上額
            ,CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN ISNULL(WITH_前回売掛データ.今月残高,0)-WITH_請求得意先別売上入金サマリ.今月入金額+WITH_請求得意先別売上入金サマリ.今月売上額 ELSE WITH_当月売掛データ.今月残高 END AS 今月残高
            ,CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN 0 ELSE WITH_当月売掛データ.予備金額１ END AS 予備金額１
            ,CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN 0 ELSE WITH_当月売掛データ.予備金額２ END AS 予備金額２
            ,CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN 0 ELSE WITH_当月売掛データ.予備ＣＤ１ END AS 予備ＣＤ１
            ,CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN 0 ELSE WITH_当月売掛データ.予備ＣＤ２ END AS 予備ＣＤ２
            ,CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN NULL ELSE WITH_当月売掛データ.修正担当者ＣＤ END AS 修正担当者ＣＤ
            ,CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN NULL ELSE 修正担当者.担当者名 END AS 修正担当者名
            ,CASE WHEN WITH_当月売掛データ.日付 IS NULL THEN NULL ELSE WITH_当月売掛データ.修正日 END AS 修正日
            FROM WITH_請求得意先別売上入金サマリ
                LEFT JOIN WITH_当月売掛データ ON WITH_請求得意先別売上入金サマリ.部署ＣＤ=WITH_当月売掛データ.部署ＣＤ AND WITH_請求得意先別売上入金サマリ.得意先ＣＤ=WITH_当月売掛データ.得意先ＣＤ
                LEFT JOIN WITH_前回売掛データ ON WITH_請求得意先別売上入金サマリ.部署ＣＤ=WITH_前回売掛データ.部署ＣＤ AND WITH_請求得意先別売上入金サマリ.得意先ＣＤ=WITH_前回売掛データ.得意先ＣＤ
                LEFT JOIN 担当者マスタ 修正担当者 ON 修正担当者.担当者ＣＤ=WITH_当月売掛データ.修正担当者ＣＤ
                LEFT JOIN 部署マスタ   サマリ部署 ON サマリ部署.部署ＣＤ=WITH_請求得意先別売上入金サマリ.部署ＣＤ
                LEFT JOIN 部署マスタ   売掛データ部署 ON 売掛データ部署.部署ＣＤ=WITH_当月売掛データ.部署ＣＤ
                LEFT JOIN 得意先マスタ 売掛データ得意先 ON 売掛データ得意先.得意先ＣＤ=WITH_当月売掛データ.請求先ＣＤ
                ";

        //$DataList = DB::select($sql);
        $dsn = 'sqlsrv:server=localhost;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        return response()->json($DataList);
    }
    /**
     * Save
     */
    public function Save($request)
    {
        DB::beginTransaction();

        try {
            $params = $request->all();

            $date = Carbon::now()->format('Y-m-d H:i:s');
            $BushoCd=$params['BushoCd'];
            $ShoriKbn = $params['ShoriKbn'];
            $ShuseiTantoCd=$params['ShuseiTantoCd'];
            $SaveList = $params['SaveList'];
            $TargetDate=$params['TargetDate'];
            $TargetDate=preg_replace('/年|月/','/',$TargetDate);
            $TargetDate.='01';

            売掛データ::query()
            ->where('部署ＣＤ', $BushoCd)
            ->where('日付', $TargetDate)
            ->delete();

            if($ShoriKbn=='1')//処理区分が集計処理ならInsert処理を実施
            {
                foreach($SaveList as $SaveItem)
                {
                    $rec['日付'] = $TargetDate;
                    $rec['部署ＣＤ'] = $SaveItem['部署ＣＤ'];
                    $rec['請求先ＣＤ'] = $SaveItem['請求先ＣＤ'];
                    $rec['前月残高'] = $SaveItem['前月残高'];
                    $rec['今月入金額'] = $SaveItem['今月入金額'];
                    $rec['差引繰越額'] = $SaveItem['差引繰越額'];
                    $rec['今月売上額'] = $SaveItem['今月売上額'];
                    $rec['今月残高'] = $SaveItem['今月残高'];
                    $rec['予備金額１'] = $SaveItem['予備金額１'];
                    $rec['予備金額２'] = $SaveItem['予備金額２'];
                    $rec['予備ＣＤ１'] = $SaveItem['予備ＣＤ１'];
                    $rec['予備ＣＤ２'] = $SaveItem['予備ＣＤ２'];
                    $rec['修正担当者ＣＤ'] = $ShuseiTantoCd;
                    $rec['修正日'] = $date;
                    売掛データ::insert($rec);
                }
            }
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            //'lastupdatedate'=>$this->LastUpdateDateSearch($request),
            //'edited' => $this->Search($request),
        ]);
    }
}
