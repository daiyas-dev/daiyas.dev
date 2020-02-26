<?php

namespace App\Http\Controllers;

use App\Models\得意先単価マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
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

    // トランザクション開始
        DB::transaction(function () use ($params, $CustomerCd) {
            // $params = $request->all();

            // $CustomerCd = $params['CustomerCd'];

            $saveList = $params['SaveList'];

            $AddList = $saveList['AddList'];
            $UpdateList = $saveList['UpdateList'];
            $OldList = $saveList['OldList'];
            $DeleteList = $saveList['DeleteList'];

            //DeleteList
            foreach($DeleteList as $rec) {

                得意先単価マスタ::query()
                ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                ->where('商品ＣＤ', $rec['商品ＣＤ'])
                ->delete();
            }

            //UpdateList
            foreach ($OldList as $index => $rec) {
                $data = $UpdateList[$index];

                得意先単価マスタ::query()
                ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                ->where('商品ＣＤ', $rec['商品ＣＤ'])
                ->update($data);
            }

            //AddList
            foreach ($AddList as $rec) {
                $data = $rec;
                $cnt = DB::table('得意先単価マスタ')
                    ->where('得意先ＣＤ', $CustomerCd)
                    ->where('商品ＣＤ', $data['商品ＣＤ'])->count();

                if ($cnt == 0){
                    得意先単価マスタ::insert($data);
                }else{
                    得意先単価マスタ::query()
                    ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->update($data);
                }
            }

            // //確認用：削除予定
            // $query = 得意先単価マスタ::query()
            //     ->when(
            //         $CustomerCd,
            //         function($q) use ($CustomerCd){
            //             return $q->where('得意先ＣＤ', $CustomerCd);
            //         }
            //     );
            // $CustomerList = $query->get();
            // throw new \Exception('hogehoge');

        });

        $query = 得意先単価マスタ::query()
            ->when(
                $CustomerCd,
                function($q) use ($CustomerCd){
                    return $q->where('得意先ＣＤ', $CustomerCd);
                }
            );
        $savedData = $query->get();

        return response()->json([
            "result" => true,
            'model' => $savedData,
        ]);
    }

    /**
     * DeleteTankaList
     */
    public function DeleteTankaList($request)
    {
        // トランザクション開始
        DB::transaction(function () use ($request) {
            $params = $request->all();

            $CustomerCd = $params['CustomerCd'];
            $ProductCd = $params['ProductCd'];

            得意先単価マスタ::query()
            ->where('得意先ＣＤ', $CustomerCd)
            ->where('商品ＣＤ', $ProductCd)
            ->delete();

            // //確認用：削除予定
            // $query = 得意先単価マスタ::query()
            //     ->when(
            //         $CustomerCd,
            //         function($q) use ($CustomerCd){
            //             return $q->where('得意先ＣＤ', $CustomerCd);
            //         }
            //     );
            // $CustomerList = $query->get();
            // throw new \Exception('hogehoge');

        });

        return response()->json([
            "result" => true,
        ]);
    }
}
