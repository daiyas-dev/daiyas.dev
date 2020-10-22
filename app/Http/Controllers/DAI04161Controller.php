<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use App\Models\祝日マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;

class DAI04161Controller extends Controller
{
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $errFlg = False;
        $model = new 祝日マスタ();
        $model->fill($params);

        $TargetDate = $params['対象日付'];
        $data = collect($model)->all();

        $newData = array_merge(['対象日付' => $params['対象日付']], $data);
        $isNew = $params['IsNew'];
        $duplicate = "";

        // トランザクション開始
        try {
            DB::beginTransaction();
            if($isNew){
                //新規
                try {
                    DB::table('祝日マスタ')->insert($newData);
                } catch (Exception $exception) {
                    $errMs = $exception->getCode();
                    $errFlg = True;

                    //主キー重複
                    if($errMs == "23000"){
                        $duplicate = $TargetDate;
                    } else {
                        throw $exception;
                    }
                }
            }else{
                //修正
                DB::table('祝日マスタ')
                ->where('対象日付', $TargetDate)
                ->update($newData);
            }
            DB::commit();

            // モバイルSvを更新
            $Message = $params['Message'];
            $ds = new DataSendWrapper();
            if($errFlg == false){
                if ($isNew) {
                    $del_sql="delete from HolidayMaster where target_date = '$TargetDate'";
                    $ds->Insert('祝日マスタ',$newData,true,null,null,null, $Message,$del_sql);
                }else{
                    $ds->Update('祝日マスタ',$newData,true,null,null,null, $Message);
                }
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
        $TargetDate = $request->TargetDate;

        // トランザクション開始
        DB::transaction(function() use ($TargetDate) {

            DB::table('祝日マスタ')->where('対象日付', '=', $TargetDate)->delete();

        });

        $params = $request->all();
        $model = new 祝日マスタ();
        $model->fill($params);
        $data = collect($model)->all();
        $newData = array_merge(['対象日付' => $TargetDate], $data);
        //モバイルSvを更新
        $Message = $params['Message'];
        $ds = new DataSendWrapper();
        $ds->Delete('祝日マスタ',$newData,true,null,null,null, $Message);

        return response()->json([
            'result' => true,
        ]);
    }
}
