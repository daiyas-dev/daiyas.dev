<?php

use App\Models\Course;
use Illuminate\Database\Seeder;

class CoursesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        //factory(Course::class, 20)->create();
        for ($i = 1; $i <= 20; $i++) {
            \DB::table('courses')->insert(
                array(
                    'kbn' => '1',
                    'cd' => '1' . sprintf('%02d', $i),
                    'name' => '平日' . sprintf('%02d', $i) . 'コース',
                    'user_id' => $i,
                    'fac_kbn' => '',
                )
            );
        }
        for ($i = 1; $i <= 20; $i++) {
            \DB::table('courses')->insert(
                array(
                    'kbn' => '2',
                    'cd' => '2' . sprintf('%02d', $i),
                    'name' => '夜間' . sprintf('%02d', $i) . 'コース',
                    'user_id' => 20 - $i,
                    'fac_kbn' => '',
                )
            );
        }
    }
}

