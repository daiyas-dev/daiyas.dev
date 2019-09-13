<?php

use Illuminate\Database\Seeder;

class CodesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('codes')->delete();
        
        \DB::table('codes')->insert(array (
            0 => 
            array (
                'cd' => 100,
                'ord' => 1,
                'name' => '平日',
                'abb' => '平日',
                'sub1' => NULL,
                'sub2' => NULL,
                'user_id' => NULL,
                'update_at' => NULL,
            ),
            1 => 
            array (
                'cd' => 100,
                'ord' => 2,
                'name' => '夜間',
                'abb' => '夜間',
                'sub1' => NULL,
                'sub2' => NULL,
                'user_id' => NULL,
                'update_at' => NULL,
            ),
        ));
        
        
    }
}