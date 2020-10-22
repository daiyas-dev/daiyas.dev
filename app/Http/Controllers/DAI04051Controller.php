<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use App\Models\得意先単価マスタ新;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
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

    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $CustomerCd = $params['CustomerCd'];

        $DeliveryDate = date('Y-m-d');

        $sql = "
            SELECT
                M1.部署ＣＤ,
                M1.得意先ＣＤ,
                MC.コースＣＤ,
                MC.コース区分
            FROM
                得意先マスタ M1
                LEFT OUTER JOIN 部署マスタ MB
                    ON MB.部署ＣＤ = M1.部署ＣＤ
                LEFT OUTER JOIN 祝日マスタ MH
                    ON  MH.対象日付 = '$DeliveryDate'
                    AND (対象部署ＣＤ IS NULL OR 対象部署ＣＤ LIKE CONCAT('%', MB.部署ＣＤ, '%'))
                LEFT OUTER JOIN (
                    SELECT
                        CT.部署ＣＤ
                        ,CT.コースＣＤ
                        ,CT.管理ＣＤ
                        ,CTC.一時フラグ
                        ,CM.コース区分
                        ,CM.担当者ＣＤ
                        ,CT.得意先ＣＤ
                    FROM
                        (
                            SELECT
                                部署ＣＤ, コースＣＤ, 0 AS 管理ＣＤ, ＳＥＱ, 得意先ＣＤ, 修正担当者ＣＤ, 修正日
                            FROM
                                コーステーブル
                            UNION ALL
                            SELECT
                                部署ＣＤ, コースＣＤ, 管理ＣＤ, ＳＥＱ, 得意先ＣＤ, 修正担当者ＣＤ, 修正日
                            FROM
                                コーステーブル一時
                        ) CT
                            INNER JOIN (
                                SELECT
                                    *
                                FROM (
                                    SELECT
                                        部署ＣＤ
                                        ,コースＣＤ
                                        ,一時フラグ
                                        ,RANK() OVER(PARTITION BY 部署ＣＤ, コースＣＤ ORDER BY 一時フラグ DESC) AS RNK
                                    FROM
                                        コーステーブル管理
                                    WHERE
                                        適用開始日 <= '$DeliveryDate' AND 適用終了日 >= '$DeliveryDate'
                                ) X
                                WHERE
                                    RNK = 1
                            ) CTC
                                ON  CTC.部署ＣＤ=CT.部署ＣＤ
                                AND CTC.コースＣＤ=CT.コースＣＤ
                        LEFT JOIN コースマスタ CM
                            ON  CM.部署ＣＤ = CTC.部署ＣＤ
                            AND CM.コースＣＤ = CTC.コースＣＤ
                ) MC
                    ON  MC.部署ＣＤ = M1.部署CD
                    AND MC.得意先ＣＤ = M1.得意先ＣＤ
                    AND MC.コース区分 = IIF(MH.対象日付 IS NOT NULL, 4, CASE DATEPART(WEEKDAY, '$DeliveryDate') WHEN 1 THEN 3 WHEN 7 THEN 2 ELSE 1 END)
                LEFT OUTER JOIN 担当者マスタ MT
                    ON MT.担当者ＣＤ = MC.担当者ＣＤ
            WHERE
                M1.得意先CD = '$CustomerCd'
            AND (M1.受注得意先ＣＤ = 0 OR M1.受注得意先ＣＤ = M1.得意先ＣＤ)
            ";

        $Data = DB::selectOne($sql);

        $CourseCD = $Data->コースＣＤ ?? null;
        $BushoCD = $Data->部署ＣＤ ?? null;

        DB::beginTransaction();
        try {
            $saveList = $params['SaveList'];

            $AddList = $saveList['AddList'];
            $UpdateList = $saveList['UpdateList'];
            $OldList = $saveList['OldList'];
            $DeleteList = $saveList['DeleteList'];

            //DeleteList
            foreach($DeleteList as $rec) {
                得意先単価マスタ新::query()
                    ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->where('適用開始日', $rec['適用開始日'])
                    ->delete();
            }

            //UpdateList
            foreach ($OldList as $index => $rec) {
                $data = $UpdateList[$index];

                得意先単価マスタ新::query()
                    ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->where('適用開始日', $rec['適用開始日'])
                    ->update($data);
            }

            //AddList
            foreach ($AddList as $rec) {
                $data = $rec;
                $cnt = DB::table('得意先単価マスタ新')
                    ->where('得意先ＣＤ', $CustomerCd)
                    ->where('商品ＣＤ', $data['商品ＣＤ'])
                    ->where('適用開始日', $rec['適用開始日'])
                    ->count();

                if ($cnt == 0){
                    得意先単価マスタ新::insert($data);
                }else{
                    得意先単価マスタ新::query()
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->where('商品ＣＤ', $rec['商品ＣＤ'])
                        ->where('適用開始日', $rec['適用開始日'])
                        ->update($data);
                }
            }

            DB::commit();

            //モバイルsv更新
            $Message = $params['Message'];
            $Message['department_code'] = $BushoCD;
            $Message['course_code'] = $CourseCD;
            $ds = new DataSendWrapper();
            $ds->UpdateCustomerPricemasterNew($CustomerCd,$Message,$CourseCD);

        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        $Utilities = new UtilitiesController();

        return response()->json([
            "result" => true,
            'model' => $Utilities->SearchTankaList($request),
        ]);
    }

    /**
     * DeleteTankaList
     */
    public function DeleteTankaList($request)
    {
        $params = $request->all();

        $CustomerCd = $params['得意先ＣＤ'];

        $DeliveryDate = date('Y-m-d');

        $sql = "
            SELECT
                M1.部署ＣＤ,
                M1.得意先ＣＤ,
                MC.コースＣＤ,
                MC.コース区分
            FROM
                得意先マスタ M1
                LEFT OUTER JOIN 部署マスタ MB
                    ON MB.部署ＣＤ = M1.部署ＣＤ
                LEFT OUTER JOIN 祝日マスタ MH
                    ON  MH.対象日付 = '$DeliveryDate'
                    AND (対象部署ＣＤ IS NULL OR 対象部署ＣＤ LIKE CONCAT('%', MB.部署ＣＤ, '%'))
                LEFT OUTER JOIN (
                    SELECT
                        CT.部署ＣＤ
                        ,CT.コースＣＤ
                        ,CT.管理ＣＤ
                        ,CTC.一時フラグ
                        ,CM.コース区分
                        ,CM.担当者ＣＤ
                        ,CT.得意先ＣＤ
                    FROM
                        (
                            SELECT
                                部署ＣＤ, コースＣＤ, 0 AS 管理ＣＤ, ＳＥＱ, 得意先ＣＤ, 修正担当者ＣＤ, 修正日
                            FROM
                                コーステーブル
                            UNION ALL
                            SELECT
                                部署ＣＤ, コースＣＤ, 管理ＣＤ, ＳＥＱ, 得意先ＣＤ, 修正担当者ＣＤ, 修正日
                            FROM
                                コーステーブル一時
                        ) CT
                            INNER JOIN (
                                SELECT
                                    *
                                FROM (
                                    SELECT
                                        部署ＣＤ
                                        ,コースＣＤ
                                        ,一時フラグ
                                        ,RANK() OVER(PARTITION BY 部署ＣＤ, コースＣＤ ORDER BY 一時フラグ DESC) AS RNK
                                    FROM
                                        コーステーブル管理
                                    WHERE
                                        適用開始日 <= '$DeliveryDate' AND 適用終了日 >= '$DeliveryDate'
                                ) X
                                WHERE
                                    RNK = 1
                            ) CTC
                                ON  CTC.部署ＣＤ=CT.部署ＣＤ
                                AND CTC.コースＣＤ=CT.コースＣＤ
                        LEFT JOIN コースマスタ CM
                            ON  CM.部署ＣＤ = CTC.部署ＣＤ
                            AND CM.コースＣＤ = CTC.コースＣＤ
                ) MC
                    ON  MC.部署ＣＤ = M1.部署CD
                    AND MC.得意先ＣＤ = M1.得意先ＣＤ
                    AND MC.コース区分 = IIF(MH.対象日付 IS NOT NULL, 4, CASE DATEPART(WEEKDAY, '$DeliveryDate') WHEN 1 THEN 3 WHEN 7 THEN 2 ELSE 1 END)
                LEFT OUTER JOIN 担当者マスタ MT
                    ON MT.担当者ＣＤ = MC.担当者ＣＤ
            WHERE
                M1.得意先CD = '$CustomerCd'
            AND (M1.受注得意先ＣＤ = 0 OR M1.受注得意先ＣＤ = M1.得意先ＣＤ)
            ";

        $Data = DB::selectOne($sql);

        $CourseCD = $Data->コースＣＤ ?? null;
        $BushoCD = $Data->部署ＣＤ ?? null;
        
        DB::beginTransaction();
        try {

            得意先単価マスタ新::query()
                ->where('得意先ＣＤ', $params['得意先ＣＤ'])
                ->where('商品ＣＤ', $params['商品ＣＤ'])
                ->where('適用開始日', $params['適用開始日'])
                ->delete();

            DB::commit();
            //モバイルsv更新
            $Message = $params['Message'];
            $Message['department_code'] = $BushoCD;
            $Message['course_code'] = $CourseCD;
            $ds = new DataSendWrapper();
            //$ds->Delete('得意先単価マスタ新', $params, false, null, $params['得意先ＣＤ'], null, $Message);
            $ds->UpdateCustomerPricemasterNew($params['得意先ＣＤ'],$Message,$CourseCD);
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            "result" => true,
        ]);
    }
}
