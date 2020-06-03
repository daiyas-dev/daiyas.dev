<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

//バッチ処理の試作1(SQL実行とWebAPI呼出のテスト)
class TestBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:test1';

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
        echo"batch start\n";
        $sql = "select top 1 * from 得意先マスタ";
        /*
        //LaravelのDBクラスでアクセス
        $CustomerData = DB::selectOne($sql);
        $customerName=$CustomerData->得意先名カナ;
        */
        /*
        //PDOでアクセス
        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';
        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $CustomerData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        $customerName=$CustomerData[0]['得意先名カナ'];
        echo $customerName;
        */
        $this->CallWebAPI();
    }
    private function CallWebAPI()
    {
        $url = "http://localhost:49503/api/Hello";

        // cURLセッションを初期化
        $ch = curl_init();

        // オプションを設定
        curl_setopt($ch, CURLOPT_URL, $url); // 取得するURLを指定
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 実行結果を文字列で返す
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // サーバー証明書の検証を行わない

        // URLの情報を取得
        $response =  curl_exec($ch);

        // 取得結果を表示
        echo $response;

        // セッションを終了
        curl_close($ch);
    }
}
