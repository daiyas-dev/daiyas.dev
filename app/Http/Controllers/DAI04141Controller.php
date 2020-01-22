<?php

namespace App\Http\Controllers;

use App\Models\消費税率マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI04141Controller extends Controller
{
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $model = new 消費税率マスタ();
        $model->fill($params);

        $data = collect($model)->all();

        // トランザクション開始
        DB::transaction(function() use ($params, $data) {
            $TaxCd = $params['税区分'];

            DB::table('消費税率マスタ')->updateOrInsert(
                ['税区分' => $TaxCd],
                $data
            );
        });

        return response()->json([
            'result' => true,
        ]);
    }

}
