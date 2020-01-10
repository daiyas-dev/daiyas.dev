<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI04042Controller extends Controller
{
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
        $BpCdList = implode(",", $BpCutomerCd);

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
