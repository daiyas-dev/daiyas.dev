<?php

namespace App\Http\Controllers;

use App\Libs\DaiyasCommon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XWriter;

use NcJoes\OfficeConverter\OfficeConverter;

class PID0201Controller extends Controller
{

    /**
     * GetViewModel
     */
    public function GenerateViewModel()
    {
        //TODO: dummy ViewModel
        $vm = json_decode('
            {
            }
        ');

        return $vm;
    }

    /**
     * GetViewModel
     */
    public function GetViewModel()
    {
        return response()->json($this->GenerateViewModel());
    }

    public function Export($request)
    {
        $kind = $request->kind ?? 'Excel';

        //excel template
        $templateDir = 'excel\\';
        $templateXlsx = 'test1.xlsx';
        $templatePath = storage_path($templateDir . $templateXlsx);

        //set values
        $setter = function($excel) {
            $faker = \Faker\Factory::create('ja_JP');
            $sheet = $excel->getActiveSheet();
            $sheet->setCellValue('A1', now()->format('Y/m/d'));
            $sheet->setCellValue('G29', $faker->numberBetween(1, 99));
            $sheet->setCellValue('G30', $faker->numberBetween(1, 99));
            $sheet->setCellValue('G31', $faker->numberBetween(1, 99));
            $sheet->setCellValue('G34', $faker->numberBetween(50, 100) * 10000);
            $sheet->setCellValue('G35', $faker->numberBetween(50, 100) * 10000);
            $sheet->setCellValue('G36', $faker->numberBetween(50, 100) * 10000);
        };

        DaiyasCommon::Export($kind, $templatePath, $setter);
    }

}
