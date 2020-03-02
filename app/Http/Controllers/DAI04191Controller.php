<?php

namespace App\Http\Controllers;

use App\Models\金融機関名称;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI04191Controller extends Controller
{
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();
        $BankCd = $params['銀行CD'];

        $model = new 金融機関名称();
        $model->fill($params);

        $data = collect($model)->all();
        $newData = array_merge(['銀行CD' => $BankCd], $data);

        // トランザクション開始
        DB::transaction(function() use ($BankCd, $newData) {

            $cnt = DB::table('金融機関名称')->where('銀行CD', $BankCd) ->count();
            if ($cnt == 0) {
                DB::table('金融機関名称')->insert($newData);
            } else {
                DB::table('金融機関名称')->where('銀行CD', $BankCd)->update($newData);
            }

        });

        return response()->json([
            'result' => true,
            'model' => $data,
        ]);
    }

    /**
     * GetBankListForDetail
     */
    public function GetBankListForDetail($request)
    {
        $BankCd = $request->BankCd;

        $query = 金融機関名称::query()
            ->when(
                $BankCd,
                function($q) use ($BankCd){
                    return $q->where('銀行CD', $BankCd);
                }
            );

        $BankList = $query->get();

        return response()->json($BankList);
    }

    /**
     * Delete
     */
    public function Delete($request)
    {
        $BankCd = $request->BankCd;

        // トランザクション開始
        DB::transaction(function() use ($BankCd) {

            DB::table('金融機関名称')->where('銀行CD', '=', $BankCd)->delete();

        });

        return response()->json([
            'result' => true,
        ]);
    }

}
