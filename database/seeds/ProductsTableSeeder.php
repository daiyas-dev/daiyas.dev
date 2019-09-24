<?php

use Flynsarmy\CsvSeeder\CsvSeeder;
use Illuminate\Database\Seeder;

class ProductsTableSeeder extends CsvSeeder
{
    public function __construct()
    {
        $this->table = 'products';
        $this->filename = base_path() . '/database/seeds/csv/products.csv';
    }

    public function run()
    {
        DB::disableQueryLog();
        DB::table($this->table)->truncate();

        parent::run();
    }
}
