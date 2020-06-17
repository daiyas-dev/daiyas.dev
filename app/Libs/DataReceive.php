<?php

namespace App\Libs;
use Exception;
use PDO;
use Illuminate\Support\Carbon;

//モバイル・Web受注から社内DBへ取込
class DataReceive
{
    public $tmp_path;

    public function Receive()
    {
        try {
            $this->tmp_path=base_path()."\\tmp";

            //モバイル・Web受注サーバからデータを取得する
            $zip_path = $this->GetResponse();
            if ($zip_path=="") {
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
                $this->ErrorReceiveList("ZIPファイルを展開できません。",null,basename($zip_path),null);
                return;
            }

            //解凍したファイルを読み込んでSQLを実行
            $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
            $user = 'daiyas';
            $password = 'daiyas';
            $pdo = new PDO($dsn, $user, $password);
            $pdo->beginTransaction();
            $is_error=false;
            foreach(glob($zip_dir_path.'\\*') as $sqlfile){
                if(is_file($sqlfile)){
                    $sql = file_get_contents($sqlfile);
                    $pdo->exec($sql);
                    $error_info=$pdo->errorInfo();
                    if($error_info[0]!="00000" || $error_info[1]!=null || $error_info[2]!=null)
                    {
                        //SQLを実行してエラーが発生した場合
                        $err_file_name=basename($zip_dir_path).'\\'.basename($sqlfile);
                        $this->ErrorReceiveList("SQL実行エラー",$error_info[1]." ".$error_info[2],$err_file_name,$sql);
                        $is_error=true;
                        break;
                    }
                }
            }
            if ($is_error) {
                $pdo->rollback();
            }
            else{
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
                    rmdir($zip_dir_path);
                }
            }
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * 指定のURLからzipファイルを取得する
     * @param void
     * @return 取得したzipファイルのフルパス
     */
    private function GetResponse()
    {
        try {
            //WebAPIからレスポンスを取得する
            $url = "http://192.168.1.210/hellolaravel/public/api/MobileDataSend";

            $last_update_date = Carbon::now()->format('YmdHis');
            $post_data = array(
                'LastUpdateDate' => $last_update_date
            );

            // cURLセッションを初期化
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url); // 取得するURLを指定
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 実行結果を文字列で返す
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // サーバー証明書の検証を行わない

            // URLの情報を取得
            $result = curl_exec($ch);
            $curl_error=curl_error($ch);

            // セッションを終了
            curl_close($ch);
            echo 'RETURN:' . $result;

            //エラーチェック
            if($curl_error!="")
            {
                $this->ErrorReceiveList("接続エラー",$curl_error,null,null);
                return "";
            }

            //取得したレスポンスをファイルにする
            $tmp_file = tempnam($this->tmp_path, "zip");
            if ($file_handle = fopen($tmp_file, "w")) {
                // 書き込み
                fwrite($file_handle, base64_decode($result));
                // ファイルを閉じる
                fclose($file_handle);
            }
            return $tmp_file;
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * モバイル受信エラーテーブルに送信フラグを書き込む
     * @param エラー理由
     * @param エラーメッセージ(エラー発生時に取得したメッセージをそのまま保存する)
     * @param 受信ファイル名(パスなし)
     * @param 受信SQL
     * @return void
     */
    private function ErrorReceiveList($description,$message=null,$file_name=null,$sql=null)
    {
        try {
            //モバイル送信リストに登録する
            $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
            $user = 'daiyas';
            $password = 'daiyas';
            $pdo = new PDO($dsn, $user, $password);

            //試行回数を取得
            $next_id_Sql = "
                    SELECT
                        MAX(受信ＩＤ)+1 AS NEXT_ID
                    FROM モバイル受信エラー
                ";
            $stmt = $pdo->query($next_id_Sql);
            $receive_id = $stmt->fetch()["NEXT_ID"];
            $receive_id = $receive_id==null ? 1 : $receive_id;

            //呼出元を取得
            $esc_file_name = $file_name==null ? 'null' : "'$file_name'";
            $esc_message = $message==null ? 'null' : "'" . str_replace("'","''",$message) . "'";
            $esc_sql = $sql==null ? 'null' : "'" . str_replace("'","''",$sql). "'";
            $mr_sql="INSERT INTO モバイル受信エラー(
                    受信ＩＤ
                   ,エラー発生日時
                   ,受信ファイル名
                   ,エラー理由
                   ,エラーメッセージ
                   ,SQL
                   )VALUES(
                     $receive_id
                    ,GETDATE()
                    ,$esc_file_name
                    ,'$description'
                    ,$esc_message
                    ,$esc_sql
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
