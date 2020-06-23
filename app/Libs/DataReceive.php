<?php

namespace App\Libs;
use Exception;
use PDO;
use Illuminate\Support\Carbon;

//モバイル・Web受注から社内DBへ取込
class DataReceive
{
    public $tmp_path;
    public $ConversionMap;

    public function Receive()
    {
        try {
            //マッピング情報を読み込む
            $this->ConversionMap = json_decode(file_get_contents(public_path()."/dbmapping/pwa.txt"),true);

            //作業用のフォルダを作成する
            $this->tmp_path=base_path()."\\tmp";
            if(!file_exists($this->tmp_path))
            {
                mkdir($this->tmp_path);
            }

            $sql ="
                    SELECT 受信ＩＤ,テーブル名,最終更新日時
                      FROM モバイル受信リスト
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
                    return;
                }
                $mv_table_name=$this->ConversionMap[$DataItem['テーブル名']]['TableName'];

                //モバイル・Web受注サーバからデータを取得する
                $response = $this->GetResponse($DataItem['受信ＩＤ'],$mv_table_name,$DataItem['最終更新日時']);
                if($response=="") {
                    return;
                }
                if(!isset($response["file_path"])) {
                    return;
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
                    $this->ErrorReceiveList($DataItem['受信ＩＤ'],"エラー","ZIPファイルを展開できません。",basename($zip_path));
                    return;
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
    /**
     * 指定のURLからzipファイルを取得する
     * @param  int      受信ID
     * @param  string   モバイルDBのテーブル名
     * @param  datetime 最終更新日時。nullの場合は全件取得
     * @return array    file_path=取得したzipファイルのフルパス,last_update_date=最終更新日時
     */
    private function GetResponse($receive_id,$table_name,$last_update_date)
    {
        try {
            //WebAPIからレスポンスを取得する
            //TODO:テスト用URL(NEW社内)
            $url = "http://192.168.1.210/hellolaravel/public/api/mobiledatasend";
            //TODO:本番URL
            $url="http://52.197.70.172/api/mobiledatasend";

            $post_data = array(
                 'TableName'=> $table_name
                ,'LastUpdateDate' => $last_update_date
            );

            // cURLセッションを初期化
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url); // 取得するURLを指定
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 実行結果を文字列で返す
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // サーバー証明書の検証を行わない

            //Post実行
            $result = curl_exec($ch);
            $curl_error=curl_error($ch);

            // セッションを終了
            curl_close($ch);
            //echo 'RETURN:' . $result;

            //エラーチェック
            if($curl_error!="")
            {
                $this->ErrorReceiveList($receive_id,"接続エラー",$curl_error,null);
                return "";
            }
            $arrResult=json_decode($result);
            if($arrResult==null)
            {
                $this->ErrorReceiveList($receive_id,"接続エラー",$curl_error,null);
                return "";
            }
            else
            {
                if ($arrResult->result==1)
                {
                    $tmp_file="";
                    if ($arrResult->FileData!=="")
                    {
                        //取得したレスポンスをファイルにする
                        $tmp_file = tempnam($this->tmp_path, "zip");
                        if ($file_handle = fopen($tmp_file, "w")) {
                            // 書き込み
                            fwrite($file_handle, base64_decode($arrResult->FileData));
                            // ファイルを閉じる
                            fclose($file_handle);
                        }
                    }
                    return array("file_path"=>$tmp_file,"last_update_date"=>$arrResult->LastUpdateDate);
                }
                else{
                    $this->ErrorReceiveList($receive_id,"エラー",base64_decode($arrResult->message),null);
                    return "";
                }
            }
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * ファイルを読み取って、所定のテーブルに格納する
     * @param object (参照)トランザクション
     * @param int    受信ID
     * @param string データファイルフルパス
     * @return bool  処理結果 true=成功 / false=エラー有り
     */
    private function DataImport(&$pdo,$receive_id,$data_file_path)
    {
        try {
            //テーブル名を取得
            $table_name = basename($data_file_path,".txt");
            $field_list=null;

            //マッピング情報を取得
            $field_list=$this->getMapping($table_name);
            $cnv_table_name=$field_list['TableName'];

            //1レコードごとにデータを更新
            $table_data = json_decode(file_get_contents($data_file_path),true);
            $new_pk=array();
            $new_data=array();
            foreach($table_data as $record)
            {
                //列情報を取得
                $ret = $this->getRowData($record,$field_list);
                $new_pk=$ret['key'];
                $new_data=$ret['val'];

                //同一情報が存在するか確認
                $where="";
                foreach($new_pk as $key => $val)
                {
                    $where .= " AND $key = '$val'";
                }
                $where=substr($where,5);
                $stmt = $pdo->query("SELECT COUNT(*) AS CNT FROM $cnv_table_name WHERE $where");
                $count = $stmt->fetch()["CNT"];

                //更新実施
                if(0<$count)
                {
                    //UPDATE
                    $values="";
                    foreach($new_data as $key=>$val)
                    {
                        $q_val = ($val===NULL || $val==='') ? "null" : "'$val'";
                        $values .= ", $key = $q_val";
                    }
                    $values=substr($values,1);
                    $sql="UPDATE $cnv_table_name SET $values WHERE $where";
                    $pdo->exec($sql);
                }
                else
                {
                    //INSERT
                    $fields="";
                    $values="";
                    foreach($new_data as $key=>$val)
                    {
                        $fields .= ", $key";
                        $q_val = ($val===NULL || $val==='') ? "null" : "'$val'";
                        $values .= ", $q_val";
                    }
                    $fields=substr($fields,1);
                    $values=substr($values,1);
                    $sql="insert into $cnv_table_name ( $fields )values( $values )";
                    $pdo->exec($sql);
                }
                $error_info=$pdo->errorInfo();
                if($error_info[0]!="00000" || $error_info[1]!=null || $error_info[2]!=null)
                {
                    //SQLを実行してエラーが発生した場合
                    $this->ErrorReceiveList($receive_id,"SQL実行エラー",$error_info[1]." ".$error_info[2] ." ".$sql,$data_file_path);
                    return false;
                }
            }
            return true;
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * ファイルを読み取って、所定のテーブルのレコードを削除する
     * @param object (参照)トランザクション
     * @param int    受信ID
     * @param string データファイルフルパス
     * @return bool  処理結果 true=成功 / false=エラー有り
     */
    private function DataDelete(&$pdo,$receive_id,$data_file_path)
    {
        try {
            //テーブル名を取得
            $table_name = basename($data_file_path,"_dellog.txt");
            $field_list=null;

            //マッピング情報を取得
            $field_list=$this->getMapping($table_name);
            $cnv_table_name=$field_list['TableName'];

            //1レコードごとにデータを削除
            $table_data = json_decode(file_get_contents($data_file_path),true);
            $new_pk=array();
            $new_data=array();
            foreach($table_data as $record)
            {
                //列情報を取得
                $ret = $this->getRowData($record,$field_list);
                $new_pk=$ret['key'];

                //削除実施
                $where="";
                foreach($new_pk as $key => $val)
                {
                    $where .= " AND $key = '$val'";
                }
                $where=substr($where,5);
                $sql="DELETE FROM $cnv_table_name WHERE $where";
                $pdo->exec($sql);
                $error_info=$pdo->errorInfo();
                if($error_info[0]!="00000" || $error_info[1]!=null || $error_info[2]!=null)
                {
                    //SQLを実行してエラーが発生した場合
                    $this->ErrorReceiveList($receive_id,"SQL実行エラー",$error_info[1]." ".$error_info[2] ." ".$sql,$data_file_path);
                    return false;
                }
            }
            return true;
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * ファイルを読み取って、所定のテーブルのレコードを削除する
     * @param object (参照)トランザクション
     * @param int    受信ID
     * @param string 最終更新日時
     * @return void
     */
    private function updateLastUpdateDate(&$pdo,$receive_id,$last_update_date=null)
    {
        //最終更新日を更新する
        $q_last_update_date = $last_update_date==null ? Carbon::now()->format('Y/m/d H:i:s') : $last_update_date;

        $stmt = $pdo->query("SELECT COUNT(*) AS CNT FROM モバイル受信リスト WHERE 受信ＩＤ=$receive_id AND 最終更新日時<'$q_last_update_date'");
        $count = $stmt->fetch()["CNT"];
        if (0<$count)
        {
            $sql="UPDATE モバイル受信リスト SET 最終更新日時='$q_last_update_date' WHERE 受信ＩＤ=$receive_id";
            $pdo->exec($sql);
        }
    }
    /**
     * 指定のテーブルのマッピング情報を返す
     * @param string テーブル名
     * @return array マッピング情報
     */
    private function getMapping($table_name)
    {
        foreach($this->ConversionMap as $key=>$map)
        {
            if(strtolower($map['TableName'])===strtolower($table_name))
            {
                $field_list=$map;
                //テーブル名を保持する(名称の都合でTablenameの値を差し替える)
                $field_list['MVTableName']=$map['TableName'];
                $field_list['TableName']=$key;
                break;
            }
        }
        if($field_list==null)
        {
            throw new Exception("テーブルマッピング情報がありません。");
        }

        //モバイルDB->社内用マッピングを追加する
        foreach($field_list['PrimaryKey'] as $val)
        {
            $mobile_field_name=$field_list['Field'][$val];
            $field_list['MVPrimaryKey'][]=$mobile_field_name;
        }
        foreach($field_list['Field'] as $key=>$val)
        {
            $field_list['MVField'][$val]=$key;
        }
        return $field_list;
    }
    /**
     * 行データとマッピング情報を受取り、キーと値の配列を戻す
     * @param array 行データ
     * @param array マッピング情報
     * @return array キーと値の配列
     */
    private function getRowData($record,$field_list)
    {
        foreach($record as $field_name => $field_value)
        {
            if(array_key_exists($field_name,$field_list['MVField']))
            {
                $new_key=$field_list['MVField'][$field_name];
                if(in_array($field_name,$field_list['MVPrimaryKey'],true))
                {
                    $new_pk[$new_key]=$field_value;
                }
                $new_data[$new_key]=$field_value;
            }
        }
        return array("key"=>$new_pk,"val"=>$new_data);
    }
    /**
     * モバイル受信エラーテーブルに送信フラグを書き込む
     * @param string エラー理由
     * @param string エラーメッセージ(エラー発生時に取得したメッセージをそのまま保存する)
     * @return void
     */
    private function ErrorReceiveList($receive_id,$description,$message=null,$file_name=null)
    {
        try {
            //モバイル送信リストに登録する
            $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
            $user = 'daiyas';
            $password = 'daiyas';
            $pdo = new PDO($dsn, $user, $password);

            //試行回数を取得
            $seq_no=null;
            $next_seq_Sql = "
                    SELECT
                    MAX(試行回数)+1 AS NEXT_SEQ
                    FROM モバイル受信エラー
                    WHERE 受信ＩＤ = $receive_id
                ";
            $stmt = $pdo->query($next_seq_Sql);
            if (!!$stmt) {
                $seq_no = $stmt->fetch()["NEXT_SEQ"];
            }
            $seq_no = $seq_no==null ? 1 : $seq_no;

            //呼出元を取得
            $esc_file_name = $file_name==null ? 'null' : "'$file_name'";
            $esc_message = $message==null ? 'null' : "'" . str_replace("'","''",$message) . "'";
            $mr_sql="INSERT INTO モバイル受信エラー(
                    受信ＩＤ
                   ,試行回数
                   ,エラー発生日時
                   ,受信ファイル名
                   ,エラー理由
                   ,エラーメッセージ
                   )VALUES(
                     $receive_id
                    ,$seq_no
                    ,GETDATE()
                    ,$esc_file_name
                    ,'$description'
                    ,$esc_message
                   )
                ";
            $pdo->beginTransaction();
            $pdo->exec($mr_sql);
            $pdo->commit();
            $pdo = null;
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
}
