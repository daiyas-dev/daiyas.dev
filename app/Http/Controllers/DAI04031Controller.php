<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use App\Models\商品マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;

class DAI04031Controller extends Controller
{
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $model = new 商品マスタ();
        $model->fill($params);

        $ProductCd = $params['商品ＣＤ'];
        $data = collect($model)->all();
        $newData = array_merge(['商品ＣＤ' => $params['商品ＣＤ']], $data);

        $isNew = $params['IsNew'];
        $duplicate = "";

        // トランザクション開始
        try {
            DB::beginTransaction();
            if($isNew){
                //新規
                try {
                    DB::table('商品マスタ')->insert($newData);
                } catch (Exception $exception) {
                    $errMs = $exception->getCode();

                    //主キー重複
                    if($errMs == "23000"){
                        $duplicate = $ProductCd;
                    } else {
                        throw $exception;
                    }
                }
            }else{
                //修正
                DB::table('商品マスタ')
                ->where('商品ＣＤ', $ProductCd)
                ->update($newData);
            }
            DB::commit();

            // モバイルSvを更新
            $Message = $params['Message'];
            $ds = new DataSendWrapper();
            if ($isNew) {
                $ds->Insert('商品マスタ',$newData,true,null,null,null, $Message);
            }else{
                $ds->Update('商品マスタ',$newData,true,null,null,null, $Message);
            }

        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            'model' => $newData,
            'duplicate' => $duplicate,
        ]);
    }

    /**
     * Delete
     */
    public function Delete($request)
    {
        $ProductCd = $request->ProductCd;

        // トランザクション開始
        DB::transaction(function() use ($ProductCd) {

            DB::table('商品マスタ')->where('商品ＣＤ', '=', $ProductCd)->delete();

        });

        $params = $request->all();
        $model = new 商品マスタ();
        $model->fill($params);
        $data = collect($model)->all();
        $newData = array_merge(['商品ＣＤ' => $ProductCd], $data);
        //モバイルSvを更新
        $Message = $params['Message'];
        $ds = new DataSendWrapper();
        $ds->Delete('商品マスタ',$newData,true,null,null,null, $Message);

        return response()->json([
            'result' => true,
        ]);
    }
}
