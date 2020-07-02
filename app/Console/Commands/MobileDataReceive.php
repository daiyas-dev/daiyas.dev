<?php

namespace App\Console\Commands;
use Exception;
use Illuminate\Console\Command;
use App\Libs\PWADataReceive;

//AWS(PWA)から社内DBへ取込
class MobileDataReceive extends Command
{
    protected $signature = 'batch:MobileDataReceive';
    protected $description = 'AWS(PWA)から社内DBへ更新データを取込';
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
