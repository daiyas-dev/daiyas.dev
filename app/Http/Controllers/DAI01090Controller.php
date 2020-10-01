<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use App\Models\モバイル予測入力;
use App\Models\日別得意先製造パターン;
use App\Models\注文データ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;

class DAI01090Controller extends Controller
{

    /**
     *  GetProductList
     */

    public function GetProductList($request)
    {
        $CustomerCd = $request->CustomerCd;
        $DateStart = $request->DateStart;
        $DateEnd = $request->DateEnd;

        $sql = "
            SELECT
                MTT.*,
                PM.商品名,
                PM.商品略称,
                PM.商品区分
            FROM
                (
                    SELECT
                        *
                    FROM (
                        SELECT
                            *
                            , RANK() OVER(PARTITION BY 得意先ＣＤ, 商品ＣＤ ORDER BY 適用開始日 DESC) AS RNK
                        FROM
                            得意先単価マスタ新
                        WHERE
                            得意先ＣＤ=$CustomerCd
                        AND 適用開始日 <= '$DateStart'
                    ) TT1
                    WHERE
                        RNK = 1
					UNION
                    SELECT
                        *
                    FROM (
                        SELECT
                            *
                            , RANK() OVER(PARTITION BY 得意先ＣＤ, 商品ＣＤ ORDER BY 適用開始日 DESC) AS RNK
                        FROM
                            得意先単価マスタ新
                        WHERE
                            得意先ＣＤ=$CustomerCd
                        AND 適用開始日 >= '$DateStart' AND 適用開始日 <= '$DateEnd'
                    ) TT2
                ) MTT
                LEFT OUTER JOIN 商品マスタ PM
                    ON	PM.商品ＣＤ=MTT.商品ＣＤ
            ORDER BY
                MTT.商品ＣＤ,
                MTT.適用開始日 DESC
        ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }

    /**
     * Search
     */
    public function Search($vm)
    {
        return response()->json($this->GetOrderList($vm));
    }

    /**
     * GetOrderList
     */
    public function GetOrderList($vm)
    {
        $BushoCd = $vm->BushoCd;
        $DeliveryDate = $vm->DeliveryDate;
        $CustomerCd = $vm->CustomerCd;

        $sql = "
WITH 対象日数 AS (
    SELECT
		0 as v
    UNION ALL
    SELECT
		v + 1
    FROM
		対象日数 t
    WHERE
		t.v < DAY(EOMONTH('$DeliveryDate')) - 1
),
対象日付 AS (
SELECT
	DATEADD(day, v, '$DeliveryDate') AS 配送日
FROM
	対象日数
)
SELECT
	対象日付.配送日
	,IIF(祝日マスタ.対象日付 IS NULL, IIF(
        (SELECT
            SUBSTRING(休日設定, DATEPART(WEEKDAY, 対象日付.配送日), 1) as 休日設定
        FROM 得意先マスタ
        WHERE 得意先ＣＤ = $CustomerCd
        )=1
        , 1, 0), 1) AS 休日指定
    ,chumon.注文区分
	,CONVERT(varchar, chumon.注文日付, 112) 注文日付
	,chumon.注文時間
	,tokui.売掛現金区分
	,tokui.部署ＣＤ
	,tokui.得意先ＣＤ
	,chumon.明細行Ｎｏ
	,chumon.商品ＣＤ
	,chumon.商品区分
	,chumon.入力区分
	,chumon.現金個数
	,chumon.現金金額
	,chumon.掛売個数
	,chumon.掛売金額
	,chumon.備考１
	,chumon.備考２
	,chumon.備考３
	,chumon.備考４
	,chumon.備考５
	,chumon.予備金額１
	,chumon.予備金額２
	,chumon.予備ＣＤ１
	,chumon.予備ＣＤ２
	,chumon.修正担当者ＣＤ
	,chumon.修正日
	,chumon.特記_社内用
	,chumon.特記_配送用
	,chumon.特記_通知用
    ,IIF(chumon.得意先ＣＤ IS NULL, tokui.備考１, chumon.特記_社内用) AS 備考社内
    ,IIF(chumon.得意先ＣＤ IS NULL, tokui.備考２, chumon.特記_配送用) AS 備考配送
    ,IIF(chumon.得意先ＣＤ IS NULL, tokui.備考３, chumon.特記_通知用) AS 備考通知
FROM
	対象日付
	LEFT JOIN 祝日マスタ
		ON 祝日マスタ.対象日付=対象日付.配送日
	LEFT JOIN 注文データ chumon
		ON 対象日付.配送日=chumon.配送日
		AND chumon.注文区分 = 0
		AND chumon.部署ＣＤ = $BushoCd
		AND chumon.得意先ＣＤ = $CustomerCd
	LEFT JOIN 得意先マスタ tokui
		ON tokui.部署ＣＤ = $BushoCd AND tokui.得意先ＣＤ = $CustomerCd
ORDER BY
	対象日付.配送日
        ";

        $DataList = DB::select(DB::raw($sql));

        return $DataList;
    }


    /**
     * Save
     */
    public function Save($request)
    {
        $skip = [];
        $CustomerCd = $request->CustomerCd;

        DB::beginTransaction();

        try {
            $params = $request->all();

            $SaveList = $params['SaveList'];

            $sql = "
                    SELECT 備考１ as '特記_社内用',
                           備考２ as '特記_配送用',
                           備考３ as '特記_通知用'
                    FROM 得意先マスタ
                    WHERE 得意先ＣＤ = $CustomerCd
            ";
            $Tokuisaki = DB::selectOne($sql);

            $date = Carbon::now()->format('Y-m-d H:i:s');
            foreach ($SaveList as $rec) {
                // $r = 注文データ::query()
                //     ->where('注文区分', $rec['注文区分'])
                //     ->where('注文日付', $rec['注文日付'])
                //     ->where('部署ＣＤ', $rec['部署ＣＤ'])
                //     ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                //     ->where('配送日', $rec['配送日'])
                //     ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                //     ->get();

                // $rec['備考１'] = $rec['備考１'] ?? '';
                // $rec['備考２'] = $rec['備考２'] ?? '';
                // $rec['備考３'] = $rec['備考３'] ?? '';
                // $rec['備考４'] = $rec['備考４'] ?? '';
                // $rec['備考５'] = $rec['備考５'] ?? '';
                // $rec['特記_社内用'] = $rec['特記_社内用'] ?? '';
                // $rec['特記_配送用'] = $rec['特記_配送用'] ?? '';
                // $rec['特記_通知用'] = $rec['特記_通知用'] ?? '';

                注文データ::query()
                ->where('注文区分', $rec['注文区分'])
                ->where('注文日付', $rec['注文日付'])
                ->where('部署ＣＤ', $rec['部署ＣＤ'])
                ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                ->where('配送日', $rec['配送日'])
                ->where('商品ＣＤ', $rec['商品ＣＤ'])
                ->delete();

            if(isset($rec['現金個数']) || isset($rec['掛売個数'])){
                $no = 注文データ::query()
                ->where('注文区分', $rec['注文区分'])
                ->where('注文日付', $rec['注文日付'])
                ->where('部署ＣＤ', $rec['部署ＣＤ'])
                ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                ->where('配送日', $rec['配送日'])
                ->max('明細行Ｎｏ') + 1;

                $rec['注文区分'] = $rec['注文区分'] ?? 0;
                $rec['注文日付'] = $rec['注文日付'] ?? 0;
                $rec['部署ＣＤ'] = $rec['部署ＣＤ'] ?? 0;
                $rec['得意先ＣＤ'] = $rec['得意先ＣＤ'] ?? 0;
                $rec['明細行Ｎｏ'] = $no;
                $rec['現金個数'] = $rec['現金個数'] ?? 0;
                $rec['現金金額'] = $rec['現金金額'] ?? 0;
                $rec['掛売個数'] = $rec['掛売個数'] ?? 0;
                $rec['掛売金額'] = $rec['掛売金額'] ?? 0;
                $rec['修正日'] = $date;
                $rec['備考１'] = $rec['備考１'] ?? '';
                $rec['備考２'] = $rec['備考２'] ?? '';
                $rec['備考３'] = $rec['備考３'] ?? '';
                $rec['備考４'] = $rec['備考４'] ?? '';
                $rec['備考５'] = $rec['備考５'] ?? '';
                $rec['特記_社内用'] = $Tokuisaki->特記_社内用 ?? '';
                $rec['特記_配送用'] = $Tokuisaki->特記_配送用 ?? '';
                $rec['特記_通知用'] = $Tokuisaki->特記_通知用 ?? '';

                注文データ::insert($rec);
            }

                // if (isset($rec['修正日']) && !!$rec['修正日']) {
                //     if (count($r) != 1) {
                //         $skip = collect($skip)->push(["target" => $rec, "current" => null]);
                //         continue;
                //     } else if ($rec['修正日'] != $r[0]->修正日) {
                //         $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                //         continue;
                //     }

                //     //現金個数及び掛売個数がnullの場合は削除扱い
                //     if (!isset($rec['現金個数']) && !isset($rec['掛売個数'])) {
                //         注文データ::query()
                //             ->where('注文区分', $rec['注文区分'])
                //             ->where('注文日付', $rec['注文日付'])
                //             ->where('部署ＣＤ', $rec['部署ＣＤ'])
                //             ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                //             ->where('配送日', $rec['配送日'])
                //             ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                //             ->delete();

                //     } else{
                //         注文データ::query()
                //             ->where('注文区分', $rec['注文区分'])
                //             ->where('注文日付', $rec['注文日付'])
                //             ->where('部署ＣＤ', $rec['部署ＣＤ'])
                //             ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                //             ->where('配送日', $rec['配送日'])
                //             ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                //             ->delete();

                //         $no = 注文データ::query()
                //         ->where('注文区分', $rec['注文区分'])
                //         ->where('注文日付', $rec['注文日付'])
                //         ->where('部署ＣＤ', $rec['部署ＣＤ'])
                //         ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                //         ->where('配送日', $rec['配送日'])
                //         ->max('明細行Ｎｏ') + 1;
    
                //         $rec['明細行Ｎｏ'] = $no;
                //         $rec['現金個数'] = $rec['現金個数'] ?? 0;
                //         $rec['現金金額'] = $rec['現金金額'] ?? 0;
                //         $rec['掛売個数'] = $rec['掛売個数'] ?? 0;
                //         $rec['掛売金額'] = $rec['掛売金額'] ?? 0;
                //         $rec['修正日'] = $date;
    
                //         注文データ::insert($rec);
                //     }
                // } else {
                //     $no = 注文データ::query()
                //         ->where('注文区分', $rec['注文区分'])
                //         ->where('注文日付', $rec['注文日付'])
                //         ->where('部署ＣＤ', $rec['部署ＣＤ'])
                //         ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                //         ->where('配送日', $rec['配送日'])
                //         ->max('明細行Ｎｏ') + 1;

                //     $rec['明細行Ｎｏ'] = $no;
                //     $rec['現金個数'] = $rec['現金個数'] ?? 0;
                //     $rec['現金金額'] = $rec['現金金額'] ?? 0;
                //     $rec['掛売個数'] = $rec['掛売個数'] ?? 0;
                //     $rec['掛売金額'] = $rec['掛売金額'] ?? 0;
                //     $rec['修正日'] = $date;

                //     注文データ::insert($rec);
                // }
            }

            DB::commit();
            //モバイルsv更新
            $ds = new DataSendWrapper();
            $ds->MultiUpdateOrderData($params['BushoCd'], $params['CustomerCd'], $params['DateStart'], $params['DateEnd']);
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            "edited" => count($skip) > 0 ? $this->GetOrderList($request) : [],
        ]);
    }
}
