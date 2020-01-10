<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI04042Controller extends Controller
{

    /**
     * UpdateBunpaisakiList
     */
    public function UpdateBunpaisakiList($request)
    {
        $CustomerCd = $request->CustomerCd;
        $BunpaisakiList = $request->BunpaisakiList;

        if (!$CustomerCd) return [];

        $sql = "
        ";

        // $sql = "
        //     UPDATE 得意先マスタ
        //     SET 受注得意先ＣＤ = null
        //     WHERE 受注得意先ＣＤ=$CustomerCd

        //     UPDATE 得意先マスタ
        //     SET 受注得意先ＣＤ = $CustomerCd
        //     WHERE 得意先ＣＤ in ($BunpaisakiList)

        // ";

            $Result = DB::select($sql);

            return response()->json($Result);
        }
    }
