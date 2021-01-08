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
                WITH TEL2CUST AS(
                    SELECT Q.* FROM(
                        SELECT
                        TC.Tel_TelNo
                        ,TC.Tel_CustNo
                        ,RANK() OVER(PARTITION BY TC.Tel_TelNo ORDER BY TC.Tel_TelNo,TC.Tel_UpdDate DESC)AS RNK
                        FROM C_TelToCust TC
                    )Q
                    WHERE Q.RNK=1
                )
                SELECT
                    TEL.電話番号
                    ,ISNULL(CM1.得意先名略称,ISNULL(CM2.得意先名略称,'得意先マスタに登録なし'))AS 得意先名
                FROM
                    非顧客電話番号マスタ TEL
                    LEFT JOIN 得意先マスタ CM1 ON CM1.電話番号１=TEL.電話番号
                    LEFT JOIN TEL2CUST T2C ON T2C.Tel_TelNo=TEL.電話番号
                    LEFT JOIN 得意先マスタ CM2 ON CM2.得意先ＣＤ=T2C.Tel_CustNo
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
