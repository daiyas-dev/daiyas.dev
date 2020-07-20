<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use App\Models\担当者マスタ;
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

        $model = new 担当者マスタ();
        $model->fill($params);

        $data = collect($model)->all();

        $isNew = $params['IsNew'];
        $TantoCd = $params['担当者ＣＤ'];

        $savedData = [];

        try {
            DB::beginTransaction();


            DB::table('担当者マスタ')->updateOrInsert(
                ['担当者ＣＤ' => $TantoCd],
                $data
            );

            DB::commit();

            $savedData = array_merge(['担当者ＣＤ' => $params['担当者ＣＤ']], $data);

            // モバイルSvを更新
            $Message = $params['Message'];
            $ds = new DataSendWrapper();
            if ($isNew) {
                $ds->Insert('担当者マスタ', $savedData, true, null, null, null, $Message);
            } else {
                $ds->Update('担当者マスタ', $savedData, true, null, null, null, $Message);
            }

        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

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
        $TantoCd = $request->TantoCd;

        // トランザクション開始
        DB::transaction(function() use ($TantoCd) {

            DB::table('担当者マスタ')->where('担当者ＣＤ', '=', $TantoCd)->delete();

        });


        return response()->json([
            'result' => true,
        ]);
    }

}
