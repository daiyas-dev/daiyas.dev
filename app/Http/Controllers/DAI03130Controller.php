<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI03130Controller extends Controller
{
    public function getKaisha()
    {
        $sql = "
                SELECT 会社名称 FROM 部署マスタ WHERE 部署CD IN (SELECT MIN(部署CD) FROM 部署マスタ)
            ";
        return DB::selectOne($sql);
    }

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
            $WhereBusho1=" AND D1.部署ＣＤ IN( $BushoList )";
        }

        $sql = "
            SELECT
                D1.部署ＣＤ
                ,MAX(M1.部署名) AS 部署名
                ,SUM(現金個数 + 掛売個数) AS 数量
                ,SUM(現金金額 + 掛売金額 - 現金値引 - 掛売値引) AS 金額
            FROM
                売上データ明細 D1
                LEFT JOIN 部署マスタ   M1 ON M1.部署CD     = D1.部署ＣＤ
                LEFT JOIN 得意先マスタ M2 ON M2.得意先ＣＤ = D1.得意先ＣＤ
                LEFT JOIN 商品マスタ   M3 ON M3.商品ＣＤ   = D1.商品ＣＤ
            WHERE
                    日付 >= '$DateStart'
                AND 日付 <= '$DateEnd'
                AND D1.売掛現金区分 <> 4
                AND D1.分配元数量 = 0
                AND M3.弁当区分 != 8
                $WhereBusho1
            GROUP BY D1.部署ＣＤ
            ORDER BY D1.部署ＣＤ
            ";

        $DataList = DB::select($sql);
        return response()->json(
            [
                [
                    "UriageData" => $DataList,
                    "KaishaMei" => $this->getKaisha(),
                ]
            ]
        );
    }
}
