<?php

namespace App\Console\Commands;
use Exception;
use Illuminate\Console\Command;
use App\Libs\WebOrderDataReceive;

//AWS(WebOrder)から社内DBへ取込
class WODataReceive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:WODataReceive';

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
            $dr = new WebOrderDataReceive();
            $dr->Receive();
        }
        catch (Exception $exception) {
            echo $exception;
        }
    }
}
