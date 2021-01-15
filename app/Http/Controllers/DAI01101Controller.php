<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use App\Models\入金データ;
use App\Models\売上データ明細;
use App\Models\管理マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;

class DAI01101Controller extends Controller
{

    /**
     * GetProductList
     */
    public function GetProductList($request)
    {
        $CustomerCd = $request->CustomerCd;

        $sql = "
            SELECT
                PM.商品ＣＤ,
                PM.商品名,
                PM.商品区分,
                PM.主食ＣＤ,
                PM.副食ＣＤ,
                IIF(MTT.商品ＣＤ IS NOT NULL, MTT.単価, PM.売価単価) AS 売価単価
            FROM
                商品マスタ PM
                LEFT JOIN 得意先単価マスタ MTT
                    ON	PM.商品ＣＤ=MTT.商品ＣＤ
                    AND MTT.得意先ＣＤ=$CustomerCd
            WHERE
                表示ＦＬＧ=0
				AND PM.商品ＣＤ IN (SELECT サブ各種CD1 FROM 各種テーブル WHERE 各種CD=44)
        ";

        $Result = collect(DB::select($sql))
            ->map(function ($product) {
                $vm = (object) $product;

                $vm->Cd = $product->商品ＣＤ;
                $vm->CdNm = $product->商品名;

                return $vm;
            })
            ->values();

        return response()->json($Result);
    }

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
                売上データ明細.受注Ｎｏ,
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
                商品マスタ.商品名 AS 単価,
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

        //モバイルsv更新用
        $MUpdateList = [];
        $MInsertList = [];
        $MDeleteList = [];

        DB::beginTransaction();

        try {
            $BushoCd = $request->BushoCd;
            $TargetDate = $request->TargetDate;
            $CourseCd = $request->CourseCd;
            $CustomerCd = $request->CustomerCd;
            $EditUser = $request->EditUser;

            $params = $request->all();

            $UriageList = $params['UriageList'];

            $date = Carbon::now()->format('Y-m-d H:i:s');

            //親得意先
            $sql = "
                UPDATE 売上データ明細
                SET
                    現金個数 = 0,
                    掛売個数 = 0,
                    分配元数量 = 現金個数 + 掛売個数 + 分配元数量,
                    修正日 = '$date',
                    修正担当者ＣＤ = $EditUser
                WHERE
                    日付 = '$TargetDate'
                AND 部署ＣＤ = $BushoCd
                AND コースＣＤ = $CourseCd
                AND 得意先ＣＤ = $CustomerCd
            ";

            //親得意先　モバイルsv更新用
            $Msql = "
                UPDATE SalesDetailsData
                SET
                    cash_num = 0,
                    sold_num = 0,
                    source_quantity = cash_num + sold_num + source_quantity,
                    updated_at = '$date',
                    updated_responsible_code = $EditUser
                WHERE
                    date = '$TargetDate'
                AND department_code = $BushoCd
                AND course_code = $CourseCd
                AND customer_code = $CustomerCd
            ";

            DB::update($sql);

            //子得意先
            foreach ($UriageList as $rec) {
                if (isset($rec['修正日']) && !!$rec['修正日']) {
                    $r = 売上データ明細::query()
                        ->where('日付', $rec['日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('コースＣＤ', $rec['コースＣＤ'])
                        ->where('行Ｎｏ', $rec['行Ｎｏ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                        ->where('受注Ｎｏ', $rec['受注Ｎｏ'])
                        ->get();

                    if (count($r) != 1) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => null]);
                        continue;
                    } else if ($rec['修正日'] != $r[0]->修正日) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                        continue;
                    }

                    $rec['修正日'] = $date;
                    $rec['請求日付'] = $rec['請求日付'] ?? '';


                    //個数が0の場合は削除扱い
                    if ($rec['現金個数'] == '0' && $rec['掛売個数'] == '0') {
                        売上データ明細::query()
                            ->where('日付', $rec['日付'])
                            ->where('部署ＣＤ', $rec['部署ＣＤ'])
                            ->where('コースＣＤ', $rec['コースＣＤ'])
                            ->where('行Ｎｏ', $rec['行Ｎｏ'])
                            ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                            ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                            ->where('受注Ｎｏ', $rec['受注Ｎｏ'])
                            ->delete($rec);

                        //モバイルsv更新用
                        array_push($MDeleteList, $rec);
                    } else {
                        売上データ明細::query()
                            ->where('日付', $rec['日付'])
                            ->where('部署ＣＤ', $rec['部署ＣＤ'])
                            ->where('コースＣＤ', $rec['コースＣＤ'])
                            ->where('行Ｎｏ', $rec['行Ｎｏ'])
                            ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                            ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                            ->where('受注Ｎｏ', $rec['受注Ｎｏ'])
                            ->update($rec);

                        //モバイルsv更新用
                        array_push($MUpdateList, $rec);
                    }
                } else {
                    $gno = 売上データ明細::query()
                        ->where('日付', $rec['日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('コースＣＤ', $rec['コースＣＤ'])
                        ->max('行Ｎｏ') + 1;

                    $mno = 売上データ明細::query()
                        ->where('日付', $rec['日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('コースＣＤ', $rec['コースＣＤ'])
                        ->where('行Ｎｏ', $rec['行Ｎｏ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->max('明細行Ｎｏ') + 1;

                    $rec['修正日'] = $date;
                    $rec['行Ｎｏ'] = $gno;
                    $rec['明細行Ｎｏ'] = $mno;
                    $rec['請求日付'] = $rec['請求日付'] ?? '';

                    売上データ明細::insert($rec);

                    //モバイルsv更新用
                    array_push($MInsertList, $rec);
                }
            }

            $NyukinList = $params['NyukinList'];

            foreach ($NyukinList as $rec) {
                if (isset($rec['伝票Ｎｏ']) && !!$rec['伝票Ｎｏ']) {
                    $r = 入金データ::query()
                        ->where('入金日付', $rec['入金日付'])
                        ->where('伝票Ｎｏ', $rec['伝票Ｎｏ'])
                        ->get();

                    if (count($r) != 1) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => null]);
                        continue;
                    } elseif ($rec['修正日'] != $r[0]->修正日) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                        continue;
                    }

                    入金データ::query()
                        ->where('入金日付', $rec['入金日付'])
                        ->where('伝票Ｎｏ', $rec['伝票Ｎｏ'])
                        ->delete();
                }

                //現金が0の場合は登録しない
                if (!!isset($rec['現金']) && $rec['現金'] != 0) {
                    $no = 管理マスタ::query()
                        ->where('管理ＫＥＹ', 1)
                        ->max('入金伝票Ｎｏ') + 1;

                    管理マスタ::query()
                        ->where('管理ＫＥＹ', 1)
                        ->update(['入金伝票Ｎｏ' => $no]);

                    $rec['入金日付'] = $TargetDate;
                    $rec['伝票Ｎｏ'] = $no;
                    $rec['修正日'] = $date;

                    入金データ::insert($rec);
                }
            }

            DB::commit();

            //モバイルSvを更新
            //親得意先
            $ds = new DataSendWrapper();
            $ds->Execute($Msql, true, $BushoCd, null, $CourseCd);

            foreach ($MDeleteList as $rec) {
                $ds->Delete('売上データ明細', $rec, true, $rec['部署ＣＤ'], null, $rec['コースＣＤ']);
            }
            foreach ($MInsertList as $rec) {
                $ds->InsertUriageMeisaiData($rec);
            }
            foreach ($MUpdateList as $rec) {
                $ds->InsertUriageMeisaiData($rec);
            }
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            "edited" => count($skip) > 0 ? $this->GetSalesList($request,false) : [],
        ]);
    }
}
