<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Libs\PWADataSend;

//社内DBからモバイルへ送信(クラステスト用)
class TestBatch4 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:test4';

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
        $ds = new PWADataSend();
        $ds->Send();
    }
}
