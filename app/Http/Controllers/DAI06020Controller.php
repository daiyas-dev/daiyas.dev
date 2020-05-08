<?php

namespace App\Http\Controllers;

use App\Models\チケット発行;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI06020Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $BushoCd = $vm->BushoCd;
        $CustomerCd = $vm->CustomerCd;

        //得意先の情報を取得する。取得されるのは必ず1件。
        $sql = "
            WITH WITH_CUSTOMER AS(
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
                    得意先マスタ
                WHERE 部署CD = $BushoCd
                  AND 得意先ＣＤ = $CustomerCd
            )
            ,WITH_TICKET_NO AS (
                SELECT
                MAX(チケット管理番号) AS チケット管理番号
                FROM
                チケット発行
                WHERE
                得意先ＣＤ = $CustomerCd
            )
            ,WITH_PRICE_MASTER AS(
                SELECT
                    TT.*
                    ,IIF(
                        FIRST_VALUE(TT.適用開始日) OVER(
                            PARTITION BY TT.得意先ＣＤ, TT.商品ＣＤ
                            ORDER BY TT.適用開始日 DESC
                        ) = TT.適用開始日,
                        1, 0
                    ) AS 状況
                FROM
                    得意先単価マスタ新 TT
                WHERE
                        TT.得意先ＣＤ=$CustomerCd
            )
            ,WITH_PRODUCT AS(
                SELECT TOP 1
                  商品名
                , 単価
                , 商品マスタ.商品ＣＤ as 商品ＣＤ
                FROM
                  商品マスタ
                , WITH_PRICE_MASTER
                WHERE 商品マスタ.商品ＣＤ = WITH_PRICE_MASTER.商品ＣＤ
                  AND WITH_PRICE_MASTER.状況=1
                ORDER BY 商品ＣＤ
            )
            SELECT
            WITH_CUSTOMER.*
            ,WITH_TICKET_NO.*
            ,WITH_PRODUCT.*
            FROM
            WITH_CUSTOMER
            ,WITH_TICKET_NO
            ,WITH_PRODUCT
        ";
        $DataList = DB::select($sql);

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
            $InsatsuMaisu=$params['InsatsuMaisu'];
            $CustomerCd=$params['CustomerCd'];
            $TicketNo=$params['TicketNo'];
            $IssueDate=$params['IssueDate'];
            $ExpireDate=$params['ExpireDate'];
            $TicketSu=$params['TicketSu'];
            $SvTicketSu=$params['SvTicketSu'];
            $InsatsuTantoCd=$params['InsatsuTantoCd'];
            $SaveItem=$params['SaveList'][0];

            for($i=0;$i<$InsatsuMaisu;$i++)
            {
                $rec=array();
                $rec['得意先ＣＤ'] = $CustomerCd;
                $rec['チケット管理番号'] = $TicketNo+$i;
                $rec['印刷日時'] = $date;
                $rec['発行日'] = $IssueDate;
                $rec['有効期限'] = $ExpireDate;
                $rec['チケット内数'] = $TicketSu;
                $rec['SV内数'] = $SvTicketSu;
                $rec['単価'] = $SaveItem['単価'];
                $rec['金額'] = $rec['単価'] * $rec['チケット内数'];
                $rec['商品ＣＤ'] = $SaveItem['商品ＣＤ'];
                $rec['担当者ＣＤ'] = $InsatsuTantoCd;
                $rec['廃棄'] = 0;
                $rec['修正日'] = $date;
                $rec['修正担当者ＣＤ'] = $InsatsuTantoCd;
                チケット発行::insert($rec);
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            //'lastupdatedate'=>$this->LastUpdateDateSearch($request),
            //'edited' => $this->Search($request),
        ]);
    }
}
