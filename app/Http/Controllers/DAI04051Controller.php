<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use App\Models\得意先単価マスタ新;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;

class DAI04051Controller extends Controller
{
    /**
     * GetProductList
     */
    public function GetProductList($request)
    {
        $sql = "
            SELECT
                PM.商品ＣＤ,
                PM.商品名,
                PM.商品区分,
                PM.売価単価
            FROM
                商品マスタ PM
            WHERE
                表示ＦＬＧ=0 AND 弁当区分 IN (0, 8, 9)
            ORDER BY 商品ＣＤ

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
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $CustomerCd = $params['CustomerCd'];

        DB::beginTransaction();
        try {
            $saveList = $params['SaveList'];

            $AddList = $saveList['AddList'];
            $UpdateList = $saveList['UpdateList'];
            $OldList = $saveList['OldList'];
            $DeleteList = $saveList['DeleteList'];

            //DeleteList
            foreach($DeleteList as $rec) {
                得意先単価マスタ新::query()
                    ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->where('適用開始日', $rec['適用開始日'])
                    ->delete();
            }

            //UpdateList
            foreach ($OldList as $index => $rec) {
                $data = $UpdateList[$index];

                得意先単価マスタ新::query()
                    ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->where('適用開始日', $rec['適用開始日'])
                    ->update($data);
            }

            //AddList
            foreach ($AddList as $rec) {
                $data = $rec;
                $cnt = DB::table('得意先単価マスタ新')
                    ->where('得意先ＣＤ', $CustomerCd)
                    ->where('商品ＣＤ', $data['商品ＣＤ'])
                    ->where('適用開始日', $rec['適用開始日'])
                    ->count();

                if ($cnt == 0){
                    得意先単価マスタ新::insert($data);
                }else{
                    得意先単価マスタ新::query()
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->where('商品ＣＤ', $rec['商品ＣＤ'])
                        ->where('適用開始日', $rec['適用開始日'])
                        ->update($data);
                }
            }

            DB::commit();

            //モバイルsv更新
            $ds = new DataSendWrapper();
            $ds->UpdateCustomerPricemasterNew($CustomerCd);
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        $Utilities = new UtilitiesController();

        return response()->json([
            "result" => true,
            'model' => $Utilities->SearchTankaList($request),
        ]);
    }

    /**
     * DeleteTankaList
     */
    public function DeleteTankaList($request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();

            得意先単価マスタ新::query()
                ->where('得意先ＣＤ', $params['得意先ＣＤ'])
                ->where('商品ＣＤ', $params['商品ＣＤ'])
                ->where('適用開始日', $params['適用開始日'])
                ->delete();

            DB::commit();
            //モバイルsv更新
            $ds = new DataSendWrapper();
            $ds->Delete('得意先単価マスタ新', $params, true, null, $params['得意先ＣＤ'], null);
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            "result" => true,
        ]);
    }
}
