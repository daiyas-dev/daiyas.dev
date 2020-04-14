<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI05030Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;

        $BushoArray = $vm->BushoArray;
        $WhereBusho="";
        if($BushoArray !=null && is_array($BushoArray) && 0<count($BushoArray))
        {
            $BushoList = implode(',',$BushoArray);
            $WhereBusho=" AND 売掛データ.部署ＣＤ IN( $BushoList )";
        }

        $TokuiCd = $vm->TokuiCd;
        $WhereTokui="";
        if($TokuiCd !=null)
        {
            $WhereTokui=" AND 売掛データ.請求先ＣＤ = $TokuiCd";
        }

        $sql = "
        SELECT
            日付,
            売掛データ.
            請求先ＣＤ,
            前月残高,
            今月入金額,
            差引繰越額,
            今月売上額,
            今月残高,
            得意先マスタ.得意先名
        FROM
            [売掛データ]
            INNER JOIN [得意先マスタ] ON 得意先マスタ.得意先ＣＤ = 売掛データ.請求先ＣＤ AND 得意先マスタ.部署ＣＤ = 売掛データ.部署ＣＤ
        WHERE
            日付 = '$DateStart'
        $WhereTokui
        $WhereBusho
        ORDER BY
            売掛データ.請求先ＣＤ,
            日付
        ";

        //$DataList = DB::select($sql);
        $dsn = 'sqlsrv:server=localhost;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        return response()->json($DataList);
    }
}
