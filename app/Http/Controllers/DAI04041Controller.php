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

        $CustomerCd = $params['得意先ＣＤ'];
        $data = collect($model)->all();
        $newData = array_merge(['得意先ＣＤ' => $CustomerCd], $data);

        //更新対象外のカラム
        if ($data['支払方法１'] <> '4') {
            unset($newData['金融機関CD']);
            unset($newData['金融機関支店CD']);
            unset($newData['記号番号']);
            unset($newData['口座種別']);
            unset($newData['口座番号']);
            unset($newData['口座名義人']);
        };

        // トランザクション開始
        DB::transaction(function () use ($CustomerCd, $newData) {

            $cnt = DB::table('得意先マスタ')->where('得意先ＣＤ', $CustomerCd) ->count();
            if ($cnt == 0) {
                DB::table('得意先マスタ')->insert($newData);
            } else {
                DB::table('得意先マスタ')->where('得意先ＣＤ', $CustomerCd)->update($newData);
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

        $savedData = array_merge(['得意先ＣＤ' => $params['得意先ＣＤ']], $data);

        return response()->json([
            'result' => true,
            'model' => $savedData,
        ]);
    }

    /**
     * Delete
     */
    public function Delete($request)
    {
        $CustomerCd = $request->CustomerCd;

        // トランザクション開始
        DB::transaction(function() use ($CustomerCd) {

            DB::table('得意先マスタ')->where('得意先ＣＤ', '=', $CustomerCd)->delete();

        });

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
