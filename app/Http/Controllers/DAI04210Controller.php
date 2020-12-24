<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;

class DAI04210Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $TelNo = $vm->TelNo;
        $WhereTelNo=null;
        if($TelNo !=null)
        {
            $WhereTelNo=" AND TEL.電話番号 LIKE '$TelNo%'";
        }

        $sql = "
                    SELECT
                        TEL.電話番号
                        ,ISNULL(CM.得意先名略称,'得意先マスタに登録なし')AS 得意先名
                    FROM
                        非顧客電話番号マスタ TEL
                        LEFT JOIN 得意先マスタ CM ON CM.電話番号１=TEL.電話番号
                    WHERE 0=0
                    　    $WhereTelNo
                ";

        $DataList = DB::select($sql);
        return $DataList;
    }
    /**
     * Delete
     */
    public function Delete($vm)
    {
        $TelNo = $vm->TelNo;
        DB::beginTransaction();
        try {
            $sql = "DELETE FROM 非顧客電話番号マスタ WHERE 電話番号='$TelNo'";
            $ret = DB::delete($sql);
            if ($ret != 1) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
        return;
    }
}
