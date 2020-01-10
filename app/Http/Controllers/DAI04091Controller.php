<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
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
}
