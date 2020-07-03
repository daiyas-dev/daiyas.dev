<?php

namespace App\Console\Commands\Test;

use Illuminate\Console\Command;
use App\Libs\Test\DataSendTest2;

//社内DBからモバイルへ送信(送信ID指定)
class TestBatch5 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:test5 {user?}';

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
        $user = $this->argument('user');
        $ds = new DataSendTest2();
        $ds->Send();
    }
}
