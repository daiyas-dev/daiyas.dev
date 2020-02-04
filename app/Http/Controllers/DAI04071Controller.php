<?php

namespace App\Http\Controllers;

use App\Models\部署マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI04071Controller extends Controller
{
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $model = new 部署マスタ();
        $model->fill($params);

        $data = collect($model)->all();

        // トランザクション開始
        DB::transaction(function() use ($params, $data) {
            $BushoCd = $params['部署CD'];

            DB::table('部署マスタ')->updateOrInsert(
                ['部署CD' => $BushoCd],
                $data
            );
        });

        $savedData = array_merge(['部署CD' => $params['部署CD']], $data);

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
        $BushoCd = $request->BushoCd;

        // トランザクション開始
        DB::transaction(function() use ($BushoCd) {

            DB::table('部署マスタ')->where('部署CD', '=', $BushoCd)->delete();

        });

        return response()->json([
            'result' => true,
        ]);
    }

    /**
     * GetBushoListForDetail
     */
    public function GetBushoListForDetail($request)
    {
        $BushoCd = $request->BushoCd;
        $query = 部署マスタ::query()
            ->when(
                $BushoCd,
                function($q) use ($BushoCd){
                    return $q->where('部署CD', $BushoCd);
                }
            );

        $BushoList = $query->get();

        return response()->json($BushoList);
    }
}