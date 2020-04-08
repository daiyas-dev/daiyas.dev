<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI03060Controller extends Controller
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
        if($BushoArray !=null && is_array($BushoArray) && 0<count($BushoArray))
        {
            $BushoList = implode(',',$BushoArray);
            $WhereBusho1=" AND コースマスタ.部署ＣＤ IN( $BushoList )";
        }

        //TODO:検索条件を画面から受け取れるようにする
        $TokuiCd=$vm->CustomerCd;
        $WhereTokui="";
        if($TokuiCd!=null)
        {
            $WhereTokui = " AND 売上データ明細.得意先ＣＤ=$TokuiCd ";
        }

        $sql = "
                SELECT
                      コースマスタ.部署ＣＤ
                    , コースマスタ.コースＣＤ
                    , コースマスタ.担当者ＣＤ as 担当者ＣＤ
                    , コースマスタ.コース名 as コース名
                    , 部署マスタ.部署名
                    , 担当者マスタ.担当者名
                    , MONTH(売上データ明細.日付) as 売上データ明細月
                    , case
                        WHEN 現金金額 - 現金値引 + 掛売金額 - 掛売値引 is null
                            THEN 0
                        ELSE 現金金額 - 現金値引 + 掛売金額 - 掛売値引
                        END
                    as 合計金額
                FROM
                    [コースマスタ]
                    left outer join [売上データ明細]
                        ON 売上データ明細.コースＣＤ = コースマスタ.コースＣＤ
                        AND 売上データ明細.部署ＣＤ = コースマスタ.部署ＣＤ
                    inner join 得意先マスタ tokui
                        on tokui.得意先ＣＤ = 売上データ明細.得意先ＣＤ
                        and tokui.部署CD = 売上データ明細.部署ＣＤ
                        AND 売上データ明細.日付 >= '$DateStart'
                        AND 売上データ明細.日付 <= '$DateEnd'
                        AND 売上データ明細.商品区分 >= 1
                        AND 売上データ明細.商品区分 <= 10
                        AND 売上データ明細.売掛現金区分 <> 2
                        AND コースマスタ.コース区分 in (1, 2, 3, 4)
                        $WhereTokui
                    left join 部署マスタ on 部署マスタ.部署ＣＤ = コースマスタ.部署ＣＤ
                    left join 担当者マスタ on 担当者マスタ.担当者ＣＤ = コースマスタ.担当者ＣＤ
                WHERE
                    (
                        tokui.得意先ＣＤ = tokui.受注得意先ＣＤ
                        OR tokui.受注得意先ＣＤ = 0
                    )
                    $WhereBusho1
                order by
                     コースマスタ.部署ＣＤ
                    , コースマスタ.コースＣＤ
            ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }
}
