<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI03120Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;

        $BushoCd = $vm->BushoCd;
        $WhereBusho1="";
        if($BushoCd != null)
        {
            $WhereBusho1=" AND URI.部署ＣＤ = $BushoCd";
        }

        $sql = "
                SELECT
                      URI.部署ＣＤ
                    , 部署マスタ.部署名
                    , URI.商品ＣＤ
                    , MAX(SMAS.商品名) AS 商品名
                    , MAX(予備金額１) AS 単価
                    , SUM(現金個数 + 掛売個数) AS 数量
                    , SUM(現金金額 + 掛売金額 - 現金値引 - 掛売値引) AS 金額
                FROM
                    売上データ明細 URI
                    LEFT JOIN 商品マスタ SMAS ON SMAS.商品ＣＤ = URI.商品ＣＤ
                    LEFT JOIN 得意先マスタ TMAS ON TMAS.得意先ＣＤ = URI.得意先ＣＤ
                    LEFT JOIN 部署マスタ ON 部署マスタ.部署ＣＤ = URI.部署ＣＤ
                WHERE
                        URI.日付 >= '$DateStart'
                    AND URI.日付 <= '$DateEnd'
                    AND URI.売掛現金区分 <> 4
                    AND URI.分配元数量 = 0
                    $WhereBusho1
                    AND URI.商品ＣＤ <> 800
                    AND (URI.現金個数>0 OR URI.掛売個数>0 OR URI.商品ＣＤ = 25)
                GROUP BY
                        URI.部署ＣＤ
                      , 部署マスタ.部署名
                      , URI.商品ＣＤ
                ORDER BY
                      URI.部署ＣＤ
                    , URI.商品ＣＤ
            ";

        $DataList = DB::select($sql);
        return response()->json($DataList);
    }
}
