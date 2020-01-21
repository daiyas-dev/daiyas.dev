<?php

namespace App\Http\Controllers;

use App\Models\金融機関名称;
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

        $model = new 金融機関名称();
        $model->fill($params);

        $data = collect($model)->all();

        // トランザクション開始
        DB::transaction(function() use ($params, $data) {
            $BankCd = $params['銀行CD'];

            DB::table('金融機関名称')->updateOrInsert(
                ['銀行CD' => $BankCd],
                $data
            );
        });

        return response()->json([
            'result' => true,
        ]);
    }

}
