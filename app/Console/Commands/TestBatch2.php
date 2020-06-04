<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;

//モバイル・Web受注から社内DBへ取込
//バッチ処理の試作2(WebAPI呼出(受信)し、ZIPファイルを展開してSQLを実行する)
class TestBatch2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:test2';

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
     * @return mixed
     */
    public function handle()
    {
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
        $tmp_path=base_path()."\\tmp";
        $tmpfile = tempnam($tmp_path, "zip");
        if ($file_handle = fopen($tmpfile, "w")) {
            // 書き込み
            fwrite($file_handle, base64_decode($response));
            // ファイルを閉じる
            fclose($file_handle);
        }

        //zipを解凍する
        $zip = new \ZipArchive();
        if ($zip->open($tmpfile) === true) {
            $tmp_zip_path = $tmp_path."\\".basename($tmpfile,".tmp");
            mkdir($tmp_zip_path);
            $zip->extractTo($tmp_zip_path);
            $zip->close();
        }

        //解凍したファイルを読み込んでSQLを実行
        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';
        $pdo = new PDO($dsn, $user, $password);
        $pdo->beginTransaction();
        foreach(glob($tmp_zip_path.'\\*') as $sqlfile){
            if(is_file($sqlfile)){
                $sql = file_get_contents($sqlfile);
                $pdo->exec($sql);
            }
        }
        $pdo->commit();
        $pdo = null;

        //使用したテンポラリファイルを消す
        unlink($tmpfile);
        foreach(glob($tmp_zip_path.'\\*') as $sqlfile){
            if(is_file($sqlfile)){
                unlink($sqlfile);
            }
            rmdir($tmp_zip_path);
        }
    }
}
