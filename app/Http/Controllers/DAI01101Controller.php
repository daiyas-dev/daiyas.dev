<?php

namespace App\Http\Controllers;

use App\Models\入金データ;
use App\Models\管理マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;

class DAI01101Controller extends Controller
{

    /**
     * Search
     */
    public function Search($request)
    {
        return response()->json($this->GetSalesList($request, false));
    }

    /**
     * GetParentData
     */
    public function GetParentData($request)
    {
        return response()->json($this->GetSalesList($request, true));
    }

    /**
     * GetSalesList
     */
    public function GetSalesList($request, $isParent)
    {
        $TargetDate = $request->TargetDate;
        $CustomerCd = $request->CustomerCd;
        $WhereCustomerCd = $isParent ? "得意先マスタ.得意先ＣＤ = $CustomerCd" : "得意先マスタ.受注得意先ＣＤ = $CustomerCd";

        $sql = "
            SELECT
                得意先マスタ.得意先ＣＤ,
                得意先マスタ.得意先名,
                得意先マスタ.売掛現金区分 AS 得意先売掛現金区分,
                得意先支払方法.各種名称 AS 得意先売掛現金区分名称,
                売上データ明細.日付,
                売上データ明細.行Ｎｏ,
                売上データ明細.明細行Ｎｏ,
                売上データ明細.商品ＣＤ,
                売上データ明細.現金個数,
                売上データ明細.現金金額,
                売上データ明細.現金値引,
                売上データ明細.現金値引事由ＣＤ,
                売上データ明細.掛売個数,
                売上データ明細.掛売金額,
                売上データ明細.掛売値引,
                売上データ明細.掛売値引事由ＣＤ,
                売上データ明細.食事区分,
                売上データ明細.売掛現金区分 AS 売上売掛現金区分,
                売上支払方法.各種名称 AS 売上売掛現金区分名称,
                商品マスタ.商品区分,
                商品マスタ.商品名,
                売上データ明細.分配元数量,
                売上データ明細.修正日,
                CASE WHEN 売上データ明細.食事区分 IS NULL OR 売上データ明細.食事区分 = 0 THEN 商品マスタ.食事区分 ELSE 売上データ明細.食事区分 END 食事区分,
                CASE WHEN 売上データ明細.主食ＣＤ = 0 THEN 商品マスタ.主食ＣＤ ELSE 売上データ明細.主食ＣＤ END 主食ＣＤ,
                CASE WHEN 売上データ明細.副食ＣＤ = 0 THEN 商品マスタ.副食ＣＤ ELSE 売上データ明細.副食ＣＤ END 副食ＣＤ,
                入金データ.入金日付,
                入金データ.伝票Ｎｏ,
                入金データ.現金 AS 入金額,
                入金データ.摘要,
                入金データ.備考,
                入金データ.請求日付,
                入金データ.修正日 AS 入金データ修正日
            FROM
                [得意先マスタ]
                    LEFT JOIN 各種テーブル 得意先支払方法
                        ON 得意先支払方法.各種CD=1
                        AND 得意先支払方法.行NO = 得意先マスタ.売掛現金区分
                    LEFT OUTER JOIN [売上データ明細]
                        ON 売上データ明細.日付 = '$TargetDate'
                        AND 売上データ明細.部署ＣＤ = 得意先マスタ.部署ＣＤ
                        AND 売上データ明細.得意先ＣＤ = 得意先マスタ.得意先ＣＤ
                    LEFT JOIN 各種テーブル 売上支払方法
                        ON 売上支払方法.各種CD=1
                        AND 売上支払方法.行NO = 売上データ明細.売掛現金区分
                    LEFT OUTER JOIN [商品マスタ]
                        ON 商品マスタ.商品ＣＤ = 売上データ明細.商品ＣＤ
                    LEFT OUTER JOIN [入金データ]
                        ON 入金データ.入金日付 = '$TargetDate'
                        AND 入金データ.得意先ＣＤ = 得意先マスタ.得意先ＣＤ
                        AND 入金データ.入金区分 = 1
            WHERE
				$WhereCustomerCd
            ORDER BY
                得意先マスタ.得意先ＣＤ,
                売上データ明細.行Ｎｏ,
                売上データ明細.商品ＣＤ
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

        DB::beginTransaction();

        try {
            $params = $request->all();

            $SaveList = $params['SaveList'];

            $date = Carbon::now()->format('Y-m-d H:i:s');
            foreach ($SaveList as $rec) {
                if (isset($rec['伝票Ｎｏ']) && !!$rec['伝票Ｎｏ']) {
                    $r = 入金データ::query()
                        ->where('入金日付', $rec['入金日付'])
                        ->where('伝票Ｎｏ', $rec['伝票Ｎｏ'])
                        ->get();

                    if (count($r) != 1) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => null]);
                        continue;
                    } else if ($rec['修正日'] != $r[0]->修正日) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                        continue;
                    }

                    //現金が0の場合は削除扱い
                    if (!isset($rec['現金']) && !isset($rec['現金'])) {
                        入金データ::query()
                            ->where('入金日付', $rec['入金日付'])
                            ->where('伝票Ｎｏ', $rec['伝票Ｎｏ'])
                            ->delete();
                    } else {
                        $rec['修正日'] = $date;

                        入金データ::query()
                            ->where('入金日付', $rec['入金日付'])
                            ->where('伝票Ｎｏ', $rec['伝票Ｎｏ'])
                            ->update($rec);
                    }
                } else {
                    $no = 管理マスタ::query()
                        ->where('管理ＫＥＹ', 1)
                        ->max('入金伝票Ｎｏ') + 1;

                    管理マスタ::query()
                        ->where('管理ＫＥＹ', 1)
                        ->update(['入金伝票Ｎｏ' => $no]);

                    $rec['伝票Ｎｏ'] = $no;
                    $rec['修正日'] = $date;

                    入金データ::insert($rec);
                }
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            "edited" => $this->GetSalesList($request),
        ]);
    }
}
