<?php

namespace App\Http\Controllers;

use App\Models\コーステーブル;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;

class DAI04091Controller extends Controller
{

    /**
     * GetCustomerListForSelect
     */
    public function GetCustomerListForSelect($request)
    {
        $BushoCd = $request->bushoCd ?? $request->BushoCd;

        if (!$BushoCd) return [];

        $sql = "
            SELECT
                TM.得意先ＣＤ AS Cd,
                TM.得意先名 AS CdNm,
                TM.得意先名カナ,
                TM.得意先名略称
            FROM 得意先マスタ TM
            WHERE TM.部署ＣＤ=$BushoCd
        ";

            $Result = DB::select($sql);

            return response()->json($Result);
    }

    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $Condition = $params['Condition'];
        $BushoCd = $Condition['BushoCd'];
        $CourseCd = $Condition['CourseCd'];
        $MngCd = $Condition['MngCd'];
        $Kind = $Condition['Kind'];
        $StartDate = $Condition['StartDate'];
        $EndDate = $Condition['EndDate'];
        $Memo = $Condition['Memo'];
        $EditUserCd = $Condition['EditUserCd'];

        $SaveList = $params['SaveList'];

        $date = Carbon::now()->format('Y-m-d H:i:s');

        $Table = $Kind == 0 ? "コーステーブル" : "コーステーブル一時";
        $WhereMngCd = $Kind == 0 ? "" : "AND 管理ＣＤ=$MngCd";

        //期間重複チェック
        $WhereSelf = is_numeric($MngCd) ? "AND 管理ＣＤ=$MngCd" : "";
        $ChkSql = "
                SELECT
                    COUNT(管理ＣＤ) AS DupCnt
                FROM コーステーブル管理
                WHERE
                    部署ＣＤ=$BushoCd
                AND コースＣＤ=$CourseCd
                AND 一時フラグ = 1
                $WhereSelf
                AND (適用開始日 <= '$EndDate' AND 適用終了日 >= '$StartDate')
            ";
        $DupCnt = DB::selectOne($ChkSql)->DupCnt;

        if ($MngCd != 0 && $DupCnt != 0) {
            return response()->json([
                "result" => false,
                "message" => "適用期間が重複しています",
            ]);
        }

        DB::beginTransaction();

        try {
            if (is_numeric($MngCd)) {
                $DelSql = "
                    DELETE FROM コーステーブル管理
                    WHERE
                        部署ＣＤ=$BushoCd
                    AND コースＣＤ=$CourseCd
                    AND 管理ＣＤ=$MngCd
                ";
                DB::delete($DelSql);

                $DelSql = "
                    DELETE FROM $Table
                    WHERE
                        部署ＣＤ=$BushoCd
                    AND コースＣＤ=$CourseCd
                    $WhereMngCd
                ";
                DB::delete($DelSql);
            } else {
                $SelSql = "
                    SELECT
                        ISNULL(MAX(管理ＣＤ), 0) + 1 AS MngCd
                    FROM コーステーブル管理
                    WHERE
                        部署ＣＤ=$BushoCd
                    AND コースＣＤ=$CourseCd
                ";
                $MngCd = DB::selectOne($SelSql)->MngCd;
            }

            $InsSql = "
                INSERT INTO コーステーブル管理
                VALUES
                    ($BushoCd
                    ,$CourseCd
                    ,$MngCd
                    ,$Kind
                    ,'$StartDate'
                    ,'$EndDate'
                    ,'$Memo'
                    ,$EditUserCd
                    ,'$date')
            ";
            DB::insert($InsSql);

            foreach ($SaveList as $rec) {
                $rec['修正日'] = "'$date'";

                $Values = $rec['部署ＣＤ'] . ','
                    . $rec['コースＣＤ'] . ','
                    . ($Kind == 0 ? '' : $MngCd . ',')
                    . $rec['ＳＥＱ'] . ','
                    . $rec['得意先ＣＤ'] . ','
                    . $rec['修正担当者ＣＤ'] . ','
                    . $rec['修正日']
                    ;

                $InsDataSql = "
                    INSERT INTO $Table
                    VALUES ($Values)
                ";

                DB::insert($InsDataSql);
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            "result" => true,
            "MngCd" => $MngCd,
        ]);
    }

}
