<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use App\Models\各種テーブル;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;

class DAI04130Controller extends Controller
{
    /**
     * GetKakusyuListForDetail
     */
    public function GetKakusyuListForDetail($request)
    {
        $KakusyuCd = $request->KakusyuCd;
        $GyoNo = $request->GyoNo;

        $query = 各種テーブル::query()
            ->when(
                $KakusyuCd,
                function($q) use ($KakusyuCd){
                    return $q->where('各種CD', $KakusyuCd);
                }
            )
            ->when(
                    $GyoNo,
                    function($q) use ($GyoNo){
                        return $q->where('行NO', $GyoNo);
                    }
            );

        $KakusyuList = $query->get();

        return response()->json($KakusyuList);
    }

    // /**
    //  * Save
    //  */
    public function Save($request)
    {
        $params = $request->all();
        $duplicateList = [];

        //モバイルsv更新用
        $MUpdateList = [];
        $MInsertList = [];

        try {
            DB::beginTransaction();

            $saveList = $params['SaveList'];

            $AddList = $saveList['AddList'];
            $UpdateList = $saveList['UpdateList'];
            $OldList = $saveList['OldList'];

            //UpdateList
            foreach ($OldList as $index => $rec) {
                $data = $UpdateList[$index];

                try{
                    各種テーブル::query()
                    ->where('各種CD', $rec['各種CD'])
                    ->where('行NO', $rec['行NO'])
                    ->update($data);

                    //モバイルsv更新用
                    array_push($MUpdateList, $data);


                } catch (Exception $exception) {
                    $errMs = $exception->getCode();

                    //主キー重複
                    if($errMs == "23000"){
                        $duplicateList = collect($duplicateList) ->push([$data]);
                    } else {
                        throw $exception;
                    }
                }
            }

            //AddList
            foreach ($AddList as $rec) {
                $data = $rec;
                $cnt = DB::table('各種テーブル')
                    ->where('各種CD', $rec['各種CD'])
                    ->where('行NO', $data['行NO'])->count();

                if ($cnt == 0){
                    各種テーブル::insert($data);

                    //モバイルsv更新用
                    array_push($MInsertList, $data);
                }else{
                    //主キー重複
                    $duplicateList = collect($duplicateList) ->push([$data]);
                }
            }

            DB::commit();

            //モバイルsv更新
            //TODO: 全更新完了後に通知されるよう対応
            $Message = $params['Message'];
            $ds = new DataSendWrapper();
            $ds->UpdateVariousData($Message);

        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            "result" => true,
            'duplicateList' => $duplicateList,
        ]);
    }

    /**
     * DeleteKakusyuList
     */
    public function DeleteKakusyuList($request)
    {
        // トランザクション開始
        DB::transaction(function () use ($request) {
            $params = $request->all();

            $KakusyuCd = $params['KakusyuCd'];
            $GyoNo = $params['GyoNo'];

            各種テーブル::query()
            ->where('各種CD', $KakusyuCd)
            ->where('行NO', $GyoNo)
            ->delete();
        });

        $params = $request->all();
        $model = new 各種テーブル();
        $model->fill($params);
        $data = collect($model)->all();
        $newData = array_merge(['各種CD' => $params['KakusyuCd'], '行NO' => $params['GyoNo']], $data);
        //モバイルSvを更新
        $Message = $params['Message'];
        $ds = new DataSendWrapper();
        $ds->Delete('各種テーブル',$newData,true,null,null,null, $Message);

        return response()->json([
            "result" => true,
        ]);
    }
}
