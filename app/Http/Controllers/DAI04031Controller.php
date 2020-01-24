<?php

namespace App\Http\Controllers;

use App\Models\商品マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI04031Controller extends Controller
{
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $model = new 商品マスタ();
        $model->fill($params);

        $data = collect($model)->all();

        // トランザクション開始
        DB::transaction(function() use ($params, $data) {
            $ProductCd = $params['商品ＣＤ'];

            DB::table('商品マスタ')->updateOrInsert(
                ['商品ＣＤ' => $ProductCd],
                $data
            );
        });

        $savedData = array_merge(['商品ＣＤ' => $params['商品ＣＤ']], $data);

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
        $ProductCd = $request->ProductCd;

        // トランザクション開始
        DB::transaction(function() use ($ProductCd) {

            DB::table('商品マスタ')->where('商品ＣＤ', '=', $ProductCd)->delete();

        });

        return response()->json([
            'result' => true,
        ]);
    }
}
