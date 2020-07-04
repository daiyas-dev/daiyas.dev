<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use App\Models\モバイル対象商品;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use PDO;

class DAI04120Controller extends Controller
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

    /**
     * GetMobileProductList
     */
    public function GetMobileProductList($request)
    {
        $sql = "
            SELECT
                T1.部署ＣＤ
                ,T1.商品ＣＤ
                ,T2.商品区分
                ,T2.商品名
                ,T1.主要商品FLG
                ,T1.期間限定FLG
                ,T1.販売期間開始
                ,T1.販売期間終了
                ,T1.修正日
            FROM
                モバイル_対象商品 T1
                LEFT OUTER JOIN 商品マスタ T2 ON
                    T1.商品ＣＤ = T2.商品ＣＤ
            ORDER BY
                T2.商品区分
                ,T2.商品ＣＤ
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        return response()->json($DataList);
    }

    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        DB::beginTransaction();
        try {
            $BushoCd = $request->BushoCd;

            $saveList = $params['SaveList'];

            $AddList = $saveList['AddList'];
            $UpdateList = $saveList['UpdateList'];
            $OldList = $saveList['OldList'];
            $DeleteList = $saveList['DeleteList'];

            //DeleteList
            foreach($DeleteList as $rec) {
                モバイル対象商品::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->delete();
            }

            //UpdateList
            foreach ($OldList as $index => $rec) {
                $data = $UpdateList[$index];

                モバイル対象商品::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->update($data);
            }

            //AddList
            foreach ($AddList as $rec) {
                $data = $rec;
                $cnt = モバイル対象商品::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('商品ＣＤ', $data['商品ＣＤ'])
                    ->count();

                if ($cnt == 0){
                    モバイル対象商品::insert($data);
                }else{
                    モバイル対象商品::query()
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('商品ＣＤ', $rec['商品ＣＤ'])
                        ->update($data);
                }
            }

            DB::commit();

            //モバイルsv更新
            $Message = $params['Message'];
            $ds = new DataSendWrapper();
            $ds->UpdateTargetProductData($BushoCd, $Message);

        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            "result" => true,
        ]);
    }

    /**
     * Delete
     */
    public function Delete($request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();

            モバイル対象商品::query()
                ->where('部署ＣＤ', $params['部署ＣＤ'])
                ->where('商品ＣＤ', $params['商品ＣＤ'])
                ->delete();

            DB::commit();

            //モバイルSvを更新
            $Message = $params['Message'];
            $ds = new DataSendWrapper();
            $ds->Delete('モバイル_対象商品', $params, true, $params['部署ＣＤ'], null, null, $Message);
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            "result" => true,
        ]);
    }
}
