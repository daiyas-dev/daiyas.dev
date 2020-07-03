<?php

namespace App\Console\Commands\Test;

use Illuminate\Console\Command;

//他のartisanコマンドを別プロセスで呼び出す
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
        $phpPath = PHP_BINARY;
        $artisan = base_path(). DIRECTORY_SEPARATOR. "artisan";
        $command = "{$phpPath} {$artisan} batch:MobileDataSend2 abc";
        //exec("nohup {$phpPath} {$artisan} {$command} > /dev/null &");
		$fp = popen('start "" /min ' . $command, 'r');
        pclose($fp);
    }
}
