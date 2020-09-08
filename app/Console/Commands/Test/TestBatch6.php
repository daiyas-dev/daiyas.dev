<?php

namespace App\Console\Commands\Test;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

//モバイルAPIテスト
class TestBatch6 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:test6';

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
        $this->Postgettokuisakitanka();
    }
    private function Postgettokuisakitanka()
    {
        // 引数生成
        $post_data = array(
            'product_code' => '10'
           ,'customer_code' => '1463'
           ,'delivery_date' => '2020/07/06'
       );
       $post_data = array(
          'customer_code' => '1463'
      );
       //関数実行
       $this->PostCommand($post_data,"gettokuisakitanka");
   }
    private function PostCommand($post_data,$function_name)
    {
        $url = "http://192.168.1.210/hellolaravel/public/api/$function_name";

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
}
