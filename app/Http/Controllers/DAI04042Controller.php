<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI04042Controller extends Controller
{

    /**
     * GetCustomerListForSelect
     */
    public function GetCustomerListForSelect($request)
    {
        // $BushoCd = $request->bushoCd ?? $request->BushoCd;

        // if (!$BushoCd) return [];

        $sql = "
SELECT
    TM.得意先ＣＤ AS Cd,
    TM.得意先名 AS CdNm,
    TM.得意先名カナ,
    TM.得意先名略称
FROM 得意先マスタ TM
        ";

        $Result = DB::select($sql);

        return response()->json($Result);
    }

    /**
     * DeleteBunpaisakiList
     */
    public function DeleteBunpaisakiList($request)
    {
        $BpCutomerCd = $request->Bunpaisaki;
        if (!$BpCutomerCd) {
            return [];
        }

        $sql = "
            UPDATE 得意先マスタ
            SET 受注得意先ＣＤ = null
            WHERE 得意先ＣＤ=$BpCutomerCd

        ";

        $Result = DB::update($sql);

        return response()->json($Result);
    }

    /**
     * UpdateBunpaisakiList
     */
    public function UpdateBunpaisakiList($request)
    {
        $CustomerCd = $request->CustomerCd;
        $BpCutomerCd = $request->Bunpaisaki;
        $List = implode(",", $BpCutomerCd);
        $List = preg_replace("/^,+|,+$/", "", $List);
        $BpCdList = preg_replace("/,,+/", ",", $List);

        if (!$BpCdList) {
            return [];
        }

        $sql = "
            UPDATE 得意先マスタ
            SET 受注得意先ＣＤ = $CustomerCd
            WHERE 得意先ＣＤ in ($BpCdList)
        ";

        $Result = DB::update($sql);

        return response()->json($Result);
    }
}
