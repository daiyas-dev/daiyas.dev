<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
//use PDO;

class DAI03020Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $PrintOrder = $vm->PrintOrder;
        $sql = $PrintOrder==='0' ? $this->SearchSQLTokui($vm) : $this->SearchSQLCourse($vm);
        $DataList = DB::select($sql);
        /*
        $dsn = 'sqlsrv:server=localhost;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        */
        return response()->json($DataList);
    }

    /**
     * 検索SQL(得意先別)
     */
    private function SearchSQLTokui($vm)
    {
        $TargetDate=$this->GetTargetDate($vm->TargetDate);
        $BushoArray = $vm->BushoArray;

        $WhereBusho1="";
        $WhereBusho2="";
        if($BushoArray !=null && is_array($BushoArray) && 0<count($BushoArray))
        {
            $BushoList = implode(',',$BushoArray);
            $WhereBusho1=" AND COUT.部署ＣＤ IN( $BushoList )";
            $WhereBusho2=" AND 得意先マスタ.部署ＣＤ IN( $BushoList )";
        }

        $sql = "
            SELECT
                日付
                ,売掛データ.請求先ＣＤ
                ,前月残高
                ,今月入金額
                ,差引繰越額
                ,今月売上額
                ,今月残高
                ,得意先マスタ.得意先名
                ,コーステーブル.コースＣＤ
                ,コースマスタ.コース名
                ,コースマスタ.担当者ＣＤ
                ,売掛データ.部署ＣＤ
                ,部署マスタ.部署名
            FROM
                [売掛データ]
                INNER JOIN [得意先マスタ] ON 得意先マスタ.得意先ＣＤ = 売掛データ.請求先ＣＤ
                LEFT JOIN (
                        SELECT
                            COUT.得意先ＣＤ
                            ,COUT.部署ＣＤ
                            ,MIN(COUT.コースＣＤ) AS コースＣＤ
                        FROM
                            コースマスタ COUM
                            INNER JOIN コーステーブル COUT ON
                            COUM.部署ＣＤ = COUT.部署ＣＤ
                            AND COUM.コースＣＤ = COUT.コースＣＤ
                        WHERE
                            COUM.コース区分 IN (1,2,3)
                            $WhereBusho1
                        GROUP BY
                            COUT.得意先ＣＤ
                            ,COUT.部署ＣＤ
                    ) COU_KEY ON
                    COU_KEY.部署ＣＤ = 売掛データ.部署ＣＤ
                    AND COU_KEY.得意先ＣＤ =
                        CASE
                            WHEN 得意先マスタ.受注得意先ＣＤ = 0 THEN 得意先マスタ.得意先ＣＤ
                            ELSE 得意先マスタ.受注得意先ＣＤ
                        END
                LEFT JOIN コーステーブル ON
                    コーステーブル.部署ＣＤ = COU_KEY.部署ＣＤ
                    AND コーステーブル.コースＣＤ = COU_KEY.コースＣＤ
                    AND コーステーブル.得意先ＣＤ =
                        CASE
                        WHEN 得意先マスタ.受注得意先ＣＤ = 0 THEN 得意先マスタ.得意先ＣＤ
                        ELSE 得意先マスタ.受注得意先ＣＤ END
                LEFT JOIN [コースマスタ] ON
                    コースマスタ.部署ＣＤ = コーステーブル.部署ＣＤ
                    AND コースマスタ.コースＣＤ = コーステーブル.コースＣＤ
                INNER JOIN [部署マスタ] ON
                    売掛データ.部署ＣＤ = 部署マスタ.部署ＣＤ
            WHERE
                日付 = '$TargetDate'
                $WhereBusho2
                AND 得意先マスタ.得意先ＣＤ = 得意先マスタ.請求先ＣＤ
            ORDER BY
                売掛データ.部署ＣＤ, 売掛データ.請求先ＣＤ,日付
                ";
       return $sql;
    }
        /**
     * 検索SQL(コース別)
     */
    private function SearchSQLCourse($vm)
    {
        $TargetDate=$this->GetTargetDate($vm->TargetDate);
        $BushoArray = $vm->BushoArray;

        $whereBusho1="";
        $whereBusho2="";
        if($BushoArray !=null && is_array($BushoArray) && 0<count($BushoArray))
        {
            $BushoList = implode(',',$BushoArray);
            $whereBusho1 = " AND COUT.部署ＣＤ IN( $BushoList )";
            $whereBusho2 = " AND 得意先マスタ.部署ＣＤ IN( $BushoList )";
        }

        $sql = "
                SELECT
                    日付
                    ,売掛データ.請求先ＣＤ
                    ,前月残高
                    ,今月入金額
                    ,差引繰越額
                    ,今月売上額
                    ,今月残高
                    ,得意先マスタ.得意先名
                    ,コーステーブル.コースＣＤ
                    ,コースマスタ.コース名
                    ,コースマスタ.担当者ＣＤ
                    ,売掛データ.部署ＣＤ
                    ,部署マスタ.部署名
                FROM
                    [売掛データ]
                    INNER JOIN [得意先マスタ] ON 得意先マスタ.得意先ＣＤ = 売掛データ.請求先ＣＤ
                    INNER JOIN (
                            SELECT
                                COUT.得意先ＣＤ
                                ,COUT.部署ＣＤ
                                ,MIN(COUT.コースＣＤ) AS コースＣＤ
                            FROM
                                コースマスタ COUM
                                INNER JOIN コーステーブル COUT ON
                                COUM.部署ＣＤ = COUT.部署ＣＤ
                                AND COUM.コースＣＤ = COUT.コースＣＤ
                            WHERE
                                COUM.コース区分 IN (1,2,3)
                                $whereBusho1
                            GROUP BY
                                COUT.得意先ＣＤ
                                ,COUT.部署ＣＤ
                        ) COU_KEY ON
                        COU_KEY.部署ＣＤ = 売掛データ.部署ＣＤ
                        AND COU_KEY.得意先ＣＤ =
                            CASE
                            WHEN 得意先マスタ.受注得意先ＣＤ = 0 THEN 得意先マスタ.得意先ＣＤ
                            ELSE 得意先マスタ.受注得意先ＣＤ
                            END
                    INNER JOIN コーステーブル ON
                        コーステーブル.部署ＣＤ = COU_KEY.部署ＣＤ
                        AND コーステーブル.コースＣＤ = COU_KEY.コースＣＤ
                        AND コーステーブル.得意先ＣＤ =
                            CASE
                            WHEN 得意先マスタ.受注得意先ＣＤ = 0 THEN 得意先マスタ.得意先ＣＤ
                            ELSE 得意先マスタ.受注得意先ＣＤ END
                    INNER JOIN [コースマスタ] ON
                        コースマスタ.部署ＣＤ = コーステーブル.部署ＣＤ
                        AND コースマスタ.コースＣＤ = コーステーブル.コースＣＤ
                        INNER JOIN [部署マスタ] ON
                        売掛データ.部署ＣＤ = 部署マスタ.部署ＣＤ
                WHERE
                    日付 = '$TargetDate'
                    $whereBusho2
                    AND 得意先マスタ.得意先ＣＤ = 得意先マスタ.請求先ＣＤ
                ORDER BY
                    売掛データ.部署ＣＤ, コーステーブル.コースＣＤ,ＳＥＱ,日付
                  ";
       return $sql;
    }

    /**
     * yyyy年nn月書式の日付をyyyy/mm/01にして戻す。
     */
    private function GetTargetDate($datestr)
    {
        return preg_replace('/年|月/','/',$datestr).'01';
    }
}
