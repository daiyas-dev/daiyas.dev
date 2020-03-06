<?php

namespace App\Http\Controllers;

use App\Models\コースマスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI04081Controller extends Controller
{
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $model = new コースマスタ();
        $model->fill($params);

        $data = collect($model)->all();

        // トランザクション開始
        DB::transaction(function() use ($params, $data) {
            $BushoCd = $params['部署ＣＤ'];
            $CourseCd = $params['コースＣＤ'];

            DB::table('コースマスタ')->updateOrInsert(
                ['部署ＣＤ' => $BushoCd, 'コースＣＤ' => $CourseCd],
                $data
            );
        });

        return response()->json([
            'result' => true,
        ]);
    }

    /**
     * Delete
     */
    public function Delete($request)
    {
        $BushoCd = $request->BushoCd;
        $CourseCd = $request->CourseCd;

        // トランザクション開始
        DB::transaction(function() use ($BushoCd, $CourseCd) {

            DB::table('コースマスタ')
            ->where('部署ＣＤ', '=', $BushoCd)
            ->where('コースＣＤ', '=', $CourseCd)
            ->delete();

        });

        return response()->json([
            'result' => true,
        ]);
    }

}
