<?php

use Flynsarmy\CsvSeeder\CsvSeeder;

setlocale(LC_ALL, 'ja_JP.UTF-8');
class MenusTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'menus';
        $this->filename = base_path() . '/database/seeds/csv/menus.csv';
    }

    public function run()
    {
        DB::disableQueryLog();
        DB::table($this->table)->truncate();

        parent::run();
    }
}
