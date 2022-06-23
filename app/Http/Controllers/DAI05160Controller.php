<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI05160Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $DateStart = $vm->DateStart .' 00:00:00';
        $DateEnd = $vm->DateEnd .' 23:59:59';

        $sql = "
            select ml.送信ＩＤ,ml.部署ＣＤ,ml.得意先ＣＤ,format(ml.作成日時,'yyyy/MM/dd HH:mm:ss')as 表示用作成日時
                  ,busho.部署名
                  ,tokui.得意先名略称 as 得意先名
              from モバイル送信リスト ml
                   inner join 部署マスタ busho on busho.部署CD=ml.部署ＣＤ
                   inner join 得意先マスタ tokui on tokui.得意先ＣＤ=ml.得意先ＣＤ
             where ml.作成日時>='$DateStart'
               and ml.作成日時<='$DateEnd'
               and ml.送信済フラグ=0
               and ml.コントローラＩＤ in('DAI0130Save','DAI01090','DAI01270')
            order by ml.部署ＣＤ,ml.作成日時,tokui.得意先名カナ
        ";

        $DataList = DB::select($sql);
        return response()->json($DataList);
    }
}
