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
            $TantoCd = $params['担当者ＣＤ'];

            DB::table('担当者マスタ')->updateOrInsert(
                ['担当者ＣＤ' => $TantoCd],
                $data
            );
        });

        $savedData = array_merge(['担当者ＣＤ' => $params['担当者ＣＤ']], $data);

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
        $TantoCd = $request->TantoCd;

        // トランザクション開始
        DB::transaction(function() use ($TantoCd) {

            DB::table('担当者マスタ')->where('担当者ＣＤ', '=', $TantoCd)->delete();

        });


        return response()->json([
            'result' => true,
        ]);
    }

}
