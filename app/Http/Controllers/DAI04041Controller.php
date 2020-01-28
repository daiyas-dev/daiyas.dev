<?php

namespace App\Http\Controllers;

use App\Models\得意先マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI04041Controller extends Controller
{
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $model = new 得意先マスタ();
        $model->fill($params);

        $data = collect($model)->all();

        // トランザクション開始
        DB::transaction(function() use ($params, $data) {
            $CustomerCd = $params['得意先ＣＤ'];

            DB::table('得意先マスタ')->updateOrInsert(
                ['得意先ＣＤ' => $CustomerCd],
                $data
            );

            // //確認用：削除予定
            // $query = 得意先マスタ::query()
            //     ->when(
            //         $CustomerCd,
            //         function($q) use ($CustomerCd){
            //             return $q->where('得意先ＣＤ', $CustomerCd);
            //         }
            //     );

            // $CustomerList = $query->get();
            // throw new \Exception('hogehoge');
        });

        // //確認用：削除予定
        // $data = collect($model);
        // // トランザクション開始

            // $cnt = DB::table('得意先マスタ')->where('得意先ＣＤ', $CustomerCd) ->count();
            // if ($cnt == 0) {
            //     $insertData = $data->merge(['得意先ＣＤ' => $CustomerCd])->all();
            //     DB::table('得意先マスタ')->insert($insertData);
            // } else {
            //     $updateData = $data->all();
            //     DB::table('得意先マスタ')->update($updateData);
            // }

        // });


        return response()->json([
            'result' => true,
        ]);
    }

    /**
     * GetCustomerListForDetail
     */
    public function GetCustomerListForDetail($request)
    {
        $CustomerCd = $request->CustomerCd;
        $query = 得意先マスタ::query()
            ->when(
                $CustomerCd,
                function($q) use ($CustomerCd){
                    return $q->where('得意先ＣＤ', $CustomerCd);
                }
            );

        $CustomerList = $query->get();

        return response()->json($CustomerList);
    }

}
