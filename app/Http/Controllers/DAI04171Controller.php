<?php

namespace App\Http\Controllers;

use App\Models\製造品マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;

class DAI04171Controller extends Controller
{
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $model = new 製造品マスタ();
        $model->fill($params);

        $data = collect($model)->all();

        DB::beginTransaction();
        try {
            製造品マスタ::query()->updateOrInsert(
                ['既定製造パターン' => $params['既定製造パターン'], '商品ＣＤ' => $params['商品ＣＤ']],
                $data
            );

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
        ]);
    }

    /**
     * Delete
     */
    public function Delete($request)
    {
        $params = $request->all();

        DB::beginTransaction();
        try {
            製造品マスタ::query()
                ->where('既定製造パターン', $params['既定製造パターン'])
                ->where('商品ＣＤ', $params['商品ＣＤ'])
                ->delete();

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
        ]);
    }
}
