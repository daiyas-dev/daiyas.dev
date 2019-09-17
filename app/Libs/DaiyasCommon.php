<?php

namespace App\Libs;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XWriter;

use NcJoes\OfficeConverter\OfficeConverter;

class DaiyasCommon
{
    public static function Export($kind, $template, $setter)
    {
        //clone template
        $reader = new XReader();
        $reader->setReadDataOnly(false);

        $st = microtime(true);
        $excel = clone $reader->load($template);
        $rt =  microtime(true) - $st;
        Log::debug('template load & clone: ' . $rt . ' [s]');

        $st = microtime(true);
        //set values
        $setter($excel);
        $rt =  microtime(true) - $st;
        Log::debug('set values: ' . $rt . ' [s]');

        //excel writer
        $writer = new Xwriter($excel);

        if ($kind == 'Excel') {
            //excel download
            $writer->save('php://output');
        } else {
            $st = microtime(true);
            //set values
            $setter($excel);
            $rt =  microtime(true) - $st;
            Log::debug('set values: ' . $rt . ' [s]');

            //Export Pdf
            $st = microtime(true);
            $work = dirname($template) . '\\' . Auth::id() . '_' . now()->format('YmdHis') . '.xlsx';
            $writer->save($work);
            $rt =  microtime(true) - $st;
            Log::debug('save work: ' . $rt . ' [s]');

            $pdf = preg_replace('/\.xlsx/', '.pdf', $work);

            //TODO: LibreOffice command line <- without ms-excel
            $cmd = 'cd ' . dirname($work) . ' & ' .
                   'soffice --nolockcheck --nologo --headless --norestore --language=ja --nofirststartwizard --convert-to pdf ' .
                    $work;

            //TODO: OfficeToPDF https://github.com/cognidox/OfficeToPDF <- require ms-excel
            $exe = storage_path('excel\OfficeToPDF.exe');
            $cmd = 'cd ' . dirname($work) . ' & ' . $exe . ' ' . $work . ' ' . $pdf;

            //TODO: Excel VBA
            $cmd = 'cd ' . dirname($work) . ' & ExportPdf.vbs ' . $work . ' ' . $pdf;

            //execute command
            $st = microtime(true);
            exec($cmd, $out, $ret);
            $rt =  microtime(true) - $st;
            Log::debug('generate pdf: ' . $rt . ' [s]');

            if ($ret == 0) {
                $st = microtime(true);
                header('Content-type:application/pdf');
                header("Content-Disposition:attachment;filename='downloaded.pdf'");
                readfile($pdf);
                $rt =  microtime(true) - $st;
                Log::debug('return pdf: ' . $rt . ' [s]');
            } else {
                //TODO: return error
                return response()->json([
                    'status' => 500,
                    'errors' => ['PDF生成失敗', $out],
                ], 500);
            }

            //delete work
            unlink($work);
            unlink($pdf);
        }
    }

}
