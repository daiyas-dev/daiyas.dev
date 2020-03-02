<?php

namespace App\Http\Controllers;

use App\Models\金融機関支店名称;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI04201Controller extends Controller
{
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();
        $BankCd = $params['銀行CD'];
        $BankBranchCd = $params['支店CD'];

        $model = new 金融機関支店名称();
        $model->fill($params);

        $data = collect($model)->all();
        $newData = array_merge(['銀行CD' => $BankCd], ['支店CD' => $BankBranchCd], $data);

        // トランザクション開始
        DB::transaction(function() use ($BankCd, $BankBranchCd, $newData) {

            $cnt = DB::table('金融機関支店名称')
                ->where('銀行CD', $BankCd)
                ->where('支店CD', $BankBranchCd)
                ->count();

            if ($cnt == 0) {
                DB::table('金融機関支店名称')->insert($newData);
            } else {
                DB::table('金融機関支店名称')
                    ->where('銀行CD', $BankCd)
                    ->where('支店CD', $BankBranchCd)
                    ->update($newData);
            }
        });

        return response()->json([
            'result' => true,
            'model' => $data,
        ]);
    }

    /**
     * GetBankBranchListForDetail
     */
    public function GetBankBranchListForDetail($request)
    {
        $BankCd = $request->BankCd;
        $BankBranchCd = $request->BankBranchCd;

        $query = 金融機関支店名称::query()
            ->when(
                $BankCd,
                function($q) use ($BankCd){
                    return $q->where('銀行CD', $BankCd);
                }
            )
            ->when(
                $BankBranchCd,
                function($q) use ($BankBranchCd){
                    return $q->where('支店CD', $BankBranchCd);
                }
            );

        $BankBranchList = $query->get();

        return response()->json($BankBranchList);
    }

    /**
     * Delete
     */
    public function Delete($request)
    {
        $BankCd = $request->BankCd;
        $BankBranchCd = $request->BankBranchCd;

        // トランザクション開始
        DB::transaction(function() use ($BankCd, $BankBranchCd) {

            DB::table('金融機関支店名称')
                ->where('銀行CD', '=', $BankCd)
                ->where('支店CD', '=', $BankBranchCd)
                ->delete();

        });

        return response()->json([
            'result' => true,
        ]);
    }

}
