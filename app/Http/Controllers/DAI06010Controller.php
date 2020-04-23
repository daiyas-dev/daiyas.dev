<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI06010Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $BushoCd = $vm->BushoCd;
        $DateStart=$vm->DateStart;
        $DateEnd=$vm->DateEnd;
        $CustomerCd=$vm->CustomerCd;

        //処理選択
        $sql_customer_cd="";
        if ($vm->SearchType=="2" && $CustomerCd!=null) {
            $sql_customer_cd=" AND D1.得意先ＣＤ = $CustomerCd";
        }

        //発行範囲
        $sql_date_period="";
        if ($vm->IssuePeriod=="2" && $DateStart!=null && $DateEnd!=null) {
            $sql_date_period="　AND 発行日 >= '$DateStart'　AND 発行日 <= '$DateEnd'";
        }

        $sql = "
                SELECT
                      チケット管理番号
                    , D1.得意先ＣＤ
                    , 得意先名
                    , FORMAT(D1.発行日,'yyyy/MM/dd')AS F発行日
                    , FORMAT(有効期限,'yyyy/MM/dd')AS F有効期限
                    , FORMAT(印刷日時,'yyyy/MM/dd')AS F印刷日時
                    , チケット内数
                    , SV内数
                    , 単価
                    , CASE 廃棄 WHEN 1 THEN 0 ELSE ISNULL(チケット内数,0)END AS 有効チケット枚数
                    , CASE 廃棄 WHEN 1 THEN 0 ELSE ISNULL(SV内数,0)END AS 有効サービス枚数
                    , CASE 廃棄 WHEN 1 THEN 0 ELSE 単価*ISNULL(チケット内数,0)END AS チケット金額
                    , CASE 廃棄 WHEN 1 THEN 0 ELSE 単価*ISNULL(SV内数,0)END AS サービス金額
                    , D1.担当者ＣＤ
                    , M2.担当者名
                    , 廃棄
                    , M1.得意先名カナ
                FROM
                    [チケット発行] D1
                    INNER JOIN [得意先マスタ] M1
                        ON M1.得意先ＣＤ = D1.得意先ＣＤ
                    INNER JOIN [担当者マスタ] M2
                        ON M2.担当者ＣＤ = D1.担当者ＣＤ
                WHERE
                    M1.部署CD = $BushoCd
                    $sql_date_period
                    $sql_customer_cd
                ";

        //出力順
        /*
        $order="";
        if ($vm->PrintOrder=="1") {
            $order=" order by M1.得意先名カナ";
        }else{
            $order=" order by D1.得意先ＣＤ,D1.発行日 DESC";
        }
        $sql.=$order;
        */

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        return $DataList;
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
            $ShuseiTantoCd=$params['ShuseiTantoCd'];
            $SaveList = $params['SaveList'];

            //更新実施
            foreach($SaveList as $SaveItem)
            {
                $TicketNo=$SaveItem['TicketNo'];
                $CustomerCd=$SaveItem['CustomerCd'];
                $Dispose= 'false';
                if($SaveItem['Dispose']!=null && $SaveItem['Dispose']){
                    $Dispose= 'true';
                }

                $sql="
                    UPDATE チケット発行
                    SET  廃棄 = '$Dispose'
                        , 修正担当者ＣＤ = $ShuseiTantoCd
                        , 修正日 = '$date'
                    WHERE 得意先ＣＤ = $CustomerCd
                      AND チケット管理番号 = $TicketNo
                ";
                $result = DB::update($sql);
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
