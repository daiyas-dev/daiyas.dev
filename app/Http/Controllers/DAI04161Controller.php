<?php

namespace App\Http\Controllers;

use App\Models\祝日マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI04161Controller extends Controller
{
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $model = new 祝日マスタ();
        $model->fill($params);

        $data = collect($model)->all();

        // トランザクション開始
        DB::transaction(function() use ($params, $data) {
            $TargetDate = $params['対象日付'];

            DB::table('祝日マスタ')->updateOrInsert(
                ['対象日付' => $TargetDate],
                $data
            );
        });

        $savedData = array_merge(['対象日付' => $params['対象日付']], $data);

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
        $TargetDate = $request->TargetDate;

        // トランザクション開始
        DB::transaction(function() use ($TargetDate) {

            DB::table('祝日マスタ')->where('対象日付', '=', $TargetDate)->delete();

        });

        return response()->json([
            'result' => true,
        ]);
    }
}
