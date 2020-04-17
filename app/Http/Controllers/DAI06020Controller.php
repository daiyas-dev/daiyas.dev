<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI05050Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $BushoCd = $vm->BushoCd;

        $sql = "
                SELECT
                  得意先マスタ.得意先ＣＤ
                , 得意先名
                , 得意先名カナ
                , 郵便番号
                , 住所１
                , 電話番号1
                , 売掛現金区分
                , 締区分
                , 締日１
            FROM
                [得意先マスタ]
                LEFT JOIN [コーステーブル]
                ON コーステーブル.得意先ＣＤ = CASE
                    WHEN 得意先マスタ.受注得意先ＣＤ = 0 THEN 得意先マスタ.得意先ＣＤ
                    ELSE 得意先マスタ.受注得意先ＣＤ
                    END
                AND 得意先マスタ.部署ＣＤ = コーステーブル.部署ＣＤ
            where
                コーステーブル.コースＣＤ is null
                AND 得意先マスタ.部署ＣＤ = $BushoCd
            order by
                コーステーブル.コースＣＤ
                , 得意先マスタ.得意先ＣＤ
            ";
        $DataList = DB::select(DB::raw($sql));

        return response()->json($DataList);
    }
}
