<?php

namespace App\Console\Commands;
use Exception;
use Illuminate\Console\Command;
use App\Libs\PWADataReceive;

//AWS(PWA)から社内DBへ取込
class MobileDataReceive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:MobileDataReceive';

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
        try {
            $dr = new PWADataReceive();
            $dr->Receive();
        }
        catch (Exception $exception) {
            echo $exception;
        }
    }
}
