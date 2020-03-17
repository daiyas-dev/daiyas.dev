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
SELECT
    URIAGE.日付,
URIAGE.得意先ＣＤ,
TOKUI.得意先名,
URIAGE.コースＣＤ,
COU.コース名,
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
LEFT JOIN コースマスタ COU ON COU.コースＣＤ = URIAGE.コースＣＤ AND COU.部署ＣＤ = URIAGE.部署ＣＤ
LEFT JOIN 担当者マスタ TANTO ON TANTO.担当者ＣＤ = COU.担当者ＣＤ
LEFT JOIN 商品マスタ SHOHIN ON SHOHIN.商品ＣＤ = URIAGE.商品ＣＤ
LEFT JOIN (SELECT 部署CD, 得意先CD, SUM(現金) 入金額 FROM 入金データ WHERE '20190801' <= CONVERT(VARCHAR, 入金日付, 112)  AND CONVERT(VARCHAR, 入金日付, 112) <= '20200317' GROUP BY 部署CD, 得意先CD) NYUKIN ON NYUKIN.得意先ＣＤ = URIAGE.得意先ＣＤ AND NYUKIN.部署ＣＤ = URIAGE.部署ＣＤ
WHERE
URIAGE.部署ＣＤ = 101
AND (TOKUI.得意先ＣＤ != TOKUI.受注得意先ＣＤ AND TOKUI.受注得意先ＣＤ = 0)
AND URIAGE.現金個数 + URIAGE.掛売個数 > 0
AND '20190801' <= CONVERT(VARCHAR, URIAGE.日付, 112) AND CONVERT(VARCHAR, URIAGE.日付, 112) <= '20200317'
ORDER BY URIAGE.日付,URIAGE.コースＣＤ
SELECT
	D1.部署ＣＤ,部署名,日付  ,
	SUM(CASE WHEN D1.売掛現金区分 = 0 AND M3.商品区分 != 9 THEN D1.現金個数 + D1.掛売個数 ELSE 0 END) as 現金個数  ,
	SUM(CASE WHEN D1.売掛現金区分 = 0 AND M3.商品区分 != 9 THEN D1.現金金額-D1.現金値引 + D1.掛売金額-D1.掛売値引 ELSE 0 END) as 現金金額  ,
	SUM(CASE WHEN D1.売掛現金区分 = 1 AND M3.商品区分 != 9 THEN D1.現金個数 + 掛売個数 ELSE 0 END) as 売掛個数  ,
	SUM(CASE WHEN D1.売掛現金区分 = 1 AND M3.商品区分 != 9 THEN D1.現金金額-D1.現金値引 + D1.掛売金額-D1.掛売値引 ELSE 0 END) as 売掛金額  ,
	SUM(CASE WHEN D1.売掛現金区分 = 2 AND M3.商品区分 != 9 THEN D1.現金個数 + 掛売個数 ELSE 0 END) as チケット個数  ,
	SUM(CASE WHEN D1.売掛現金区分 = 2 AND M3.商品区分 != 9 THEN D1.現金金額-D1.現金値引 + D1.掛売金額-D1.掛売値引 ELSE 0 END) as チケット金額  ,
	SUM(CASE WHEN D1.売掛現金区分 = 3 AND M3.商品区分 != 9 THEN D1.現金個数 + 掛売個数 ELSE 0 END) as バークレー個数  ,
	SUM(CASE WHEN D1.売掛現金区分 = 3 AND M3.商品区分 != 9 THEN D1.現金金額-D1.現金値引 + D1.掛売金額-D1.掛売値引 ELSE 0 END) as バークレー金額
FROM
	売上データ明細 D1
	INNER JOIN [得意先マスタ] ON 得意先マスタ.得意先ＣＤ = D1.得意先ＣＤ
    INNER JOIN [商品マスタ] M3 ON M3.商品ＣＤ = D1.商品ＣＤ
    INNER JOIN [部署マスタ]　ON 部署マスタ.部署ＣＤ　= D1.部署ＣＤ
WHERE
    D1.部署ＣＤ = $BushoCd
    AND
    日付 >= '$DateStart' AND   日付 <= '$DateEnd'
    AND
    D1.売掛現金区分 <> 4
    AND D1.分配元数量 = 0
    AND M3.弁当区分 != 10
    AND M3.弁当区分 != 8
    group by D1.部署ＣＤ,部署名,日付  order by D1.部署ＣＤ,日付
        ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }
}
