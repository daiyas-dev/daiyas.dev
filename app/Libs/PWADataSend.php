<?php

namespace App\Libs;
use Exception;
use PDO;
use Illuminate\Support\Carbon;

//社内DBからAWS(PWA)へ更新用SQLを送信する
class PWADataSend
{
    public function Send($send_id = null)
    {
        try
        {
            //echo "PWAにデータを送信\n";

            //作業用のフォルダを作成する
            $this->tmp_path=base_path()."\\tmp";
            if(!file_exists($this->tmp_path))
            {
                mkdir($this->tmp_path);
            }

            $where_send_id ="";
            if($send_id != null)
            {
                $where_send_id = " AND 送信ＩＤ = $send_id ";
            }

            $sql ="
                    SELECT 送信ＩＤ,SQL, 通知メッセージ
                      FROM モバイル送信リスト
                     WHERE 送信済フラグ<>1
                       AND NOT EXISTS(SELECT 1 FROM モバイル送信エラー E WHERE E.送信ＩＤ=モバイル送信リスト.送信ＩＤ)
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

            $post_count=0;
            foreach($DataList as $DataItem)
            {
                $post_count++;
                //現在日時を取得(ファイル名に使用)
                $seq_str = Carbon::now()->format('YmdHis') ."_". sprintf('%06d', $DataItem['送信ＩＤ']);
                //SQLをファイルに書き込む
                $sql_file = $this->tmp_path . "\\" . $seq_str . ".sql";
                if ($file_handle = fopen($sql_file, "w"))
                {
                    // 書き込み
                    fwrite($file_handle, $DataItem['SQL']);
                    // ファイルを閉じる
                    fclose($file_handle);
                }

                //ZIP圧縮する
                $zip_file=$this->tmp_path . "\\" . $seq_str . ".zip";
                $this->Compress($zip_file,array($sql_file));

                //Post実行
                $this->Post($zip_file,$DataItem['送信ＩＤ'],$DataItem['通知メッセージ']);

                //使用したテンポラリファイルを消す
                unlink($zip_file);
                unlink($sql_file);

                //AWSに短時間に60件以上のリクエストを投げるとエラー(429 Too Many Requests)を返すので、40post毎にちょっと待つ
                if(40<=$post_count)
                {
                    sleep(5);
                    $post_count=0;
                }
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
     * @param 通知メッセージ
     * @return void
     */
    private function Post($zip_file_path,$send_id,$notify_message)
    {
        try
        {
            //WebAPI宛に送信
            //TODO:テスト用URL(NEW社内)
            $url = "http://192.168.1.210/hellolaravel/public/api/mobiledatareceive";
            //TODO:本番URL
            $url="https://daiyas-mobile.tk/api/mobiledatareceive";

            // base64エンコード
            $base64_data = base64_encode(file_get_contents($zip_file_path));
            $post_data = array(
                'FileData' => $base64_data,
                'Notification' => $notify_message,
            );

            // cURLセッションを初期化
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url); // 取得するURLを指定
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
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
                $this->ErrorSendList($send_id,"接続エラー",$curl_error);
                return "";
            }
            $arrResult=json_decode($result);
            if($arrResult==null)
            {
                //モバイル送信エラーを書き込む
                $description="エラー";
                $this->ErrorSendList($send_id,$description,$result);
            }
            else
            {
                if ($arrResult->result==1) {
                    //モバイル送信リストにOKを書き込む
                    $this->SuccessSendList($send_id);
                }
                else{
                    //モバイル送信エラーを書き込む
                    $description="エラー";
                    $this->ErrorSendList($send_id,$description,base64_decode($arrResult->message));
                }
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
     * @param 通知メッセージ
     * @return 送信ID
     */
    public function StoreSendList($sql,$Immediate = null,$busho_cd = null,$customer_cd=null,$course_cd=null, $notify_message = null)
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
            $send_id = $send_id==null ? 1 : $send_id;

            //呼出元を取得
            $arrCallMethod=$this->GetCallMethod();
            $controller_id=$arrCallMethod["ControllerID"];
            $method_name=$arrCallMethod["MethodName"];

            $q_busho_cd   = $busho_cd    == null ? 'null' : $busho_cd;
            $q_customer_cd= $customer_cd == null ? 'null' : $customer_cd;
            $q_course_cd  = $course_cd   == null ? 'null' : $course_cd;
            $esc_sql=str_replace("'","''",$sql);
            $q_notify_message  = $notify_message == null ? '' : json_encode($notify_message);
            $ms_sql="INSERT INTO モバイル送信リスト(
                    送信ＩＤ
                   ,部署ＣＤ
                   ,得意先ＣＤ
                   ,コースＣＤ
                   ,コントローラＩＤ
                   ,メソッド名
                   ,作成日時
                   ,SQL
                   ,通知メッセージ
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
                    ,'$q_notify_message'
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
            return $send_id;
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
