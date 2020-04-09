<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI03070Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        if($vm->PrintOrder==1){
            //コース順
            return $this->Search_Course($vm);
        }else{
            //得意先順
            return $this->search_Tokui($vm);
        }
    }
    public function search_Tokui($vm)
    {
        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;

        //検索条件(部署ＣＤ)
        $BushoArray = $vm->BushoArray;
        $WhereBusho1="";
        if($BushoArray !=null && is_array($BushoArray) && 0<count($BushoArray))
        {
            $BushoList = implode(',',$BushoArray);
            $WhereBusho1=" AND 得意先マスタ.部署ＣＤ IN( $BushoList )";
        }

        $sql = "
                WITH WITH_得意先集計 AS(
                    SELECT
                        得意先マスタ.部署ＣＤ
                        , 得意先マスタ.得意先ＣＤ
                        , 得意先マスタ.得意先名 AS 得意先名
                        , MONTH(売上データ明細.日付) AS 売上データ明細月
                        , CASE
                            WHEN 現金金額 - 現金値引 + 掛売金額 - 掛売値引 IS NULL
                            THEN 0
                            ELSE 現金金額 - 現金値引 + 掛売金額 - 掛売値引
                        END AS 金額
                    FROM
                        [得意先マスタ]
                        LEFT OUTER JOIN [売上データ明細]
                        ON 売上データ明細.得意先ＣＤ = 得意先マスタ.得意先ＣＤ
                        AND 売上データ明細.部署ＣＤ = 得意先マスタ.部署ＣＤ
                        AND 売上データ明細.日付 >= '$DateStart'
                        AND 売上データ明細.日付 <= '$DateEnd'
                        AND 売上データ明細.商品区分 >= 1
                        AND 売上データ明細.商品区分 <= 10
                        AND 売上データ明細.売掛現金区分 <> 3
                    WHERE
                         (
                            得意先マスタ.得意先ＣＤ = 得意先マスタ.受注得意先ＣＤ
                            OR 得意先マスタ.受注得意先ＣＤ = 0
                         )
                        $WhereBusho1
                )
                SELECT
                     WITH_得意先集計.部署ＣＤ
                    ,部署マスタ.部署名
                    ,WITH_得意先集計.得意先ＣＤ
                    ,WITH_得意先集計.得意先名
                    ,WITH_得意先集計.売上データ明細月
                    ,SUM(WITH_得意先集計.金額)AS 合計金額
                FROM
                    WITH_得意先集計
					LEFT JOIN 部署マスタ ON 部署マスタ.部署CD=WITH_得意先集計.部署ＣＤ
                GROUP BY
                     WITH_得意先集計.部署ＣＤ
                    ,部署マスタ.部署名
                    ,WITH_得意先集計.得意先ＣＤ
                    ,WITH_得意先集計.得意先名
                    ,WITH_得意先集計.売上データ明細月
                ORDER BY
                     WITH_得意先集計.部署ＣＤ
                    ,WITH_得意先集計.得意先ＣＤ
                ";
        $DataList = DB::select($sql);

        return response()->json($DataList);
    }
    public function Search_Course($vm)
    {
        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;

        //検索条件(部署ＣＤ)
        $BushoArray = $vm->BushoArray;
        $WhereBusho1="";
        if($BushoArray !=null && is_array($BushoArray) && 0<count($BushoArray))
        {
            $BushoList = implode(',',$BushoArray);
            $WhereBusho1=" AND コースマスタ.部署ＣＤ IN( $BushoList )";
        }

        //検索条件(コースＣＤ)
        $WhereCourse1="";
        if($vm->CourseCd!=null)
        {
            $WhereCourse1 = " AND コースマスタ.コースＣＤ = $vm->CourseCd";
        }

        $sql = "
                WITH WITH_グループ集計 AS(
                    SELECT
                        コースマスタ.部署ＣＤ
                        , コースマスタ.コースＣＤ
                        , コースマスタ.コース名
                        , 売上データ明細.得意先ＣＤ
                        , 得意先マスタ.得意先名
                        , コースマスタ.担当者ＣＤ
                        , MONTH(売上データ明細.日付) AS 売上データ明細月
                        , CASE
                            WHEN 現金金額 - 現金値引 + 掛売金額 - 掛売値引 is null
                                THEN 0
                            ELSE 現金金額 - 現金値引 + 掛売金額 - 掛売値引
                            END AS 金額
                    FROM
                        [コースマスタ]
                        LEFT OUTER JOIN [売上データ明細]
                            ON 売上データ明細.コースＣＤ = コースマスタ.コースＣＤ
                            AND 売上データ明細.日付 >= '$DateStart'
                            AND 売上データ明細.日付 <= '$DateEnd'
                            AND 売上データ明細.商品区分 >= 1
                            AND 売上データ明細.商品区分 <= 10
                            AND 売上データ明細.売掛現金区分 <> 3
                        INNER JOIN [得意先マスタ]
                            ON 得意先マスタ.得意先ＣＤ = 売上データ明細.得意先ＣＤ
                            AND 売上データ明細.部署ＣＤ = 得意先マスタ.部署ＣＤ
                    WHERE
                         (
                            得意先マスタ.得意先ＣＤ = 得意先マスタ.受注得意先ＣＤ
                            OR 得意先マスタ.受注得意先ＣＤ = 0
                         )
                         $WhereBusho1
                         $WhereCourse1
                    )
                    SELECT
                         WITH_グループ集計.部署ＣＤ
                        ,部署マスタ.部署名
                        ,WITH_グループ集計.コースＣＤ
                        ,WITH_グループ集計.コース名
                        ,WITH_グループ集計.得意先ＣＤ
                        ,WITH_グループ集計.得意先名
                        ,WITH_グループ集計.担当者ＣＤ
                        ,担当者マスタ.担当者名
                        ,WITH_グループ集計.売上データ明細月
                        ,SUM(WITH_グループ集計.金額)AS 合計金額
                    FROM
                        WITH_グループ集計
                        LEFT JOIN 部署マスタ ON 部署マスタ.部署ＣＤ = WITH_グループ集計.部署ＣＤ
                        LEFT JOIN 担当者マスタ ON 担当者マスタ.担当者ＣＤ = WITH_グループ集計.担当者ＣＤ
                    GROUP BY
                         WITH_グループ集計.部署ＣＤ
                        ,部署マスタ.部署名
                        ,WITH_グループ集計.コースＣＤ
                        ,WITH_グループ集計.コース名
                        ,WITH_グループ集計.得意先ＣＤ
                        ,WITH_グループ集計.得意先名
                        ,WITH_グループ集計.担当者ＣＤ
                        ,担当者マスタ.担当者名
                        ,WITH_グループ集計.売上データ明細月
                    ORDER BY
                         WITH_グループ集計.部署ＣＤ
                        ,WITH_グループ集計.コースＣＤ
                        ,WITH_グループ集計.得意先ＣＤ
                ";
        $DataList = DB::select($sql);

        return response()->json($DataList);
    }
}
