<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI01220Controller extends Controller
{
    /**
     *  ColumSerch
     */
    public function ColSearch($vm)
    {
        $BushoCd = $vm->BushoCd;
        $sql = "
            SELECT
                行NO AS 商品区分,
                各種名称
            FROM 各種テーブル
            WHERE 各種CD=
                (
                    SELECT
                        IIF(サブ各種CD2=2, 27, 14)
                    FROM 各種テーブル
                    WHERE 各種CD=26
                    AND サブ各種CD1=$BushoCd
                )
                AND 行NO<=7
            ORDER BY 各種CD,行NO
        ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }

    /**
     * Search
     */
    public function Search($vm)
    {
        $BushoCd = $vm->BushoCd;
        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;

        $sql = "
            WITH WITH_コーステーブル AS (
                SELECT
                    コーステーブル.コースＣＤ
                    , コースマスタ.コース名
                    , ＳＥＱ
                    , コーステーブル.得意先ＣＤ
                    , 得意先マスタ.得意先名
                    , 得意先マスタ.売掛現金区分
					, コースマスタ.担当者ＣＤ
					, 担当者マスタ.担当者名
                FROM
                    [コーステーブル]
                    INNER JOIN [コースマスタ]
                        ON コースマスタ.コースＣＤ = コーステーブル.コースＣＤ
                        AND コースマスタ.部署ＣＤ = コーステーブル.部署ＣＤ
                    INNER JOIN [得意先マスタ]
                        ON 得意先マスタ.得意先ＣＤ = コーステーブル.得意先ＣＤ
                        AND 得意先マスタ.部署ＣＤ = コーステーブル.部署ＣＤ
                    INNER JOIN [担当者マスタ]
                        ON 担当者マスタ.担当者ＣＤ = コースマスタ.担当者ＣＤ
                WHERE
                    コーステーブル.部署ＣＤ = $BushoCd
                    AND (
                        得意先マスタ.得意先ＣＤ = 得意先マスタ.受注得意先ＣＤ
                        OR 得意先マスタ.受注得意先ＣＤ = 0
                    )
                    AND コースマスタ.コース区分 in (1, 2, 3)
            )
            ,WITH_売上データ明細 AS (
                SELECT
                    日付
                    , 売上データ明細.コースＣＤ
                    , 行Ｎｏ
                    , 得意先ＣＤ
                    , 明細行Ｎｏ
                    , 売上データ明細.商品ＣＤ
                    , 現金個数
                    , 現金金額
                    , 現金値引
                    , 現金値引事由ＣＤ
                    , 掛売個数
                    , 掛売金額
                    , 掛売値引
                    , 掛売値引事由ＣＤ
                    , 商品マスタ.商品区分
                    , 売上データ明細.売掛現金区分
                    , 分配元数量
                FROM
                    [売上データ明細]
                    INNER JOIN [商品マスタ]
                    ON 商品マスタ.商品ＣＤ = 売上データ明細.商品ＣＤ
                WHERE
                    部署ＣＤ = $BushoCd
                    AND 日付 >= '$DateStart'
                    AND 日付 <= '$DateEnd'
            )
            SELECT
                WITH_コーステーブル.コースＣＤ
                , WITH_コーステーブル.コース名
                , WITH_コーステーブル.ＳＥＱ
                , WITH_コーステーブル.得意先ＣＤ
                , WITH_コーステーブル.得意先名
                , WITH_コーステーブル.担当者ＣＤ
                , WITH_コーステーブル.担当者名
                , WITH_売上データ明細.売掛現金区分
                , WITH_売上データ明細.日付
                , WITH_売上データ明細.行Ｎｏ
                , WITH_売上データ明細.明細行Ｎｏ
                , WITH_売上データ明細.商品ＣＤ
                , WITH_売上データ明細.現金個数
                , WITH_売上データ明細.現金金額
                , WITH_売上データ明細.現金値引
                , WITH_売上データ明細.現金値引事由ＣＤ
                , WITH_売上データ明細.掛売個数
                , WITH_売上データ明細.掛売金額
                , WITH_売上データ明細.掛売値引
                , WITH_売上データ明細.掛売値引事由ＣＤ
                , WITH_売上データ明細.商品区分
                , WITH_売上データ明細.分配元数量
            FROM WITH_コーステーブル
                    LEFT JOIN WITH_売上データ明細
                        ON WITH_売上データ明細.得意先ＣＤ=WITH_コーステーブル.得意先ＣＤ
                        AND WITH_売上データ明細.コースＣＤ=WITH_コーステーブル.コースＣＤ
            ORDER BY
                WITH_コーステーブル.コースＣＤ
                , WITH_コーステーブル.ＳＥＱ
        ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }
}
