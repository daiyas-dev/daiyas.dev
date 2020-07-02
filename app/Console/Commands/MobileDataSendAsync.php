<?php

namespace App\Console\Commands;
use App\Libs\DataSendWrapper;

use Illuminate\Console\Command;

//モバイルデータ送信を非同期で呼び出す
class MobileDataSendAsync extends Command
{
    protected $signature = 'batch:MobileDataSendAsync {SendID*}';
    protected $description = 'モバイルデータ送信を非同期で呼び出す。送信ID指定必須。batch:MobileDataSend 1 2 3のように複数指定可。';
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
        $ds=new DataSendWrapper();
        $ds->SendAsync($SendID);
    }
}
