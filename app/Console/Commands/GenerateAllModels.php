<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateAllModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'krlove:generate:all-models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate all models';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //$driver = DB::connection()->getDriverName();  //Variable Any Database?
        $driver = config('database.connections.' . config('database.default') . '.driver');
        $database = config('database.connections.' . config('database.default') . '.database');

        $tables = [];
        switch ($driver) {
            case 'sqlsrv':
                // $tables = DB::select("select name from sysobjects where xtype='U' and name not like '%_20%'");
                // $tables = collect($tables)->map(function($t) {
                //     $name = $t->name;
                //     if (mb_strlen($name, 'utf-8') < strlen($name)) {
                //         $name = str_replace('"', '', $name);
                //     }
                //     return $name;
                // });
                $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
                $tables = collect($tables)
                    ->filter(function ($t) {
                        return strpos($t, '_20') === false;
                    })
                    ->map(function ($t) {
                        if (mb_strlen($t, 'utf-8') < strlen($t)) {
                            $t = str_replace('"', '', $t);
                        }
                        return $t;
                    });
                break;
            case 'mysql':
                $tables = DB::select('SHOW TABLES');
                $tables = collect($tables);
                $tables = $tables->pluck('Tables_in_' . $database);
                break;
            case 'pgsql':
                $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
                $tables = collect($tables)->map(function ($t) {
                    if (mb_strlen($t, 'utf-8') < strlen($t)) {
                        $t = str_replace('"', '', $t);
                    }
                    return $t;
                });
                break;
            default:

        }

        foreach ($tables as $table) {
            if ($table === 'migrations') continue;
            $className = Str::camel(Str::singular($table));
            $className = ucfirst($className);
            Artisan::call('krlove:generate:model', ['class-name' => $className, '--table-name' => $table]);
        }
    }
}
