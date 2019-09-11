<?php

use Illuminate\Database\Seeder;

class MenusTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('menus')->delete();
        
        \DB::table('menus')->insert(array (
            0 => 
            array (
                'id' => 1,
                'created_at' => '2019-08-28 14:13:13',
                'updated_at' => '2019-08-28 14:13:13',
            ),
            1 => 
            array (
                'id' => 2,
                'created_at' => '2019-08-28 14:17:53',
                'updated_at' => '2019-08-28 14:17:53',
            ),
            2 => 
            array (
                'id' => 3,
                'created_at' => '2019-08-28 14:17:54',
                'updated_at' => '2019-08-28 14:17:54',
            ),
            3 => 
            array (
                'id' => 4,
                'created_at' => '2019-08-28 14:17:55',
                'updated_at' => '2019-08-28 14:17:55',
            ),
            4 => 
            array (
                'id' => 5,
                'created_at' => '2019-08-28 14:17:55',
                'updated_at' => '2019-08-28 14:17:55',
            ),
        ));
        
        
    }
}