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

        return response()->json([
            'result' => true,
        ]);
    }

}
