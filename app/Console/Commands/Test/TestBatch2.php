<?php

namespace App\Console\Commands\Test;

use Illuminate\Console\Command;
use App\Models\モバイル予測入力;
use DB;

//大量データを取得テスト
class TestBatch2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:test2';

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
     * @return mixed
     */
    public function handle()
    {
        /*
        foreach (モバイル予測入力::query()->get() as $user) {
            // Userオブジェクトに対する処理
        }
        */

        foreach (モバイル予測入力::query()->cursor() as $user) {
            // Userオブジェクトに対する処理
        }

        // Userオブジェクトに対する処理

        //$sql="select top 100 * from 得意先マスタ";
        //$table_data = DB::select($sql);
        /*
        得意先マスタ::query()->chunk(1000,function($customers)){
            foreach ($customers as $$customer) {
                // Userオブジェクトに対する処理
            }
        }
        */

        /*
        $table_data=array();
        DB::select($sql)->chunk(100, function ($recs){
            foreach ($recs as $rec) {
            }
        });
        */
        $a="完了";
    }
}
