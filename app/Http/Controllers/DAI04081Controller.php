<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use App\Models\コースマスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;

class DAI04081Controller extends Controller
{
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $BushoCd = $params['部署ＣＤ'];
        $CourseCd = $params['コースＣＤ'];

        $model = new コースマスタ();
        $model->fill($params);

        $data = collect($model)->all();
        $newData = array_merge(['部署ＣＤ' => $BushoCd, 'コースＣＤ' => $CourseCd], $data);

        $isNew = $params['IsNew'];
        $duplicate = "";

        // トランザクション開始
        try {
            DB::beginTransaction();
            if($isNew){
                //新規
                try {
                    DB::table('コースマスタ')->insert($newData);
                } catch (Exception $exception) {
                    $errMs = $exception->getCode();

                    //主キー重複
                    if($errMs == "23000"){
                        $duplicate = $CourseCd;
                    } else {
                        throw $exception;
                    }
                }
            }else{
                //修正
                DB::table('コースマスタ')
                ->where('部署ＣＤ', $BushoCd)
                ->where('コースＣＤ', $CourseCd)
                ->update($newData);
            }
            DB::commit();

            //モバイルSvを更新
            $Message = $params['Message'];
            $ds = new DataSendWrapper();
            if ($isNew) {
                $ds->Insert('コースマスタ',$newData,true,$BushoCd,null,$CourseCd, $Message);
            }else{
                $ds->Update('コースマスタ',$newData,true,$BushoCd,null,$CourseCd, $Message);
            }
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            'duplicate' => $duplicate,
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

        $params = $request->all();
        $model = new コースマスタ();
        $model->fill($params);
        $data = collect($model)->all();
        $newData = array_merge(['部署ＣＤ' => $BushoCd, 'コースＣＤ' => $CourseCd], $data);
        //モバイルSvを更新
        $Message = $params['Message'];
        $ds = new DataSendWrapper();
        $ds->Delete('コースマスタ',$newData,true,$BushoCd,null,$CourseCd, $Message);

        return response()->json([
            'result' => true,
        ]);
    }

}
