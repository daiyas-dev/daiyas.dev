<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI07030Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $BushoCd = $vm->BushoCd;
        $CourseKbn = $vm->CourseKbn;
        $TargetDate = $vm->TargetDate;

        //$WhereBusho = $BushoCd ? ("COU.部署ＣＤ = " . $BushoCd . " AND MOB_YOSOKU.部署CD IS NOT NULL AND") : "";
        $WhereBusho = $BushoCd ? "COU.部署ＣＤ = $BushoCd" : "";
        $WhereCourse = $CourseKbn ? ("AND COU.コース区分 = " . $CourseKbn) : "";


        $sql = "
            SELECT
                URIAGE_MEISAI.日付
                , URIAGE_MEISAI.部署ＣＤ
                , BUSYO.部署名
                , CT.コースＣＤ
                , COU.コース名
                , URIAGE_MEISAI.商品区分
                , URIAGE_MEISAI.商品ＣＤ
                , SHOHIN.商品略称
                , SUM(URIAGE_MEISAI.現金個数 +  URIAGE_MEISAI.掛売個数) AS 数量
            FROM
                コースマスタ COU
                INNER JOIN コーステーブル CT ON
                COU.コースＣＤ = CT.コースＣＤ AND
                COU.部署ＣＤ = CT.部署ＣＤ
                LEFT JOIN 部署マスタ BUSYO  ON
                COU.部署ＣＤ = BUSYO.部署CD
                LEFT JOIN 売上データ明細 URIAGE_MEISAI ON
                CT.得意先ＣＤ = URIAGE_MEISAI.得意先ＣＤ AND
                CT.部署ＣＤ = URIAGE_MEISAI.部署ＣＤ
                LEFT JOIN 商品マスタ SHOHIN ON
                URIAGE_MEISAI.商品ＣＤ = SHOHIN.商品ＣＤ
            WHERE
                URIAGE_MEISAI.部署ＣＤ=$BushoCd
            AND URIAGE_MEISAI.日付 = '$TargetDate'
            GROUP BY
                URIAGE_MEISAI.日付
                , URIAGE_MEISAI.部署ＣＤ
                , BUSYO.部署名
                , CT.コースＣＤ
                , COU.コース名
                , URIAGE_MEISAI.商品区分
                , URIAGE_MEISAI.商品ＣＤ
                , SHOHIN.商品略称
            ORDER BY
                URIAGE_MEISAI.部署ＣＤ
                , CT.コースＣＤ
                , URIAGE_MEISAI.商品区分
                , URIAGE_MEISAI.商品ＣＤ
        ";

        // $dsn = 'sqlsrv:server=localhost;database=daiyas';
        // $user = 'daiyas';
        // $password = 'daiyas';

        // $pdo = new PDO($dsn, $user, $password);
        // $stmt = $pdo->query($sql);
        // $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // $pdo = null;

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }
}
