<?php

namespace App\Http\Controllers;

use App\Models\入金データ;
use App\Models\管理マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use PDO;

class DAI01150Controller extends Controller
{

    /**
     * GetCourse
     */
    public function GetCourse($request)
    {
        $CustomerCd = $request->CustomerCd;

        $sql = "
            SELECT DISTINCT
                FIRST_VALUE(コースマスタ.コースＣＤ) OVER(ORDER BY コースマスタ.コース区分, コースマスタ.コースＣＤ)  AS コースＣＤ,
                FIRST_VALUE(コースマスタ.コース名) OVER(ORDER BY コースマスタ.コース区分, コースマスタ.コースＣＤ) AS コース名
            FROM
                コースマスタ
                INNER JOIN コーステーブル
                    ON  コースマスタ.部署ＣＤ = コーステーブル.部署ＣＤ
                    AND コースマスタ.コースＣＤ = コーステーブル.コースＣＤ
            WHERE
                0=0
            AND コーステーブル.得意先ＣＤ=$CustomerCd
        ";

        $Result = DB::selectOne($sql);

        return response()->json($Result);
    }

    /**
     * GetPaymentInfo
     */
    public function GetPaymentInfo($request)
    {
        $CustomerCd = $request->CustomerCd;

        $sql = "
            SELECT
                締区分,K1.各種名称 AS 締区分名
                ,締日１
                ,支払方法１,K2.各種名称 AS 支払方法
                ,支払サイト,K3.各種名称 AS 支払サイト名,支払日
            FROM 得意先マスタ M1
                LEFT JOIN 各種テーブル K1
                    ON K1.各種ＣＤ = 3 AND K1.行ＮＯ = M1.締区分
                LEFT JOIN 各種テーブル K2
                    ON K2.各種ＣＤ = 6 AND K2.行ＮＯ = M1.支払方法１
                LEFT JOIN 各種テーブル K3
                    ON K3.各種ＣＤ = 4 AND K3.行ＮＯ = M1.支払サイト
            WHERE M1.得意先ＣＤ = $CustomerCd
        ";

        $Result = DB::selectOne($sql);

        return response()->json($Result);
    }

    /**
     * GetSeikyuDataList
     */
    public function GetSeikyuDataList($request)
    {
        $CustomerCd = $request->CustomerCd;

        $sql = "
            SELECT
                予備金額１ AS 請求ＩＤ
                ,請求日付
                ,D1.[３回前残高]+D1.前々回残高+D1.前回残高 AS 前月残高
                ,D1.今回入金額 AS 当月入金
                ,D1.差引繰越額 AS 繰越残高
                ,D1.今回売上額 + D1.消費税額 AS 今回売上
                ,D1.消費税額 AS 消費税
                ,D1.今回請求額
                ,D1.回収予定日 AS 入金予定日
            FROM 請求データ D1
                INNER JOIN 得意先マスタ M1 ON M1.得意先ＣＤ = D1.請求先ＣＤ
            WHERE D1.請求先ＣＤ = $CustomerCd
            ORDER BY D1.請求日付 DESC
        ";

        $DataList = DB::select($sql);
        return $DataList;
    }

    /**
     * GetNyukinDataList
     */
    public function GetNyukinDataList($request)
    {
        $CustomerCd = $request->CustomerCd;

        $sql = "
            SELECT
                伝票Ｎｏ AS 入金ＩＤ
                ,入金日付
                ,現金
                ,小切手
                ,振込
                ,相殺 AS 振込手数料
                ,バークレー AS 振替
                ,その他 AS 金券
                ,値引 AS その他
            FROM 入金データ D1
            WHERE D1.得意先ＣＤ = $CustomerCd
            ORDER BY D1.入金日付 DESC
        ";

        $DataList = DB::select($sql);
        return $DataList;
    }
    /**
     * GetData
     */
    public function GetData($request)
    {
        return [
            "SeikyuDataList" => $this->GetSeikyuDataList($request),
            "NyukinDataList" => $this->GetNyukinDataList($request),
        ];
    }

    /**
     * Search
     */
    public function Search($request)
    {
        return response()->json($this->GetData($request));
    }


    /**
     * Save
     */
    public function Save($request)
    {
        $skip = [];

        DB::beginTransaction();

        try {
            $params = $request->all();

            $SaveList = $params['SaveList'];

            $date = Carbon::now()->format('Y-m-d H:i:s');
            foreach ($SaveList as $rec) {
                if (isset($rec['伝票Ｎｏ']) && !!$rec['伝票Ｎｏ']) {
                    $r = 入金データ::query()
                        ->where('入金日付', $rec['入金日付'])
                        ->where('伝票Ｎｏ', $rec['伝票Ｎｏ'])
                        ->get();

                    if (count($r) != 1) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => null]);
                        continue;
                    } else if ($rec['修正日'] != $r[0]->修正日) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                        continue;
                    }

                    //現金が0の場合は削除扱い
                    if (!isset($rec['現金']) && !isset($rec['現金'])) {
                        入金データ::query()
                            ->where('入金日付', $rec['入金日付'])
                            ->where('伝票Ｎｏ', $rec['伝票Ｎｏ'])
                            ->delete();
                    } else {
                        $rec['修正日'] = $date;

                        入金データ::query()
                            ->where('入金日付', $rec['入金日付'])
                            ->where('伝票Ｎｏ', $rec['伝票Ｎｏ'])
                            ->update($rec);
                    }
                } else {
                    $no = 管理マスタ::query()
                        ->where('管理ＫＥＹ', 1)
                        ->max('入金伝票Ｎｏ') + 1;

                    管理マスタ::query()
                        ->where('管理ＫＥＹ', 1)
                        ->update(['入金伝票Ｎｏ' => $no]);

                    $rec['伝票Ｎｏ'] = $no;
                    $rec['修正日'] = $date;

                    入金データ::insert($rec);
                }
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            "edited" => $this->GetNyukinList($request),
        ]);
    }
}
