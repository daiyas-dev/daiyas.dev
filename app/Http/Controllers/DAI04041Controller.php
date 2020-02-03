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

        if ($data['支払方法１'] <> '4') {

            // トランザクション開始
            DB::transaction(function () use ($params, $data) {
                $CustomerCd = $params['得意先ＣＤ'];

                $cnt = DB::table('得意先マスタ')->where('得意先ＣＤ', $CustomerCd) ->count();
                if ($cnt == 0) {
                    $insertData = array_merge(['得意先ＣＤ' => $CustomerCd], $data);
                    DB::table('得意先マスタ')->insert($insertData);

                } else {
                    $updateData = array_merge(['得意先ＣＤ' => $CustomerCd], $data);
                    unset($updateData['金融機関CD']);
                    unset($updateData['金融機関支店CD']);
                    unset($updateData['記号番号']);
                    unset($updateData['口座種別']);
                    unset($updateData['口座番号']);
                    unset($updateData['口座名義人']);

                    DB::table('得意先マスタ')->where('得意先ＣＤ', $CustomerCd)->update($updateData);
                }

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
        };

        if ($data['支払方法１'] == '4') {

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
        };

        $savedData = array_merge(['得意先ＣＤ' => $params['得意先ＣＤ']], $data);

        return response()->json([
            'result' => true,
            'model' => $savedData,
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
