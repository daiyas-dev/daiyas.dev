<?php

namespace App\Libs;
use Exception;
use Illuminate\Support\Carbon;

//社内DBからモバイル・Web受注へ更新用SQLを送信する
class DataSend
{
    public function Send($param)
    {
        try {
            //カラムマッピング・変換処理
            $sql = $param;

            //現在日時を取得(ファイル名に使用)
            $dt_str = Carbon::now()->format('YmdHis');

            //SQLをファイルに書き込む
            $tmp_path=base_path()."\\tmp";
            $sql_file = $tmp_path ."\\". $dt_str . ".sql";
            if ($file_handle = fopen($sql_file, "w")) {
                // 書き込み
                fwrite($file_handle, $sql);
                // ファイルを閉じる
                fclose($file_handle);
            }

            //ZIP圧縮する
            $zip_file=$tmp_path ."\\". $dt_str .".zip";
            $this->Compress($zip_file,array($sql_file));
            $this->Post($zip_file);

            //使用したテンポラリファイルを消す
            unlink($zip_file);
            unlink($sql_file);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * 指定のURLにzipファイルをpostする
     * @param zipファイル名
     * @param 圧縮するファイル配列
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
     * @return void
     */
    private function Post($zip_file_path)
    {
        try {
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
            $result=curl_exec($ch);
            echo 'RETURN:'.$result;
            // セッションを終了
            curl_close($ch);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
}
