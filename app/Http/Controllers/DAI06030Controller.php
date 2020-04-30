<?php

namespace App\Http\Controllers;

use App\Models\チケット調整;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI06030Controller extends Controller
{
    /**
     * チケット調整IDを取得する
     */
    public function SearchAdjustmentID($vm)
    {
        $CustomerCd = $vm->CustomerCd;
        $sql="
            SELECT
                  チケット調整ID as Cd
                , CONVERT(varchar, チケット調整ID) as CdNm
            FROM
                [チケット調整]
            WHERE
                [得意先ＣＤ] = $CustomerCd
            UNION
            SELECT
                  0
                , '(新規作成)'
            ORDER BY
                チケット調整ID DESC
        ";
        $DataList = DB::select($sql);
        return response()->json($DataList);
    }
    /**
     * 得意先情報を取得する
     */
    public function SearchCustomerTicket($vm)
    {
        $BushoCd = $vm->BushoCd;
        if($BushoCd==null)
        {
            return null;
        }

        $CustomerCd = $vm->CustomerCd;
        if($CustomerCd==null)
        {
            return null;
        }

        $sql="
                SELECT
                      得意先名
                    , 得意先名略称
                    , 電話番号1
                    , 売掛現金区分
                    , 備考1
                    , 備考2
                    , 備考3
                    , チケット枚数
                    , サービスチケット枚数
                FROM
                    [得意先マスタ]
                WHERE
                    得意先ＣＤ = $CustomerCd
                    AND 部署CD = $BushoCd
              ";
        $DataList = DB::select($sql);
        return $DataList;
    }
    /**
     * 得意先単価情報を取得する
     */
    public function SearchCustomerProduct($vm)
    {
        $CustomerCd = $vm->CustomerCd;
        if($CustomerCd==null)
        {
            return null;
        }

        $sql="
            SELECT
                  商品名
                , 単価
                , [商品マスタ].商品ＣＤ as 商品ＣＤ
            FROM
                [商品マスタ]
                , [得意先単価マスタ]
            where
                    商品マスタ.商品ＣＤ = 得意先単価マスタ.商品ＣＤ
                and 得意先単価マスタ.得意先ＣＤ = $CustomerCd
                    ";
        $DataList = DB::select($sql);
        return $DataList;
    }
    /**
     * Search
     */
    public function SearchCustomer($vm) {
        return response()->json(
            [
                [
                    "TicketData" => $this->SearchCustomerTicket($vm),
                    "ProductData" => $this->SearchCustomerProduct($vm),
                ]
            ]
        );
    }
    /**
     * SearchAdjustmentInfo
     */
    public function SearchAdjustmentInfo($vm)
    {
        $AdjustmentID = $vm->AdjustmentID;

        $sql = "
            SELECT
                  チケット調整ID
                , CONVERT(varchar, チケット調整ID) as チケット調整IDDisplay
                , 得意先ＣＤ
                , 日付
                , 調整理由
                , 商品ＣＤ
                , チケット減数
                , SV減数
            FROM
                [チケット調整]
            WHERE
                チケット調整ID=$AdjustmentID
            ORDER BY
                チケット調整ID DESC
        ";
        $DataList = DB::selectOne($sql);

        return response()->json($DataList);
    }
    /**
     * Save
     */
    public function Save($request)
    {
        DB::beginTransaction();

        try {
            $params = $request->all();

            $date = Carbon::now()->format('Y-m-d H:i:s');
            $AdjustmentID=$params['AdjustmentID'];
            $CustomerCd=$params['CustomerCd'];
            $AdjustmentDate=$params['AdjustmentDate'];
            $AdjustmentReason=$params['AdjustmentReason'];
            $ProductCd=$params['ProductCd'];
            $TicketZanSa=$params['TicketZanSa'];
            $SVTicketZanSa=$params['SVTicketZanSa'];
            $Kingaku=$params['Kingaku'];
            $ShuseiTantoCd=$params['ShuseiTantoCd'];

            if ($AdjustmentID==null || $AdjustmentID==0) {
                $InsSql = "
                    INSERT INTO チケット調整
                    VALUES
                        (
                              (select MAX(チケット調整ID)+1 from チケット調整)
                            , $CustomerCd
                            , '$AdjustmentDate'
                            , $AdjustmentReason
                            , $ProductCd
                            , $TicketZanSa
                            , $SVTicketZanSa
                            , $Kingaku
                            , null
                            , null
                            , null
                            , '$date'
                            , $ShuseiTantoCd
                        )
                ";
                DB::insert($InsSql);
            }
            else{
                $UpdSql = "
                    UPDATE [チケット調整]
                    SET
                    [日付] = '$AdjustmentDate'
                    ,[調整理由] = $AdjustmentReason
                    , [商品ＣＤ]=$ProductCd
                    ,[チケット減数]=$TicketZanSa
                    ,[SV減数]=$SVTicketZanSa
                    ,[金額]=$Kingaku
                    ,[修正日]='$date'
                    ,[修正担当者ＣＤ]=$ShuseiTantoCd
                    where
                    [チケット調整ID] = $AdjustmentID
                ";
                DB::update($UpdSql);
            }
            DB::commit();
        }
        catch (Exception $exception){
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            //'lastupdatedate'=>$this->LastUpdateDateSearch($vm),
            //'edited' => $this->Search($vm),
        ]);
    }
}
