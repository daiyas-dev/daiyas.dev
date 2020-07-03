<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use App\Models\部署マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;

class DAI04071Controller extends Controller
{
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $model = new 部署マスタ();
        $model->fill($params);

        $BushoCd = $params['部署CD'];
        $data = collect($model)->all();
        $newData = array_merge(['部署CD' => $params['部署CD']], $data);

        $isNew = $params['IsNew'];
        $duplicate = "";

        // トランザクション開始
        try {
            DB::beginTransaction();
            if($isNew){
                //新規
                try {
                    DB::table('部署マスタ')->insert($newData);
                } catch (Exception $exception) {
                    $errMs = $exception->getCode();

                    //主キー重複
                    if($errMs == "23000"){
                        $duplicate = $BushoCd;
                    } else {
                        throw $exception;
                    }
                }
            }else{
                //修正
                DB::table('部署マスタ')
                ->where('部署CD', $BushoCd)
                ->update($newData);
            }
            DB::commit();

            // モバイルSvを更新
            $Message = $params['Message'];
            $ds = new DataSendWrapper();
            if ($isNew) {
                $ds->Insert('部署マスタ',$newData,true,$BushoCd,null,null, $Message);
            }else{
                $ds->Update('部署マスタ',$newData,true,$BushoCd,null,null, $Message);
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
        $BushoCd = $request->BushoCd;

        // トランザクション開始
        DB::transaction(function() use ($BushoCd) {

            DB::table('部署マスタ')->where('部署CD', '=', $BushoCd)->delete();

        });

        $params = $request->all();
        $model = new 部署マスタ();
        $model->fill($params);
        $data = collect($model)->all();
        $newData = array_merge(['部署CD' => $BushoCd], $data);
        //モバイルSvを更新
        $Message = $params['Message'];
        $ds = new DataSendWrapper();
        $ds->Delete('部署マスタ',$newData,true,$BushoCd,null,null, $Message);

        return response()->json([
            'result' => true,
        ]);
    }

    /**
     * GetBushoListForDetail
     */
    public function GetBushoListForDetail($request)
    {
        $BushoCd = $request->BushoCd;
        $query = 部署マスタ::query()
            ->when(
                $BushoCd,
                function($q) use ($BushoCd){
                    return $q->where('部署CD', $BushoCd);
                }
            );

        $BushoList = $query->get();

        return response()->json($BushoList);
    }

    /**
     * UploadImage
     */
    public function UploadImage($request) {
        $BushoCd = $request->BushoCd;
        $FileName = $BushoCd . '_' . Carbon::now()->format('YmdHis') . '.png';

        $this->validate($request, [
            'file' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,png',
            ]
        ]);

        try {
            if ($request->file('file')->isValid([]))
            {
                $SavePath=public_path().'\\images\\BushoStamp\\'.$FileName;
                $spl = new \SplFileInfo($request->file);
                move_uploaded_file($spl->getPathname(), $SavePath);

                return response()->json([
                    'result' => true,
                    "file" => $FileName,
                ]);
            } else {
                return response()->json([
                    'result' => false,
                    "message" => '画像がアップロードされていないか不正なデータです。',
                ]);
            }
        } catch(Exception $ex) {
            return response()->json([
                'result' => false,
                "message" => '保存に失敗しました。',
            ]);
        }

    }
}
