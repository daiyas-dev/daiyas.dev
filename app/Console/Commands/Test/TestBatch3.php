<?php

namespace App\Console\Commands\Test;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

//社内DBからモバイル・Web受注へ送信
//バッチ処理の試作3(SQLをファイルに書き出し、ZIP圧縮してWebAPI呼出(送信)する)
class TestBatch3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:test3';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //現在日時を取得(ファイル名に使用)
        $dt_str = Carbon::now()->format('YmdHis');

        //SQLを生成する
        $sql="abcdefg";
        //カラムマッピング・変換処理

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
        $zip = new \ZipArchive();
        $zip->open($zip_file, \ZipArchive::CREATE);
        $zip->addFile($sql_file,basename($sql_file));
        $zip->close();

        // base64エンコード
        $base64_data = base64_encode(file_get_contents($zip_file));
        $post_data = array(
            'FileData' => $base64_data
        );

        //WebAPI宛に送信
        $url = "http://localhost:49503/api/Welcome";

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

       //使用したテンポラリファイルを消す
       unlink($zip_file);
       unlink($sql_file);
    }
}
