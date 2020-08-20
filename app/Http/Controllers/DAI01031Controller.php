<?php

namespace App\Http\Controllers;

use App\Models\注文データ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use LengthException;
use PDO;

class DAI01031Controller extends Controller
{

    /**
     * Search
     */
    public function Search($request)
    {
        $BushoCd = $request->BushoCd;
        $CourseCd = $request->CourseCd;
        $TargetDate = $request->TargetDate;

        $Utilities = new UtilitiesController();
        $CourseKbn = $Utilities->SearchCourseKbnFromDate($request)->コース区分;

        //コーステーブル一時有無
        $CustomerCd = $request->CustomerCd;
        $sql = "
            SELECT
                CT.*
                ,一時フラグ
            FROM コーステーブル管理 CTC
            LEFT JOIN コーステーブル CT ON CT.コースＣＤ = CTC.コースＣＤ
            WHERE
                CTC.部署ＣＤ = $BushoCd
                AND CTC.コースＣＤ = $CourseCd
                AND CT.得意先ＣＤ = $CustomerCd
                AND CONVERT(varchar, 適用開始日, 112) >= '$TargetDate'
                AND CONVERT(varchar, 適用終了日, 112) <= '$TargetDate'
                AND 一時フラグ = 1
        ";

        $Result = DB::select($sql);
        $Table = count($Result) == 0 ? "コーステーブル" : (count($Result) == 1 ? "コーステーブル一時" : "不正");

        //コーステーブル管理の内容が不正 -> 0件表示
        if ( $Table == "不正" ) return response()->json([]);

        $sql = "
            SELECT DISTINCT
                $Table.コースＣＤ,
                コースマスタ.コース名,
                ＳＥＱ,
                $Table.得意先ＣＤ,
                得意先マスタ.得意先名,
                得意先マスタ.売掛現金区分,
                IIF(
                    SUM(IIF(モバイル_販売入力.実績入力=1, 1, 0)) OVER(PARTITION BY $Table.コースＣＤ, $Table.得意先ＣＤ, モバイル_販売入力.日付) > 0
                    AND
                    モバイル_更新予定リスト.得意先ＣＤ IS NOT NULL
                    ,
                    '済',
                    ''
                ) AS 状態,
                MAX(モバイル_販売入力.修正日) OVER(PARTITION BY $Table.コースＣＤ, $Table.得意先ＣＤ, モバイル_販売入力.日付) AS 更新日時
            FROM
                [$Table]
                INNER JOIN [コースマスタ]
                    ON コースマスタ.コースＣＤ = $Table.コースＣＤ AND コースマスタ.部署ＣＤ = $Table.部署ＣＤ
                INNER JOIN [得意先マスタ]
                    ON 得意先マスタ.得意先ＣＤ = $Table.得意先ＣＤ AND コースマスタ.部署ＣＤ = $Table.部署ＣＤ
                LEFT OUTER JOIN [モバイル_販売入力]
                    ON	モバイル_販売入力.コースＣＤ = $Table.コースＣＤ AND
                        モバイル_販売入力.得意先ＣＤ = $Table.得意先ＣＤ AND
                        モバイル_販売入力.実績入力 = 1 AND
                        モバイル_販売入力.日付 = '$TargetDate'
                LEFT OUTER JOIN [モバイル_更新予定リスト]
                    ON	モバイル_更新予定リスト.得意先ＣＤ = $Table.得意先ＣＤ AND
                        モバイル_更新予定リスト.日付 = '$TargetDate'
            WHERE
            $Table.コースＣＤ = $CourseCd AND
            $Table.部署CD = $BushoCd AND
                コースマスタ.コース区分 = $CourseKbn
            ORDER BY
            $Table.コースＣＤ,ＳＥＱ
        ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }
}
