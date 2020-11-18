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
     * チケット調整IDを取得する(ドロップダウン表示用)
     * キー：得意先CD
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
            ORDER BY
                チケット調整ID DESC
        ";
        $DataList = DB::select($sql);

        $DataNew = new \stdClass();
        $DataNew->Cd=0;
        $DataNew->CdNm='(新規作成)';
        return response()->json(array_merge(array($DataNew), $DataList));
    }
    /**
     * 得意先情報を取得する
     * キー：得意先CD
     */
    private function SearchCustomerTicket($vm)
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
     * キー：得意先CD
     */
    private function SearchCustomerProduct($vm)
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
     * 得意先関連の情報を取得する
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
     * 得意先のチケット残数を取得する
     * キー：調整ID(に紐付く調整日)
     */
    private function SearchTicketZansu($vm)
    {
        $CustomerCd = $vm->CustomerCd;
        $AdjustmentDate = $vm->AdjustmentDate;
        $sql="
            SELECT
                D1.得意先ＣＤ,
                チケット内数 - ISNULL(チケット弁当数, 0) - ISNULL(チケット減数, 0) as チケット残数,
                SV内数 - ISNULL(CONVERT(DECIMAL(18,1), SV弁当数), 0.0) - ISNULL(SV減数, 0.0) as サービス残数
            FROM
            (
                SELECT
                    得意先ＣＤ,
                    SUM(チケット内数) as チケット内数,
                    SUM(SV内数) as SV内数
                    FROM チケット発行
                WHERE 発行日 <= '$AdjustmentDate'
                    AND 廃棄 = 0
                    AND 得意先ＣＤ=$CustomerCd
                GROUP BY 得意先ＣＤ
            ) D1
            LEFT OUTER JOIN
                (
                    SELECT
                        得意先ＣＤ,
                        SUM(掛売個数) as チケット弁当数
                        FROM 売上データ明細
                    WHERE 売掛現金区分 = 2
                        AND 日付 <= '$AdjustmentDate'
                    GROUP BY 得意先ＣＤ
                ) D2 on D1.得意先ＣＤ = D2.得意先ＣＤ
            LEFT OUTER JOIN
                (
                    SELECT
                        得意先ＣＤ,
                        SUM(掛売個数) as SV弁当数
                        FROM 売上データ明細
                    WHERE 売掛現金区分 = 4
                        AND 日付 <= '$AdjustmentDate'
                    GROUP BY
                        得意先ＣＤ
                ) D3 on D1.得意先ＣＤ = D3.得意先ＣＤ
            LEFT OUTER JOIN
                (
                    SELECT
                        得意先ＣＤ,
                        SUM(チケット減数) as チケット減数,
                        SUM(SV減数) as SV減数
                        FROM チケット調整
                    WHERE 日付 <= '$AdjustmentDate'
                    GROUP BY 得意先ＣＤ
                ) D4 on D1.得意先ＣＤ = D4.得意先ＣＤ
            ";
        $DataList = DB::select($sql);
        if(count($DataList)==0)
        {
            $DataNew = new \stdClass();
            $DataNew->得意先ＣＤ=$CustomerCd;
            $DataNew->チケット残数=0;
            $DataNew->サービス残数=0;
            $DataList=array($DataNew);
        }
        return $DataList;
    }
    /**
     * 得意先のチケット調整状況(累計)を取得する
     * キー：調整ID
     */
    private function SearchTicketAdjustmentSummary($vm)
    {
        $CustomerCd = $vm->CustomerCd;
        $AdjustmentID = $vm->AdjustmentID;
        if($AdjustmentID==0)
        {
            return null;
        }
        $sql="
                SELECT
                      SUM(チケット減数) as 累積チケット減数
                    , SUM(SV減数) as 累積SV減数
                FROM
                    [チケット調整]
                WHERE
                    [チケット調整ID] >= $AdjustmentID
                    AND 得意先ＣＤ= $CustomerCd
            ";
            $DataList = DB::select($sql);
        return $DataList;
    }
    /**
     * 得意先のチケット調整状況(調整ID単位)を取得する
     * キー：調整ID
     */
    private function SearchTicketAdjustment($vm)
    {
        $AdjustmentID = $vm->AdjustmentID;
        $sql="
            SELECT
                チケット調整ID
                , CONVERT(varchar, チケット調整ID) as チケット調整IDDisplay
                , 得意先ＣＤ
                , 日付
                , 調整理由
                , 商品ＣＤ
                , チケット減数
                , SV減数
                , 金額
            FROM
                [チケット調整]
            WHERE
                [チケット調整ID] = $AdjustmentID
            ";
        $DataList = DB::select($sql);
        return $DataList;
    }
    /**
     * チケット残数関連の情報を取得する
     */
    public function SearchAdjustmentInfo($vm)
    {
        return response()->json(
            [
                [
                    "TicketZansu" => $this->SearchTicketZansu($vm),
                    "TicketAdjustment" => $this->SearchTicketAdjustment($vm),
                    "TicketAdjustmentSummary" => $this->SearchTicketAdjustmentSummary($vm),
                ]
            ]
        );
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
            $TicketZanSa=-$TicketZanSa;//数値の正負を反転する
            $SVTicketZanSa=$params['SVTicketZanSa'];
            $SVTicketZanSa=-$SVTicketZanSa;//数値の正負を反転する
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
