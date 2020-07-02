<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libs\PWADataSend;

//社内DBからモバイルへ送信
class MobileDataSend extends Command
{
    /**
     * コマンド名定義
     * 引数：(省略可)送信ID
     */
    protected $signature = 'batch:MobileDataSend {SendID?*}';
    protected $description = '社内DBからAWS(PWA)へ更新データを送信';
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
        $SendID = $this->argument('SendID');
        if ($SendID==null)
        {
            $ds = new PWADataSend();
            $ds->Send();
        }
        else if(is_array($SendID))
        {
            $ds = new PWADataSend();
            foreach ($SendID as $val)
            {
                if (is_numeric($val))
                {
                    $ds->Send($val);
                }
            }
        }
    }
}
