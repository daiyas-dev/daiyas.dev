<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use App\Libs\DataSendWrapper;

class DAI04042Controller extends Controller
{

    /**
     * GetCustomerListForSelect
     */
    public function GetCustomerListForSelect($request)
    {
        $CustomerCd = $request->CustomerCd;
        $WhereCustomerCd = !!$CustomerCd ? "AND TM.得意先ＣＤ <> $CustomerCd" : "";

        // $BushoCd = $request->bushoCd ?? $request->BushoCd;
        // if (!$BushoCd) return [];

        $sql = "
SELECT
    TM.得意先ＣＤ AS Cd,
    TM.得意先名 AS CdNm,
    TM.得意先名カナ,
    TM.得意先名略称
FROM 得意先マスタ TM
WHERE 0=0
        $WhereCustomerCd
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
            SET 受注得意先ＣＤ = 得意先ＣＤ
            WHERE 得意先ＣＤ=$BpCutomerCd

        ";

        $Result = DB::update($sql);

        //モバイルSvを更新
        $Message = $request->Message;
        $ds = new DataSendWrapper();

        $sql="select * from 得意先マスタ where 得意先ＣＤ=$BpCutomerCd";
        $CustomerData = (array)DB::selectOne($sql);
        $Message['department_code']=$CustomerData['部署CD'];
        $del_sql="delete from CustomerMaster where customer_code=$BpCutomerCd";
        $ds->Insert('得意先マスタ',$CustomerData,true,$CustomerData['部署CD'],null,null, $Message,$del_sql);

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

        //モバイルSvを更新
        $Message = $request->Message;
        $ds = new DataSendWrapper();
        $ds->UpdateOrderCustomerCode($CustomerCd,$BpCdList,$Message);

        return response()->json($Result);
    }
}
