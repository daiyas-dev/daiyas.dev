<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Exception;
use PDO;

//社内システムから送信されたモバイル・Web受注DB更新SQLを受信し、DBを更新する
class MobileDataRecieveController extends Controller
{
    public function recieve(Request $request)
    {
        try {
            //作業用のフォルダを作成する
            $this->tmp_path=public_path()."/recieve";
            if (!file_exists($this->tmp_path)) {
                mkdir($this->tmp_path);
                chmod($this->tmp_path, 0777);
            }

            //取得したレスポンスをファイルにする
            $filedata = $request->input('FileData');

            if($filedata==null || $filedata==="")
            {
                return $this->getResult(0,"ZIPファイルがありません");
            }

            $tmp_file = tempnam($this->tmp_path, "zip");
            if ($file_handle = fopen($tmp_file, "w")) {
                // 書き込み
                fwrite($file_handle, base64_decode($filedata));
                // ファイルを閉じる
                fclose($file_handle);
                chmod($tmp_file, 0777);
            }

            //zipを解凍する
            $zip_path = $tmp_file;
            $zip = new \ZipArchive();
            if ($zip->open($zip_path) === true) {
                $zip_dir_path = $this->tmp_path."/".basename($zip_path, ".tmp")."_";//linuxだとファイル名とフォルダ名が同一になるので_を付加
                mkdir($zip_dir_path);
                chmod($zip_dir_path, 0777);
                $zip->extractTo($zip_dir_path);
                $zip->close();
            } else {
                //解凍できなかった場合
                return $this->getResult(0,"ZIPファイルを展開できません");
            }

            //解凍したファイルを読み込んでSQLを実行
            $dsn = 'mysql:host=127.0.0.1;dbname=daiyas_app';
            $user = 'root';
            $password = 'WcV5bu%s4p';
            $pdo = new PDO($dsn, $user, $password);
            $pdo->beginTransaction();
            $is_error=false;
            foreach(glob($zip_dir_path.'/*') as $sqlfile){
                if(is_file($sqlfile)){
	                chmod($sqlfile, 0777);
                    $sql = file_get_contents($sqlfile);
                    $pdo->exec($sql);
                    $error_info=$pdo->errorInfo();
                    if($error_info[0]!="00000" || $error_info[1]!=null || $error_info[2]!=null)
                    {
                        //SQLを実行してエラーが発生した場合
                        return $this->getResult(0,$error_info[1]." ".$error_info[2]);
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
                foreach (glob($zip_dir_path.'/*') as $sqlfile) {
                    if (is_file($sqlfile)) {
                        unlink($sqlfile);
                    }
                    rmdir($zip_dir_path);
                }
            }
            return $this->getResult(1);
        }
        catch (Exception $exception)
        {
            return $this->getResult(0,$exception);
        }
    }
    private function getResult($result,$message='')
    {
        return response()->json([
            'result' => $result,
            'message'=> base64_encode($message)
        ]);
    }
}
