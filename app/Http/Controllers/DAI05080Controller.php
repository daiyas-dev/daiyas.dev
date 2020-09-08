<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI05080Controller extends Controller
{
    public function SearchCustomerPrice($vm)
    {
        $today = date("Y/m/d");
        $BushoArray = $vm->BushoArray;
        $WhereBusho1="";
        if($BushoArray !=null && is_array($BushoArray) && 0<count($BushoArray))
        {
            $BushoList = implode(',',$BushoArray);
            $WhereBusho1=" AND T1.部署ＣＤ IN( $BushoList )";
        }

        $sql = "
                SELECT
                     T1.部署ＣＤ
                    ,T1.得意先ＣＤ
                    ,RIGHT ('000' + CONVERT(varchar, T4.商品ＣＤ), 3) + ' ' + T4.商品名 表示商品名
                    ,T4.商品名
                    ,T3.単価
                FROM
                    得意先マスタ T1
                    LEFT JOIN (
                            SELECT * FROM (
                                SELECT *, RANK() OVER(PARTITION BY 得意先ＣＤ, 商品ＣＤ ORDER BY 適用開始日 DESC) AS RNK
                                FROM 得意先単価マスタ新
                                WHERE 適用開始日 <= '$today'
                            ) TT
                            WHERE RNK = 1
                        ) T3 ON T1.得意先ＣＤ = T3.得意先ＣＤ
                    LEFT JOIN 商品マスタ T4 ON T4.商品ＣＤ = T3.商品ＣＤ
                WHERE
                    0=0
                    $WhereBusho1
                ORDER BY
                     T3.商品ＣＤ
            ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        return $DataList;
    }
    public function SearchCourse($vm)
    {
        $BushoArray = $vm->BushoArray;
        $WhereBusho1="";
        if($BushoArray !=null && is_array($BushoArray) && 0<count($BushoArray))
        {
            $BushoList = implode(',',$BushoArray);
            $WhereBusho1=" AND T1.部署ＣＤ IN( $BushoList )";
        }

        $sql = "
                SELECT
                     T1.部署ＣＤ
                    ,T5.部署名
                    ,T1.得意先ＣＤ
                    ,T1.郵便番号
                    ,T1.住所１
                    ,T1.住所２
                    ,T1.得意先名カナ
                    ,T1.得意先名
                    ,T1.電話番号１
                    ,T1.ＦＡＸ１
                FROM
                    得意先マスタ T1
                    LEFT JOIN 部署マスタ T5 ON T1.部署ＣＤ = T5.部署CD
                WHERE
                    0=0
                    $WhereBusho1
                ORDER BY
                    T1.得意先ＣＤ
            ";
        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        return $DataList;
    }

    /**
     * Search
     */
    public function Search($request)
    {
        return response()->json(
            [
                [
                    "CourseData" => $this->SearchCourse($request),
                    "PriceData" => $this->SearchCustomerPrice($request),
                ]
            ]
        );
    }
}
