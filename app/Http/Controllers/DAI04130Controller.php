<?php

namespace App\Http\Controllers;

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

        try {
            DB::transaction(function () use ($params) {

                $saveList = $params['SaveList'];

                $AddList = $saveList['AddList'];
                $UpdateList = $saveList['UpdateList'];
                $OldList = $saveList['OldList'];

                //UpdateList
                foreach ($OldList as $index => $rec) {
                    $data = $UpdateList[$index];

                    各種テーブル::query()
                    ->where('各種CD', $rec['各種CD'])
                    ->where('行NO', $rec['行NO'])
                    ->update($data);
                }

                //AddList
                foreach ($AddList as $rec) {
                    $data = $rec;
                    $cnt = DB::table('各種テーブル')
                        ->where('各種CD', $rec['各種CD'])
                        ->where('行NO', $data['行NO'])->count();

                    if ($cnt == 0){
                        各種テーブル::insert($data);
                    }else{
                        各種テーブル::query()
                        ->where('各種CD', $rec['各種CD'])
                        ->where('行NO', $rec['行NO'])
                        ->update($data);
                    }
                }

            });

        } catch (Exception $exception) {
            DB::rollBack();
            $errMs = $exception->getCode();

            return response()->json([
                "result" => false,
                'errMs' => $errMs,
            ]);
            }

        // // トランザクション開始
        // DB::transaction(function () use ($params) {

        //     $saveList = $params['SaveList'];

        //     $AddList = $saveList['AddList'];
        //     $UpdateList = $saveList['UpdateList'];
        //     $OldList = $saveList['OldList'];

        //     //UpdateList
        //     foreach ($OldList as $index => $rec) {
        //         $data = $UpdateList[$index];

        //         各種テーブル::query()
        //         ->where('各種CD', $rec['各種CD'])
        //         ->where('行NO', $rec['行NO'])
        //         ->update($data);
        //     }

        //     //AddList
        //     foreach ($AddList as $rec) {
        //         $data = $rec;
        //         $cnt = DB::table('各種テーブル')
        //             ->where('各種CD', $rec['各種CD'])
        //             ->where('行NO', $data['行NO'])->count();

        //         if ($cnt == 0){
        //             各種テーブル::insert($data);
        //         }else{
        //             各種テーブル::query()
        //             ->where('各種CD', $rec['各種CD'])
        //             ->where('行NO', $rec['行NO'])
        //             ->update($data);
        //         }
        //     }

        // });

        $query = 各種テーブル::query();
        $savedData = $query->get();

        return response()->json([
            "result" => true,
            'model' => $savedData,
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

            // //確認用：削除予定
            // $query = 各種テーブル::query()
            //     ->when(
            //         $KakusyuCd,
            //         function($q) use ($KakusyuCd){
            //             return $q->where('各種CD', $KakusyuCd);
            //         }
            //     )
            //     ->when(
            //         $GyoNo,
            //         function($q) use ($GyoNo){
            //             return $q->where('行NO', $GyoNo);
            //         }
            //     );
            // $KakusyuList = $query->get();
            // throw new \Exception('hogehoge');

        });

        return response()->json([
            "result" => true,
        ]);
    }
}
