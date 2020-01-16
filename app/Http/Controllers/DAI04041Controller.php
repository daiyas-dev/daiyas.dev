<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI04041Controller extends Controller
{
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        // トランザクション開始
        DB::transaction(function() use ($params) {
            $CustomerCd = $params['得意先ＣＤ'];

            // DB::table('得意先マスタ')->updateOrInsert(
            //     ['得意先ＣＤ' => $CustomerCd],
            //     $params
            // );

            $cnt = DB::table('得意先マスタ')->where('得意先ＣＤ', $CustomerCd) ->count();
            if ($cnt == 0) {
                DB::table('得意先マスタ')->insert($params);
            } else {
                DB::table('得意先マスタ')->where('得意先ＣＤ', $CustomerCd)->update($params);
            }

        });

        return response()->json([
            'result' => true,
        ]);
    }

}
