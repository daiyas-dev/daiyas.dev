<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

class TestBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:test';

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
        echo"batch start\n";
        $sql = "select top 1 * from 得意先マスタ";
        /*
        //LaravelのDBクラスでアクセス
        $CustomerData = DB::selectOne($sql);
        $customerName=$CustomerData->得意先名カナ;
        */
        //PDOでアクセス
        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';
        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $CustomerData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        $customerName=$CustomerData[0]['得意先名カナ'];

        echo $customerName;
    }
}
