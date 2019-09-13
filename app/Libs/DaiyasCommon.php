<?php

namespace App\Libs;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Facades\Auth;
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
        $excel = clone $reader->load($template);

        //set values
        $setter($excel);

        //excel writer
        $writer = new Xwriter($excel);

        if ($kind == 'Excel') {
            //excel download
            $writer->save('php://output');
        } else {
            //Export Pdf
            $work = dirname($template) . '\\' . Auth::id() . '_' . now()->format('YmdHis') . '.xlsx';
            $writer->save($work);
            $pdf = preg_replace('/\.xlsx/', '.pdf', $work);
            $cmd = 'cd ' . dirname($work) . ' & ' .
                   'soffice --nolockcheck --nologo --headless --norestore --language=ja --nofirststartwizard --convert-to pdf ' .
                    $work;
            exec($cmd, $out, $ret);

            if ($ret == 0) {
                header('Content-type:application/pdf');
                header("Content-Disposition:attachment;filename='downloaded.pdf'");
                readfile($pdf);
            } else {
                //TODO: return error
            }

            //delete work
            unlink($work);
            unlink($pdf);
        }
    }

}
