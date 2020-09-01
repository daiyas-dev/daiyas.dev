<?php

namespace App\Libs;
use Exception;
use PDO;
use DB;
use App\Models\注文データ;
use Illuminate\Support\Carbon;

//Web受注から社内DBへ取込
class WebOrderDataReceive extends DataReceiveBase
{
    public $receive_data_enum=array("WebUserMaster"=>1,"WebOrderData"=>2);//Web受注受信リストの受信IDに対応する処理の定義
    function __construct()
    {
        parent::__construct();
        $this->target_server=$this->target_server_enum["WebOrder"];
        $this->ReadMap();
    }
    public function Receive()
    {
        //TODO:テスト用URL(NEW社内)
        $url = "http://192.168.1.211/hellolaravel/public/api/weborderdatasend";
        //TODO:本番URL
        $url="https://daiyas-order.com/api/weborderdatasend";

        try {
            $sql ="
                    SELECT 受信ＩＤ,データ名,最終更新日時
                      FROM Web受注受信リスト
                     ORDER BY 受信ＩＤ
                  ";
            $DataList=DB::select($sql);

            //1テーブルごとにリクエストを投げて処理する
            foreach ($DataList as $DataItem)
            {
                switch($DataItem->受信ＩＤ)
                {
                    case $this->receive_data_enum['WebUserMaster']:
                    {
                        $this->getWebUserMaster($url,$DataItem->受信ＩＤ,$DataItem->最終更新日時);
                        break;
                    }
                    case $this->receive_data_enum['WebOrderData']:
                    {
                        $this->getWebOrder($url,$DataItem->受信ＩＤ,$DataItem->最終更新日時);
                        break;
                    }
                }
            }
        }
        catch (Exception $exception) {
            $err_receive_id = isset($DataItem->受信ＩＤ) ? $DataItem->受信ＩＤ : 0;
            $this->ErrorReceiveList($err_receive_id,"エラー",$exception->getMessage(),null);
        }
    }
    /**
     * Web得意先利用者マスタを取り込む
        1.  AWS: Web利用者マスタ[WebUserMaster] -> 社内: Web受注得意先利用者マスタ
     */
    private function getWebUserMaster($url,$receive_id,$last_update_date)
    {
        //モバイル・Web受注サーバからデータを取得する
        $response = $this->GetResponse_WebOrderData($url,$receive_id,$this->receive_data_enum['WebUserMaster'],$last_update_date);
        if($response=="") {
            return;
        }
        if(!isset($response["file_path"])) {
            return;
        }
        $zip_path = $response["file_path"];
        if ($zip_path=="") {
            //ファイルが送信されなかったら、更新なしとみなし、現在日時を最終更新日時として更新する。
            $this->updateLastUpdateDateByDB($receive_id);
            return;
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
            $this->ErrorReceiveList($receive_id,"エラー","ZIPファイルを展開できません。",basename($zip_path));
            return;
        }

        //解凍したファイルを読み込んでSQLを実行
        //1つのzipに含まれているファイルは1トランザクションで処理する
        DB::beginTransaction();
        foreach (glob($zip_dir_path.'\\*') as $datafile) {
            if (is_file($datafile)) {
                //テーブル名を取得
                $table_name = basename($datafile, ".txt");

                //マッピング情報を取得
                $field_list=$this->getMapping($table_name);
                $cnv_table_name=$field_list['TableName'];

                //1レコードごとにデータを更新
                $table_data = json_decode(file_get_contents($datafile), true);
                $new_pk=array();
                foreach($table_data as $record)
                {
                    //列情報を取得
                    foreach($record as $field_name => $field_value)
                    {
                        if(in_array($field_name,$field_list['PrimaryKey'],true))
                        {
                            $new_pk[$field_name]=$field_value;
                        }
                    }

                    //同一情報が存在するか確認
                    $where="";
                    foreach($new_pk as $key => $val)
                    {
                        $where .= " AND $key = '$val'";
                    }
                    $where=substr($where,5);
                    $count = DB::selectOne("SELECT COUNT(*) AS CNT FROM $cnv_table_name WHERE $where")->CNT;
                    if($record['削除フラグ']==1)
                    {
                        if (0<$count)
                        {
                            //Deleteする
                            $sql="DELETE FROM $cnv_table_name WHERE $where";
                            DB::delete($sql);
                        }
                    }
                    else
                    {
                        //Insert or Updateする
                        //追加項目を付加
                        $record['修正担当者ＣＤ']="999";
                        $record['修正日']=Carbon::now()->format('Y/m/d H:i:s');
                        if (0<$count)
                        {
                            //UPDATE
                            //TODO: Web受注得意先利用者マスタ.得意先ＣＤはupdate対象外か? WebUserMasterの対応する列不明のため下記update文に未設定
                            $sql="UPDATE $cnv_table_name set 利用者CD='{$record['利用者CD']}',備考1='{$record['備考1']}',修正担当者ＣＤ='{$record['修正担当者ＣＤ']}',修正日='{$record['修正日']}' WHERE $where";
                            DB::update($sql);
                        }
                        else
                        {
                            //INSERT
                            $fields="";
                            $values="";
                            foreach($record as $key=>$val)
                            {
                                if (array_key_exists($key, $field_list['Field']))
                                {
                                    $fields .= ", $key";
                                    $q_val = ($val===null || $val==='') ? "null" : "'$val'";
                                    $values .= ", $q_val";
                                }
                            }
                            $fields=substr($fields,1);
                            $values=substr($values,1);
                            $sql="insert into $cnv_table_name ( $fields )values( $values )";
                            DB::insert($sql);
                        }
                    }
                }
            }
            $result=true;//TODO:
            if($result===true)
            {
                $this->updateLastUpdateDateByDB($receive_id,$response["last_update_date"]);
                $is_error=false;
            }
            else
            {
                $is_error=true;
                DB::rollBack();
                exit;//ループを終了
            }
        }
        if (!$is_error) {
            DB::commit();
        }

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
    /**
     * Web受注データを取り込む
     */
    public function getWebOrder($url,$receive_id,$last_update_date)
    {
        //モバイル・Web受注サーバからデータを取得する
        $response = $this->GetResponse_WebOrderData($url,$receive_id,$this->receive_data_enum['WebOrderData'],$last_update_date);
        if($response=="") {
            return;
        }
        if(!isset($response["file_path"])) {
            return;
        }
        $zip_path = $response["file_path"];
        if ($zip_path=="") {
            //ファイルが送信されなかったら、更新なしとみなし、現在日時を最終更新日時として更新する。
            $this->updateLastUpdateDateByDB($receive_id);
            return;
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
            $this->ErrorReceiveList($receive_id,"エラー","ZIPファイルを展開できません。",basename($zip_path));
            return;
        }

        //解凍したファイルを読み込んでSQLを実行
        //1つのzipに含まれているファイルは1トランザクションで処理する
        DB::beginTransaction();
        try {
            //データをクリア
            $sql = "DELETE FROM Web受注データ利用者別詳細 WHERE Web受注ID IN (SELECT Web受注ID FROM Web受注データ WHERE 配送日 >= CONVERT(DATE, GETDATE()))";
            DB::delete($sql);
            $sql = "DELETE FROM Web受注データ利用者情報 WHERE Web受注ID IN (SELECT Web受注ID FROM Web受注データ WHERE 配送日 >= CONVERT(DATE, GETDATE()))";
            DB::delete($sql);
            $sql = "DELETE FROM Web受注データ WHERE 配送日 >= CONVERT(DATE, GETDATE())";
            DB::delete($sql);

            // $WebOrderList=[];
            $datafile=$zip_dir_path.'\\WebOrderData.txt';
            if (file_exists($datafile)) {
                $this->SaveWebOrder($datafile);
            }
            $datafile=$zip_dir_path.'\\WebOrderUserData.txt';
            if (file_exists($datafile)) {
                $this->SaveWebOrderUser($datafile);
            }
            $datafile=$zip_dir_path.'\\WebOrderInfoData.txt';
            if (file_exists($datafile)) {
                $this->SaveWebOrderInfo($datafile);
            }

            $this->updateLastUpdateDateByDB($receive_id, null);
            DB::commit();

            //使用したテンポラリファイルを消す
            unlink($zip_path);
            foreach (glob($zip_dir_path.'\\*') as $sqlfile) {
                if (is_file($sqlfile)) {
                    unlink($sqlfile);
                }
            }
            rmdir($zip_dir_path);
        }
        catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
    /**
     * Web受注データを更新する
        2.  AWS: WebOrderData -> 社内: Web受注データ
            社内側Web受注データの更新については、Web得意先ＣＤ及び配送日が一致するレコードが存在する場合はupdateもしくはdelete
            存在しない場合は、Web受注IDを最大 + 1で払い出してinsert
     * @param  string   読み込むファイル
     * @return array    作成・更新対象(Web受注ID,配送日の配列),削除対象(Web受注ID,配送日の配列)
     */
    private function SaveWebOrder($datafile)
    {
        //1レコードごとにデータを更新
        $table_data = json_decode(file_get_contents($datafile), true);
        foreach ($table_data as $record) {
            //取込済Web受注ID確認
            $sql = "
                SELECT
                    注文データ.Web受注ID
                FROM
                    注文データ
                    INNER JOIN Web受注得意先マスタ
                        ON Web受注得意先マスタ.得意先ＣＤ=注文データ.得意先ＣＤ
                WHERE
                    注文データ.配送日='{$record['配送日']}'
                AND Web受注得意先マスタ.Web得意先ＣＤ='{$record['Web得意先ＣＤ']}'
            ";
            $WebOrder = DB::selectOne($sql);
            $WebOrderID = 0;

            if (isset($WebOrder->Web受注ID)) {
                $WebOrderID = $WebOrder->Web受注ID;
            } else {
                //Web受注ID払出
                $sql = "select isnull(max(Web受注ID)+1, 1) as NewID from Web受注データ";
                $WebOrder = DB::selectOne($sql);
                $WebOrderID = $WebOrder->NewID;
                while(true) {
                    $sql = "select count(*) as CNT from 注文データ where Web受注ID=$WebOrderID";
                    $count = DB::selectOne($sql);
                    if ($count->CNT == 0) {
                        break;
                    } else {
                        $WebOrderID += 1;
                    }
                }
            }

            //insert
            $sql="insert into Web受注データ(
                     Web受注ID
                    ,Web得意先ＣＤ
                    ,配送日
                    ,注文日時
                    ,修正担当者ＣＤ
                    ,修正日
                )values(
                     $WebOrderID
                    ,'{$record['Web得意先ＣＤ']}'
                    ,'{$record['配送日']}'
                    ,'{$record['注文日時']}'
                    ,999
                    ,getdate()
                )
            ";
            DB::insert($sql);

        }
        return;
    }
    /**
     * Web受注データ利用者情報を更新する
        3.  AWS: WebOrderData -> 社内: Web受注データ利用者情報
            社内側Web受注データ利用者情報の更新については、まずWeb得意先ＣＤ及び配送日でWeb受注データからWeb受注IDを取得し
            Web受注IDＣＤ及び注文IDが一致するレコードが存在する場合はupdateもしくはdelete、存在しない場合はinsert
     * @param  string   読み込むファイル
     */
    private function SaveWebOrderUser($datafile)
    {
        //1レコードごとにデータを更新
        $table_data = json_decode(file_get_contents($datafile), true);
        foreach ($table_data as $record) {
            //Web受注IDを取得
            $sql="select J1.Web受注ID from Web受注データ J1 where J1.Web得意先ＣＤ='{$record['Web得意先ＣＤ']}' and J1.配送日='{$record['配送日']}'";

            $WebOrder = DB::selectOne($sql);
            $WebOrderID = $WebOrder->Web受注ID;

            //insert
            $sql="insert into Web受注データ利用者情報(
                     Web受注ID
                    ,注文ID
                    ,利用者ID
                    ,利用者CD
                    ,備考ID
                    ,修正担当者ＣＤ
                    ,修正日
                )values(
                    $WebOrderID
                    ,{$record['注文ID']}
                    ,{$record['利用者ID']}
                    ,'{$record['利用者CD']}'
                    ,null
                    ,999
                    ,getdate()
                )
            ";
            DB::insert($sql);
        }
    }
    /**
     * Web受注データ利用者別詳細を更新する
        4.  AWS: WebOrderData + WebOrderInfoData -> 社内: Web受注データ利用者別詳細
            AWSでのWeb得意先の利用者毎の注文の商品レベルの詳細と対応
            社内側Web受注データ利用者情報の更新については、まずWeb得意先ＣＤ及び配送日でWeb受注データからWeb受注IDを取得し
            Web受注IDＣＤ及び注文IDが一致するレコードが存在する場合はupdateもしくはdelete、存在しない場合はinsert
     * @param  string   読み込むファイル
     */
    private function SaveWebOrderInfo($datafile)
    {
        //1レコードごとにデータを更新
        $table_data = json_decode(file_get_contents($datafile), true);
        foreach ($table_data as $record) {
            //Web受注IDを取得
            $sql="select J1.Web受注ID from Web受注データ J1 where J1.Web得意先ＣＤ='{$record['Web得意先ＣＤ']}' and J1.配送日='{$record['配送日']}'";

            $WebOrder = DB::selectOne($sql);
            $WebOrderID = $WebOrder->Web受注ID;

            //insert
            $destination_id=($record['届け先ID']===null || $record['届け先ID']==='') ? "null" : $record['届け先ID'];
            $sql="insert into Web受注データ利用者別詳細(
                    Web受注ID
                    ,注文ID
                    ,注文情報ID
                    ,注文日時
                    ,商品ＣＤ
                    ,単価
                    ,現金個数
                    ,現金金額
                    ,掛売個数
                    ,掛売金額
                    ,届け先ID
                    ,修正担当者ＣＤ
                    ,修正日
                    )values(
                    $WebOrderID
                    , {$record['注文ID']}
                    , {$record['注文情報ID']}
                    ,'{$record['注文日時']}'
                    , {$record['商品ＣＤ']}
                    , {$record['単価']}
                    , {$record['現金個数']}
                    , {$record['現金金額']}
                    , {$record['掛売個数']}
                    , {$record['掛売金額']}
                    , $destination_id
                    , 999
                    , getdate()
                )
            ";
            DB::insert($sql);
        }
    }
    /**
     * 社内側注文データの更新
        5.  社内側注文データの更新
                ・Web受注IDで対応するWeb受注データが存在しなくなった場合は、該当注文データを削除
                ・DAI01032ControllerのSaveOrderFromWebで発行しているselectの結果を基にinsert/update
                　現在の注文データでselect結果と対応しないレコードは削除
                　※最初エースを1個注文していたけど、エース大盛に変更した場合などを想定
     * @param  integer  WebOrderId
     * @param  string   DeliveryDate
     */
    private function SaveOrderFromWeb($WebOrderId,$DeliveryDate)
    {
        //モバイルsv更新用
        $MInsertList = [];
        $MUpdateList = [];

        $GetOrderSQL = "
			WITH WEB AS (
				SELECT DISTINCT
                    Web受注ID
                    ,Web得意先ＣＤ
					,配送日
				FROM
					Web受注データ
				WHERE
					Web受注ID=$WebOrderId
				AND 配送日='$DeliveryDate'
			)
            SELECT
				0 AS 注文区分
				,FORMAT(MAX(注文日時), 'yyyy-MM-dd') AS 注文日付
				,FORMAT(MAX(注文日時), 'HH:mm:ss') AS 注文時間
				,部署CD AS 部署ＣＤ
				,得意先ＣＤ
				,配送日
				, 0 AS 明細行Ｎｏ
                ,商品ＣＤ
				,商品区分
				,0 AS 入力区分
                ,SUM(現金個数) AS 現金個数
                ,SUM(現金金額) AS 現金金額
                ,SUM(掛売個数) AS 掛売個数
                ,SUM(掛売金額) AS 掛売金額
				,'' AS 備考１
				,'' AS 備考２
				,'' AS 備考３
				,'' AS 備考４
				,'' AS 備考５
                ,単価 AS 予備金額１
                ,0 AS 予備金額２
                ,0 AS 予備ＣＤ１
                ,0 AS 予備ＣＤ２
                ,MAX(修正日) AS 修正日
				,(
					STRING_AGG(
						IIF(
							((売掛現金区分=0 AND 現金個数 > 0) OR (売掛現金区分=1 AND 掛売個数 > 0))
							AND
							(備考 IS NOT NULL OR 届け先名 IS NOT NULL),
							利用者CD + ':'
								+ IIF(届け先名 IS NOT NULL, ' ' + 届け先名, '')
								+ IIF(備考 IS NOT NULL, ' ' + 備考, '')
						,NULL)
						,CHAR(13) + CHAR(10)
					)
				) AS 特記_Web受注
                ,特記_社内
                ,特記_配送
                ,特記_通知
                ,Web受注ID
            FROM
			(
				SELECT
					WEB.Web受注ID
					,WEB.Web得意先ＣＤ
					,WEBTOK.得意先ＣＤ
					,TOK.得意先名
					,TOK.部署CD
                    ,TOK.売掛現金区分
                    ,TOK.備考１ AS 特記_社内
                    ,TOK.備考２ AS 特記_配送
                    ,TOK.備考３ AS 特記_通知
					,WEB.配送日
					,MAX(DETAILS.注文日時) OVER(PARTITION BY WEB.Web受注ID, TOK.得意先名) AS 注文日時
					,DETAILS.商品ＣＤ
					,PM.商品名
					,PM.商品区分
					,DETAILS.単価
					,IIF(TOK.売掛現金区分=0, DETAILS.現金個数, 0) AS 現金個数
					,IIF(TOK.売掛現金区分=0, DETAILS.現金金額, 0) AS 現金金額
					,IIF(TOK.売掛現金区分=1, DETAILS.掛売個数, 0) AS 掛売個数
					,IIF(TOK.売掛現金区分=1, DETAILS.掛売金額, 0) AS 掛売金額
					,USERS.利用者CD
					,PLACE.届け先名
					,MEMO.文言 AS 備考
					,DETAILS.修正日
				FROM
					WEB
					LEFT OUTER JOIN Web受注得意先マスタ WEBTOK
						ON  WEBTOK.Web得意先ＣＤ=WEB.Web得意先ＣＤ
					LEFT OUTER JOIN 得意先マスタ TOK
						ON  TOK.得意先ＣＤ=WEBTOK.得意先ＣＤ
					LEFT OUTER JOIN Web受注データ利用者情報 USERS
						ON  USERS.Web受注ID=WEB.Web受注ID
					LEFT OUTER JOIN Web受注データ利用者別詳細 DETAILS
						ON  DETAILS.Web受注ID=USERS.Web受注ID
						AND DETAILS.注文ID=USERS.注文ID
					LEFT OUTER JOIN 商品マスタ PM
						ON  PM.商品ＣＤ=DETAILS.商品ＣＤ
					LEFT OUTER JOIN Web受注得意先利用者マスタ USERM
						ON  USERM.Web得意先ＣＤ=WEB.Web得意先ＣＤ
						AND USERM.利用者ID=USERS.利用者ID
					LEFT OUTER JOIN 得意先マスタ TM
						ON  TM.得意先ＣＤ=USERM.得意先ＣＤ
					LEFT OUTER JOIN Web受注得意先備考マスタ MEMO
						ON  MEMO.Web得意先ＣＤ=WEB.Web得意先ＣＤ
						AND MEMO.備考ID=USERS.備考ID
					LEFT OUTER JOIN Web受注得意先届け先マスタ PLACE
						ON  PLACE.Web得意先ＣＤ=WEB.Web得意先ＣＤ
						AND PLACE.届け先ID=DETAILS.届け先ID
				WHERE
					(TOK.売掛現金区分=0 AND DETAILS.現金個数 > 0)
					OR
					(TOK.売掛現金区分=1 AND DETAILS.掛売個数 > 0)
			) X
			GROUP BY
                Web受注ID
				,部署CD
				,得意先ＣＤ
				,配送日
                ,商品ＣＤ
				,商品区分
                ,単価
                ,特記_社内
                ,特記_配送
                ,特記_通知
            ORDER BY
                得意先ＣＤ
                ,商品ＣＤ
        ";
        $OrderList = DB::select($GetOrderSQL);
        $EditUser="999";
        $EditDate=Carbon::now()->format('Y-m-d H:i:s');
        foreach($OrderList as $rec) {
            $rec = (array) $rec;

            $r = 注文データ::query()
                ->where('注文区分', $rec['注文区分'])
                ->where('部署ＣＤ', $rec['部署ＣＤ'])
                ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                ->where('配送日', $rec['配送日'])
                ->where('商品ＣＤ', $rec['商品ＣＤ'])
                ->get();
            //注文データにWeb受注IDが存在しなかったら処理しない
            if (count($r) == 0) {
                continue;
            }

            //受注データがすでに存在するとき
            $data = $rec;
            $data['修正担当者ＣＤ'] = $EditUser;
            $data['修正日'] = $EditDate;

            if (!!$data['特記_Web受注']) {
                if (count($r) == 0) {
                    $data['特記_社内用'] = ($data['特記_社内'] != '' ? $data['特記_社内'] . "\r\n" : '') . $data['特記_Web受注'];
                    $data['特記_配送用'] = ($data['特記_配送'] != '' ? $data['特記_配送'] . "\r\n" : '') . $data['特記_Web受注'];
                    $data['特記_通知用'] = $data['特記_通知'];
                } else {
                    if (preg_match('/' . $data['特記_Web受注'] . '/s', $r[0]->特記_社内用) == 0) {
                        $data['特記_社内用'] = (!!$r[0]->特記_社内用 ? $r[0]->特記_社内用 . "\r\n" : '') . $data['特記_Web受注'];
                    } else {
                        $data['特記_社内用'] = $r[0]->特記_社内用;
                    }
                    if (preg_match('/' . $data['特記_Web受注'] . '/s', $r[0]->特記_配送用) == 0) {
                        $data['特記_配送用'] = (!!$r[0]->特記_配送用 ? $r[0]->特記_配送用 . "\r\n" : '') . $data['特記_Web受注'];
                    } else {
                        $data['特記_配送用'] = $r[0]->特記_配送用;
                    }
                    $data['特記_通知用'] = $r[0]->特記_通知用;
                }
            }

          //補正
          $data['備考１'] = $data['備考１'] ?? '';
          $data['備考２'] = $data['備考２'] ?? '';
          $data['備考３'] = $data['備考３'] ?? '';
          $data['備考４'] = $data['備考４'] ?? '';
          $data['備考５'] = $data['備考５'] ?? '';
          $data['特記_社内用'] = $data['特記_社内用'] ?? '';
          $data['特記_配送用'] = $data['特記_配送用'] ?? '';
          $data['特記_通知用'] = $data['特記_通知用'] ?? '';

          unset($data['特記_Web受注']);
          unset($data['特記_社内']);
          unset($data['特記_配送']);
          unset($data['特記_通知']);

          $data['明細行Ｎｏ'] = $r[0]->明細行Ｎｏ;

        注文データ::query()
            ->where('注文区分', $data['注文区分'])
            ->where('部署ＣＤ', $data['部署ＣＤ'])
            ->where('得意先ＣＤ', $data['得意先ＣＤ'])
            ->where('配送日', $data['配送日'])
            ->where('明細行Ｎｏ', $data['明細行Ｎｏ'])
            ->update($data);
            //モバイルsv更新用
            array_push($MUpdateList, $data);
        }
        return [
            "MInsertList" => $MInsertList,
            "MUpdateList" => $MUpdateList,
        ];
    }
    /**
     * 指定のURLからzipファイルを取得する
     * @param  string   URL
     * @param  int      受信ID
     * @param  string   取得するデータのID
     * @param  datetime 最終更新日時。nullの場合は全件取得
     * @return array    file_path=取得したzipファイルのフルパス,last_update_date=最終更新日時
     */
    private function GetResponse_WebOrderData($url,$receive_id,$data_id,$last_update_date)
    {
        try {
            $post_data = array(
                 'DataID'=> $data_id
                ,'LastUpdateDate' => $last_update_date
            );
            return $this->GetResponse($url,$receive_id,$post_data);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    private function comment_area()
    {
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
        return "a";
    }
}
