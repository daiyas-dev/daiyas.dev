<?php

namespace App\Libs;
use Exception;
use PDO;

//Web受注から社内DBへ取込
class WebOrderReceive extends DataReceiveBase
{
    public $receive_tasks=array("WebUserMaster"=>1,"WebOrderData"=>2);//Web受注受信リストのIDに対応する処理の定義

    /*
        以下に示すテーブル以外のマスタについては、モバイルと同様の仕組みで連携(下記テーブル以外にAWS -> 社内は恐らく無いはず)
        但し、AWS側では全般的に物理削除ではなく、削除日時: deleted_atのデータ有無による論理削除を行っていることに注意
        連携後の社内側では、物理削除を行う
        修正担当者ＣＤは999: システム担当、修正日はAWSの対応するレコードと一致するようupdated_atを適宜利用

        1.  AWS: Web利用者マスタ[WebUserMaster] -> 社内: Web受注得意先利用者マスタ
            AWS側で利用者を新規追加して、その利用者で注文を登録することが可能なので、まずこのマスタの変更を連携
            対象取得は以下のSQLを想定
                SELECT
                    WebUserMaster.web_customer_code AS Web得意先ＣＤ,
                    WebUserMaster.user_id AS 利用者ID,
                    WebUserMaster.user_code AS 利用者CD,
                    IF(WebUserMaster.deleted_at IS NULL, 0, 1) AS 削除フラグ
                FROM
                    WebUserMaster
                WHERE
                    WebUserMaster.updated_at >= [更新判別用日時]
                    OR
                    WebUserMaster.deleted_at >= [更新判別用日時]
                ;

        2.  AWS: WebOrderData -> 社内: Web受注データ
            AWSでは、Web得意先の利用者毎に個別に注文が登録されていくので、それを得意先 + 配送日単位にまとめて連携
            対象取得は以下のSQLを想定
                SELECT
                    WebOrderData.web_customer_code AS Web得意先ＣＤ,
                    WebOrderData.delivery_date AS 配送日,
                    MAX(WebOrderData.updated_at) AS 注文日時,
                    IF(
                    	(
	                        SELECT
	                            COUNT(X.order_id)
	                        FROM
	                            WebOrderData X
	                        WHERE
	                            X.web_customer_code = WebOrderData.web_customer_code
	                        AND X.delivery_date = WebOrderData.delivery_date
	                        AND X.deleted_at IS NOT NULL
	                        GROUP BY
	                            X.web_customer_code,
	                            X.delivery_date
                    	) > 0
                    	,1
                    	,0
                    ) AS 削除フラグ
                FROM WebOrderData
                WHERE
                (
                    WebOrderData.updated_at >= [更新判別用日時]
                    OR
                    WebOrderData.deleted_at >= [更新判別用日時]
                )
                GROUP BY
                    WebOrderData.web_customer_code,
                    WebOrderData.delivery_date
                ;

            社内側Web受注データの更新については、Web得意先ＣＤ及び配送日が一致するレコードが存在する場合はupdateもしくはdelete
            存在しない場合は、Web受注IDを最大 + 1で払い出してinsert

        3.  AWS: WebOrderData -> 社内: Web受注データ利用者情報
            AWSでのWeb得意先の利用者毎の注文と対応
            対象取得は以下のSQLを想定
				SELECT
                    WebOrderData.web_customer_code AS Web得意先ＣＤ,
                    WebOrderData.delivery_date AS 配送日,
					WebOrderData.order_id AS 注文ID,
					WebOrderData.user_id AS 利用者ID,
					WebUserMaster.user_code AS 利用者CD,
                    WebOrderData.remarks_id AS 備考ID,
                    WebOrderData.updated_at,
                    IF(WebOrderData.deleted_at IS NULL, 0, 1) AS 削除フラグ
				FROM WebOrderData
                INNER JOIN WebUserMaster ON WebUserMaster.user_id = WebOrderData.user_id
                WHERE
                (
                    WebOrderData.updated_at >= [更新判別用日時]
                    OR
                    WebOrderData.deleted_at >= [更新判別用日時]
                )
                ;

            社内側Web受注データ利用者情報の更新については、まずWeb得意先ＣＤ及び配送日でWeb受注データからWeb受注IDを取得し
            Web受注IDＣＤ及び注文IDが一致するレコードが存在する場合はupdateもしくはdelete、存在しない場合はinsert

        4.  AWS: WebOrderData + WebOrderInfoData -> 社内: Web受注データ利用者別詳細
            AWSでのWeb得意先の利用者毎の注文の商品レベルの詳細と対応
            対象取得は以下のSQLを想定
				SELECT
                    WebOrderData.web_customer_code AS Web得意先ＣＤ,
                    WebOrderData.delivery_date AS 配送日,
					WebOrderData.order_id AS 注文ID,
					WebOrderInfoData.order_info_id AS 注文情報ID,
					WebOrderInfoData.updated_at AS 注文日時,
                    WebOrderInfoData.web_product_code AS 商品ＣＤ,
                    WebOrderInfoData.price AS 単価,
					IF(WebOrderInfoData.cash_type = 2, WebOrderInfoData.quantity, 0) AS 現金個数,
					IF(WebOrderInfoData.cash_type = 2, WebOrderInfoData.quantity * WebOrderInfoData.price, 0) AS 現金金額,
					IF(WebOrderInfoData.cash_type = 1, WebOrderInfoData.quantity, 0) AS 掛売個数,
					IF(WebOrderInfoData.cash_type = 1, WebOrderInfoData.quantity * WebOrderInfoData.price, 0) AS 掛売金額,
					WebOrderInfoData.destination_id AS 届け先ID,
                    WebOrderInfoData.updated_at,
                    IF(WebOrderInfoData.deleted_at IS NULL, 0, 1) AS 削除フラグ
				FROM WebOrderData
				INNER JOIN WebOrderInfoData ON WebOrderInfoData.order_id = WebOrderData.order_id
				INNER JOIN WebUserMaster ON WebUserMaster.user_id = WebOrderData.user_id
                WHERE
                (
                    WebOrderData.updated_at >= [更新判別用日時]
                    OR
                    WebOrderData.deleted_at >= [更新判別用日時]
                )
                ;

            社内側Web受注データ利用者情報の更新については、まずWeb得意先ＣＤ及び配送日でWeb受注データからWeb受注IDを取得し
            Web受注IDＣＤ及び注文IDが一致するレコードが存在する場合はupdateもしくはdelete、存在しない場合はinsert

            5.  社内側注文データの更新
                    ・Web受注IDで対応するWeb受注データが存在しなくなった場合は、該当注文データを削除
                    ・DAI01032ControllerのSaveOrderFromWebで発行しているselectの結果を基にinsert/update
                    　現在の注文データでselect結果と対応しないレコードは削除
                    　※最初エースを1個注文していたけど、エース大盛に変更した場合などを想定


    */
    public function Receive()
    {
        //TODO:テスト用URL(NEW社内)
        $url = "http://192.168.1.211/hellolaravel/public/api/mobiledatasendx";
        //TODO:本番URL
        //$url="http://52.197.70.172/api/mobiledatasend";

        $this->target_server=$this->target_server_enum["WebOrder"];

        try {
            $sql ="
                    SELECT 受信ＩＤ,タスク名,最終更新日時
                      FROM Web受注受信リスト
                     ORDER BY 受信ＩＤ
                  ";
            $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
            $user = 'daiyas';
            $password = 'daiyas';
            $pdo = new PDO($dsn, $user, $password);
            $stmt = $pdo->query($sql);
            $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;

            //1テーブルごとにリクエストを投げて、処理する
            foreach ($DataList as $DataItem)
            {
                if (!array_key_exists($DataItem['テーブル名'], $this->ConversionMap)) {
                    $this->ErrorReceiveList($DataItem['受信ＩＤ'],"エラー","テーブルマッピング情報がありません。",null);
                    continue;
                }
                $mv_table_name=$this->ConversionMap[$DataItem['テーブル名']]['TableName'];

                //モバイル・Web受注サーバからデータを取得する
                $response = $this->GetResponse($url,$DataItem['受信ＩＤ'],$mv_table_name,$DataItem['最終更新日時']);
                if($response=="") {
                    continue;
                }
                if(!isset($response["file_path"])) {
                    continue;
                }
                $zip_path = $response["file_path"];
                if ($zip_path=="") {
                    //ファイルが送信されなかったら、更新なしとみなし、現在日時を最終更新日時をとして更新する。
                    $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
                    $user = 'daiyas';
                    $password = 'daiyas';
                    $pdo = new PDO($dsn, $user, $password);
                    $this->updateLastUpdateDate($pdo,$DataItem['受信ＩＤ']);
                    $pdo = null;
                    continue;
                }
                //zipを解凍する
                $zip = new \ZipArchive();
                if ($zip->open($zip_path) === true) {
                    $zip_dir_path = $this->tmp_path."\\".basename($zip_path,".tmp");
                    mkdir($zip_dir_path);
                    $zip->extractTo($zip_dir_path);
                    $zip->close();
                }
                else{
                    //解凍できなかった場合
                    $this->ErrorReceiveList($DataItem['受信ＩＤ'],"エラー","ZIPファイルを展開できません。",basename($zip_path));
                    continue;
                }

                //解凍したファイルを読み込んでSQLを実行
                //1つのzipに含まれているファイルは1トランザクションで処理する
                $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
                $user = 'daiyas';
                $password = 'daiyas';
                $pdo = new PDO($dsn, $user, $password);
                $pdo->beginTransaction();
                foreach (glob($zip_dir_path.'\\*') as $datafile) {
                    if (is_file($datafile)) {
                        if(strpos($datafile,"_dellog") !== false)
                        {
                            //削除
                            $result = $this->DataDelete($pdo,$DataItem['受信ＩＤ'],$datafile);
                        }
                        else
                        {
                            //Insert or Update
                            $result = $this->DataImport($pdo,$DataItem['受信ＩＤ'], $datafile);
                        }
                        if($result===true)
                        {
                            $this->updateLastUpdateDate($pdo,$DataItem['受信ＩＤ'],$response["last_update_date"]);
                            $is_error=false;
                        }
                        else
                        {
                            $is_error=true;
                            $pdo->rollBack();
                            exit;//ループを終了
                        }
                    }
                }
                if (!$is_error) {
                    $pdo->commit();
                }
                $pdo = null;

                //使用したテンポラリファイルを消す
                if (!$is_error) {
                    unlink($zip_path);
                    foreach (glob($zip_dir_path.'\\*') as $sqlfile) {
                        if (is_file($sqlfile)) {
                            unlink($sqlfile);
                        }
                    }
                    rmdir($zip_dir_path);
                }
            }
        }
        catch (Exception $exception) {
            $err_receive_id = isset($DataItem['受信ＩＤ']) ? $DataItem['受信ＩＤ'] : 0;
            $this->ErrorReceiveList($err_receive_id,"エラー",$exception->getMessage(),null);
        }
    }
}
