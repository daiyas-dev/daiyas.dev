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

        return response()->json([
            'result' => true,
        ]);
    }

}
