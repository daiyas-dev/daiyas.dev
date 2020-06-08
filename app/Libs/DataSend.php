<?php

namespace App\Libs;
use Exception;
use PDO;
use Illuminate\Support\Carbon;

//社内DBからモバイル・Web受注へ更新用SQLを送信する
class DataSend
{
    public function Send($send_id = null)
    {
        try
        {
            $where_send_id ="";
            if($send_id != null)
            {
                $where_send_id = " AND 送信ＩＤ = $send_id ";
            }

            $sql ="
                    SELECT 送信ＩＤ,SQL
                      FROM モバイル送信リスト
                     WHERE 送信済フラグ<>1
                           $where_send_id
                     ORDER BY 送信ＩＤ
                  ";
            $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
            $user = 'daiyas';
            $password = 'daiyas';
            $pdo = new PDO($dsn, $user, $password);
            $stmt = $pdo->query($sql);
            $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;

            foreach($DataList as $DataItem)
            {
                //現在日時を取得(ファイル名に使用)
                $seq_str = Carbon::now()->format('YmdHis') ."_". sprintf('%06d', $DataItem['送信ＩＤ']);
                //SQLをファイルに書き込む
                $tmp_path=base_path() . "\\tmp";
                $sql_file = $tmp_path . "\\" . $seq_str . ".sql";
                if ($file_handle = fopen($sql_file, "w"))
                {
                    // 書き込み
                    fwrite($file_handle, $DataItem['SQL']);
                    // ファイルを閉じる
                    fclose($file_handle);
                }

                //ZIP圧縮する
                $zip_file=$tmp_path . "\\" . $seq_str . ".zip";
                $this->Compress($zip_file,array($sql_file));
                $this->Post($zip_file,$DataItem['送信ＩＤ']);

                //使用したテンポラリファイルを消す
                unlink($zip_file);
                unlink($sql_file);
            }
        }
        catch (Exception $exception)
        {
            throw $exception;
        }
    }
    /**
     * ファイルをzip圧縮する
     * @param zipファイル名
     * @param 圧縮するファイルフルパスの配列
     * @return void
     */
    private function Compress($zip_file_path,$file)
    {
        try {
            $zip = new \ZipArchive();
            $zip->open($zip_file_path, \ZipArchive::CREATE);
            foreach ($file as $f) {
                $zip->addFile($f, basename($f));
            }
            $zip->close();
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * 指定のURLにzipファイルをpostする
     * @param zipファイルフルパス
     * @param 送信ID
     * @return void
     */
    private function Post($zip_file_path,$send_id)
    {
        try
        {
            //WebAPI宛に送信
            $url = "http://localhost:49503/api/Welcome";

            // base64エンコード
            $base64_data = base64_encode(file_get_contents($zip_file_path));
            $post_data = array(
                'FileData' => $base64_data
            );

            // cURLセッションを初期化
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url); // 取得するURLを指定
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // サーバー証明書の検証を行わない

            // セッションを終了
            $result = curl_exec($ch);
            curl_close($ch);
            echo 'RETURN:' . $result;

            if ($result=="\"OK\"") {
                //モバイル送信リストにOKを書き込む
                $this->SuccessSendList($send_id);
            }
            else{
                //モバイル送信エラーを書き込む
                $description="エラー";
                $this->ErrorSendList($send_id,$description,$result);
            }
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * 指定のSQLをモバイル送信リストに登録する
     * @param SQL文
     * @param すぐに実行するか。null以外ならすぐに実行
     * @param 部署CD
     * @param 得意先CD
     * @param コースCD
     * @return void
     */
    public function StoreSendList($sql,$Immediate = null,$busho_cd = null,$customer_cd=null,$course_cd=null)
    {
        try {
            //モバイル送信リストに登録する
            $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
            $user = 'daiyas';
            $password = 'daiyas';
            $pdo = new PDO($dsn, $user, $password);

            //送信IDを取得
            $next_id_Sql = "
                    SELECT
                        MAX(送信ＩＤ)+1 AS NEXT_ID
                    FROM モバイル送信リスト
                ";
            $stmt = $pdo->query($next_id_Sql);
            $send_id = $stmt->fetch()["NEXT_ID"];

            //呼出元を取得
            $arrCallMethod=$this->GetCallMethod();
            $controller_id=$arrCallMethod["ControllerID"];
            $method_name=$arrCallMethod["MethodName"];

            $q_busho_cd   = $busho_cd    == null ? 'null' : $busho_cd;
            $q_customer_cd= $customer_cd == null ? 'null' : $customer_cd;
            $q_course_cd  = $course_cd   == null ? 'null' : $course_cd;
            $esc_sql=str_replace("'","''",$sql);
            $ms_sql="INSERT INTO モバイル送信リスト(
                    送信ＩＤ
                   ,部署ＣＤ
                   ,得意先ＣＤ
                   ,コースＣＤ
                   ,コントローラＩＤ
                   ,メソッド名
                   ,作成日時
                   ,SQL
                   ,送信済フラグ
                   ,送信済日時
                   )VALUES(
                     $send_id
                    ,$q_busho_cd
                    ,$q_customer_cd
                    ,$q_course_cd
                    ,'$controller_id'
                    ,'$method_name'
                    ,GETDATE()
                    ,'$esc_sql'
                    ,0
                    ,null
                   )
                ";
            $pdo->beginTransaction();
            $pdo->exec($ms_sql);
            $pdo->commit();
            $pdo = null;
            if($Immediate!=null)
            {
                //直ちに実行する
                $this->send($send_id);
            }
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * 呼び出し元のコントローラIDとメソッド名を取得する
     * @param void
     * @return array() 呼び出し元コントローラID、メソッド名
     */
    private function GetCallMethod()
    {
        $controller_id="";
        $method_name="";
        $dbg = debug_backtrace();
        for($i=count($dbg)-1;0<=$i;$i--)
        {
            if(array_key_exists('class',$dbg[$i]))
            {
                if(strpos($dbg[$i]["class"],"App\\Http\\Controllers\\")===0)
                {
                    $class_name=str_replace("App\\Http\\Controllers\\","",$dbg[$i]["class"]);
                    if(preg_match("/DAI/",$class_name))
                    {
                        $controller_id=str_replace("Controller","",$class_name);
                        if (array_key_exists('function', $dbg[$i]))
                        {
                            $method_name=$dbg[$i]["function"];
                            break;
                        }
                    }
                }
            }
        }
        return array("ControllerID"=>$controller_id,"MethodName"=>$method_name);
    }

    /**
     * モバイル送信リストに送信フラグを書き込む
     * @param 送信ID
     * @return void
     */
    private function SuccessSendList($send_id)
    {
        try {
            $sql="UPDATE モバイル送信リスト SET 送信済フラグ=1,送信済日時=GETDATE() WHERE 送信ＩＤ=$send_id";
            $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
            $user = 'daiyas';
            $password = 'daiyas';
            $pdo = new PDO($dsn, $user, $password);
            $pdo->beginTransaction();
            $pdo->exec($sql);
            $pdo->commit();
            $pdo = null;
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * モバイル送信エラーテーブルに送信フラグを書き込む
     * @param 送信ID
     * @param エラー理由
     * @param エラーメッセージ(エラー発生時に取得したメッセージをそのまま保存する)
     * @return void
     */
    private function ErrorSendList($send_id,$description,$message)
    {
        try {
            //モバイル送信リストに登録する
            $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
            $user = 'daiyas';
            $password = 'daiyas';
            $pdo = new PDO($dsn, $user, $password);

            //試行回数を取得
            $next_seq_Sql = "
                    SELECT
                        MAX(試行回数)+1 AS NEXT_SEQ
                    FROM モバイル送信エラー
                    WHERE 送信ＩＤ = $send_id
                ";
            $stmt = $pdo->query($next_seq_Sql);
            $seq_no = $stmt->fetch()["NEXT_SEQ"];
            $seq_no = $seq_no==null ? 1 : $seq_no;

            //呼出元を取得
            $esc_message=str_replace("'","''",$message);
            $ms_sql="INSERT INTO モバイル送信エラー(
                    送信ＩＤ
                   ,試行回数
                   ,エラー発生日時
                   ,エラー理由
                   ,エラーメッセージ
                   )VALUES(
                     $send_id
                    ,$seq_no
                    ,GETDATE()
                    ,'$description'
                    ,'$esc_message'
                   )
                ";
            $pdo->beginTransaction();
            $pdo->exec($ms_sql);
            $pdo->commit();
            $pdo = null;
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
}
