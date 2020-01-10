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
        $CustomerCd = $request->CustomerCd;
        if (!$CustomerCd) {
            return [];
        }

        $sql = "
            UPDATE 得意先マスタ
            SET 受注得意先ＣＤ = null
            WHERE 受注得意先ＣＤ=$CustomerCd

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
        $BunpaisakiList = $request->BunpaisakiList;
        $BunpaisakiList = implode(",", $BunpaisakiList);

        if (!$BunpaisakiList) {
            return [];
        }

        $sql = "
            UPDATE 得意先マスタ
            SET 受注得意先ＣＤ = $CustomerCd
            WHERE 得意先ＣＤ in ($BunpaisakiList)
        ";

        $Result = DB::update($sql);

        return response()->json($Result);
    }
}
