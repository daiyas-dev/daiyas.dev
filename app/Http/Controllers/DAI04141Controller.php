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

        $savedData = array_merge(['税区分' => $params['税区分']], $data);

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
        $TaxCd = $request->TaxCd;

        // トランザクション開始
        DB::transaction(function() use ($TaxCd) {

            DB::table('消費税率マスタ')->where('税区分', '=', $TaxCd)->delete();

        });

        return response()->json([
            'result' => true,
        ]);
    }

    /**
     * GetTaxListForDetail
     */
    public function GetTaxListForDetail($request)
    {
        $TaxCd = $request->TaxCd;
        $query = 消費税率マスタ::query()
            ->when(
                $TaxCd,
                function($q) use ($TaxCd){
                    return $q->where('税区分', $TaxCd);
                }
            );

        $TaxList = $query->get();

        return response()->json($TaxList);
    }

}
