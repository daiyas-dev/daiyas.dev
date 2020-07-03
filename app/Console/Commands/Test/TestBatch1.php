<?php

namespace App\Console\Commands\Test;

use Illuminate\Console\Command;
use App\Libs\Test\DataSendTest2;

//非同期処理テストの呼び出し
class TestBatch1 extends Command
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
        $ds = new DataSendTest2();
        $ds->Send();
    }
}
