<?php

namespace App\Libs;
use Exception;
use PDO;

//モバイル・Web受注から社内DBへ取込
class DataRecieve
{
    public $tmp_path;

    public function Recieve()
    {
        try {
            $this->tmp_path=base_path()."\\tmp";

            //モバイル・Web受注サーバからデータを取得する
            $zip_path = $this->GetResponse();

            //zipを解凍する
            $zip = new \ZipArchive();
            if ($zip->open($zip_path) === true) {
                $zip_dir_path = $this->tmp_path."\\".basename($zip_path,".tmp");
                mkdir($zip_dir_path);
                $zip->extractTo($zip_dir_path);
                $zip->close();
            }

            //解凍したファイルを読み込んでSQLを実行
            $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
            $user = 'daiyas';
            $password = 'daiyas';
            $pdo = new PDO($dsn, $user, $password);
            $pdo->beginTransaction();
            foreach(glob($zip_dir_path.'\\*') as $sqlfile){
                if(is_file($sqlfile)){
                    $sql = file_get_contents($sqlfile);
                    $pdo->exec($sql);
                }
            }
            $pdo->commit();
            $pdo = null;

            //使用したテンポラリファイルを消す
            unlink($zip_path);
            foreach(glob($zip_dir_path.'\\*') as $sqlfile){
                if(is_file($sqlfile)){
                    unlink($sqlfile);
                }
                rmdir($zip_dir_path);
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
            $url = "http://localhost:49503/api/Hello";

            // cURLセッションを初期化
            $ch = curl_init();

            // オプションを設定
            curl_setopt($ch, CURLOPT_URL, $url); // 取得するURLを指定
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 実行結果を文字列で返す
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // サーバー証明書の検証を行わない

            // URLの情報を取得
            $response =  curl_exec($ch);
            // セッションを終了
            curl_close($ch);

            //取得したレスポンスをファイルにする
            $tmp_file = tempnam($this->tmp_path, "zip");
            if ($file_handle = fopen($tmp_file, "w")) {
                // 書き込み
                fwrite($file_handle, base64_decode($response));
                // ファイルを閉じる
                fclose($file_handle);
            }
            return $tmp_file;
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
}
