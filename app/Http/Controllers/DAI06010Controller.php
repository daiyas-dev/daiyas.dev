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
        if ($vm->SearchCondition=="2" && $CustomerCd!=null) {
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
        $order="";
        if ($vm->PrintOrder=="1") {
            $order=" order by M1.得意先名カナ";
        }else{
            $order=" order by D1.得意先ＣＤ,D1.発行日 DESC";
        }
        $sql.=$order;

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        return $DataList;
    }
}
