<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI01250Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $BushoCd = $vm->BushoCd;
        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;


        $sql = "
SELECT DISTINCT
    URIAGE.日付,
    URIAGE.部署ＣＤ,
    BUSHO.部署名,
    URIAGE.得意先ＣＤ,
    TOKUI.得意先名,
    URIAGE.コースＣＤ,
    COU.コース名,
    COU.コース区分,
    COU.担当者ＣＤ,
    TANTO.担当者名,
    URIAGE.商品ＣＤ,
    SHOHIN.商品名,
    URIAGE.現金個数 + URIAGE.掛売個数 数量,
    URIAGE.掛売金額 + URIAGE.現金金額 金額,
    NYUKIN.入金額
FROM
    売上データ明細 URIAGE
    INNER JOIN 得意先マスタ TOKUI ON TOKUI.得意先ＣＤ = URIAGE.得意先ＣＤ AND TOKUI.部署ＣＤ = URIAGE.部署ＣＤ
    INNER JOIN 得意先マスタ TOKUI2 on TOKUI2.受注得意先ＣＤ = URIAGE.得意先ＣＤ
    LEFT JOIN コースマスタ COU ON COU.コースＣＤ = URIAGE.コースＣＤ AND COU.部署ＣＤ = URIAGE.部署ＣＤ
    LEFT JOIN 部署マスタ BUSHO ON BUSHO.部署CD = URIAGE.部署ＣＤ
    LEFT JOIN 担当者マスタ TANTO ON TANTO.担当者ＣＤ = COU.担当者ＣＤ
    LEFT JOIN 商品マスタ SHOHIN ON SHOHIN.商品ＣＤ = URIAGE.商品ＣＤ
    LEFT JOIN (SELECT 部署CD, 得意先CD, SUM(現金) 入金額 FROM 入金データ WHERE '$DateStart' <= CONVERT(VARCHAR, 入金日付, 112)  AND CONVERT(VARCHAR, 入金日付, 112) <= '$DateEnd' GROUP BY 部署CD, 得意先CD) NYUKIN ON NYUKIN.得意先ＣＤ = URIAGE.得意先ＣＤ AND NYUKIN.部署ＣＤ = URIAGE.部署ＣＤ
WHERE
    URIAGE.部署ＣＤ = $BushoCd
    AND (TOKUI.得意先ＣＤ != TOKUI.受注得意先ＣＤ AND TOKUI.受注得意先ＣＤ = 0)
    AND URIAGE.現金個数 + URIAGE.掛売個数 > 0
    AND '$DateStart' <= CONVERT(VARCHAR, URIAGE.日付, 112) AND CONVERT(VARCHAR, URIAGE.日付, 112) <= '$DateEnd'
ORDER BY URIAGE.日付,URIAGE.コースＣＤ
        ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }
}
