<?php

namespace App\Http\Controllers;

use App\Models\担当者マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI04021Controller extends Controller
{
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $model = new 担当者マスタ();
        $model->fill($params);

        $data = collect($model)->all();

        // トランザクション開始
        DB::transaction(function() use ($params, $data) {
            $CustomerCd = $params['担当者ＣＤ'];

            DB::table('担当者マスタ')->updateOrInsert(
                ['担当者ＣＤ' => $CustomerCd],
                $data
            );
        });

        return response()->json([
            'result' => true,
        ]);
    }

}
