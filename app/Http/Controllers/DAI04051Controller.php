<?php

namespace App\Http\Controllers;

use App\Models\得意先単価マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI04051Controller extends Controller
{
    /**
     * GetProductList
     */
    public function GetProductList($request)
    {
        $sql = "
            SELECT
                PM.商品ＣＤ,
                PM.商品名,
                PM.商品区分,
                PM.売価単価
            FROM
                商品マスタ PM
            WHERE
                表示ＦＬＧ=0 AND 弁当区分 IN (0, 8, 9)
            ORDER BY 商品ＣＤ

        ";

        $Result = collect(DB::select($sql))
            ->map(function ($product) {
                $vm = (object) $product;

                $vm->Cd = $product->商品ＣＤ;
                $vm->CdNm = $product->商品名;

                return $vm;
            })
            ->values();

        return response()->json($Result);
    }

    //TODO:以下コピーのまま　修正要
    /**
     * Save
     */
    public function Save($request)
    {
        $skip = [];

        // トランザクション開始
        $skip = DB::transaction(function () use ($request, $skip) {
            $params = $request->all();

            $BushoCd = $request->BushoCd;
            $TargetDate = $request->TargetDate;
            $CourseCd = $request->CourseCd;

            $AddList = $params['AddList'];
            $UpdateList = $params['UpdateList'];
            $OldList = $params['OldList'];
            $DeleteList = $params['DeleteList'];

            $date = Carbon::now()->format('Y-m-d H:i:s');

            //DeleteList
            foreach($DeleteList as $rec) {
                $r = モバイル移動入力::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('コースＣＤ', $rec['コースＣＤ'])
                    ->where('相手コースＣＤ', $rec['相手コースＣＤ'])
                    ->where('日付', $rec['日付'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->get();

                if (count($r) != 1) {
                    $skip = collect($skip)->push(["target" => $rec, "current" => null]);
                    continue;
                } else if ($rec['修正日'] != $r[0]->修正日) {
                    $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                    continue;
                }

                モバイル移動入力::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('コースＣＤ', $rec['コースＣＤ'])
                    ->where('相手コースＣＤ', $rec['相手コースＣＤ'])
                    ->where('日付', $rec['日付'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->delete();
            }

            //UpdateList
            foreach ($OldList as $index => $rec) {
                $r = モバイル移動入力::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('コースＣＤ', $rec['コースＣＤ'])
                    ->where('相手コースＣＤ', $rec['相手コースＣＤ'])
                    ->where('日付', $rec['日付'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->get();

                if (count($r) != 1) {
                    $skip = collect($skip)->push(["target" => $rec, "current" => null]);
                    continue;
                } else if ($rec['修正日'] != $r[0]->修正日) {
                    $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                    continue;
                }

                $data = $UpdateList[$index];
                $data['修正日'] = $date;

                モバイル移動入力::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('コースＣＤ', $rec['コースＣＤ'])
                    ->where('相手コースＣＤ', $rec['相手コースＣＤ'])
                    ->where('日付', $rec['日付'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->update($data);
            }

            //AddList
            foreach ($AddList as $rec) {
                $r = モバイル移動入力::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('コースＣＤ', $rec['コースＣＤ'])
                    ->where('相手コースＣＤ', $rec['相手コースＣＤ'])
                    ->where('日付', $rec['日付'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->get();

                if (count($r) != 0) {
                    $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                    continue;
                }

                $seq = モバイル移動入力::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('コースＣＤ', $rec['コースＣＤ'])
                    ->where('日付', $rec['日付'])
                    ->max('ＳＥＱ') + 1;

                $data = $rec;
                $data['修正日'] = $date;
                $data['ＳＥＱ'] = $seq;

                モバイル移動入力::insert($data);
            }

            //TODO:確認要　削除予定
            throw new \Exception('hogehoge');

            return $skip;
        });

        $send = null;
        $receive = null;
        if (!!count($skip)) {
            $send = $this->SearchSendList($request);
            $receive = $this->SearchReceiveList($request);
        }

        return response()->json([
            "result" => true,
            "skip" => !!count($skip),
            "send" => $send,
            "receive" => $receive
        ]);
    }
}
