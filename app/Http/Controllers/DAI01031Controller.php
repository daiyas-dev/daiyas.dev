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

        //TODO コーステーブル一時有無
        $CustomerCd = $request->CustomerCd;
        $sql = "
            SELECT
                CT.*
                ,一時フラグ
            FROM コーステーブル管理 CTC
            LEFT JOIN コーステーブル CT ON CT.コースＣＤ = CTC.コースＣＤ
            WHERE
                CT.得意先ＣＤ = $CustomerCd
                AND 一時フラグ = 1
                AND CONVERT(varchar, 適用開始日, 112) >= '$TargetDate'
                AND CONVERT(varchar, 適用終了日, 112) <= '$TargetDate'
            ";

        $Result = DB::select($sql);
        $Table = count($Result) == 0 ? "コーステーブル" : "コーステーブル一時";

        $sql = "
        SELECT DISTINCT
            $Table.コースＣＤ,
            コースマスタ.コース名,
            ＳＥＱ,
            $Table.得意先ＣＤ,
            得意先マスタ.得意先名,
            得意先マスタ.売掛現金区分,
            IIF(
                SUM(IIF(モバイル_販売入力.実績入力=1, 1, 0)) OVER(PARTITION BY $Table.コースＣＤ, $Table.得意先ＣＤ, モバイル_販売入力.日付) > 0,
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
        WHERE
        $Table.コースＣＤ = $CourseCd AND
        $Table.部署CD = $BushoCd AND
            コースマスタ.コース区分 = $CourseKbn
        ORDER BY
        $Table.コースＣＤ,ＳＥＱ
                ";
//         $sql = "
// SELECT DISTINCT
// 	コーステーブル.コースＣＤ,
// 	コースマスタ.コース名,
// 	ＳＥＱ,
// 	コーステーブル.得意先ＣＤ,
// 	得意先マスタ.得意先名,
// 	得意先マスタ.売掛現金区分,
// 	IIF(
// 		SUM(IIF(モバイル_販売入力.実績入力=1, 1, 0)) OVER(PARTITION BY コーステーブル.コースＣＤ, コーステーブル.得意先ＣＤ, モバイル_販売入力.日付) > 0,
// 		'済',
// 		''
// 	) AS 状態,
// 	MAX(モバイル_販売入力.修正日) OVER(PARTITION BY コーステーブル.コースＣＤ, コーステーブル.得意先ＣＤ, モバイル_販売入力.日付) AS 更新日時
// FROM
// 	[コーステーブル]
// 	INNER JOIN [コースマスタ]
// 		ON コースマスタ.コースＣＤ = コーステーブル.コースＣＤ AND コースマスタ.部署ＣＤ = コーステーブル.部署ＣＤ
// 	INNER JOIN [得意先マスタ]
// 		ON 得意先マスタ.得意先ＣＤ = コーステーブル.得意先ＣＤ AND コースマスタ.部署ＣＤ = コーステーブル.部署ＣＤ
// 	LEFT OUTER JOIN [モバイル_販売入力]
// 		ON	モバイル_販売入力.コースＣＤ = コーステーブル.コースＣＤ AND
// 			モバイル_販売入力.得意先ＣＤ = コーステーブル.得意先ＣＤ AND
// 			モバイル_販売入力.実績入力 = 1 AND
// 			モバイル_販売入力.日付 = '$TargetDate'
// WHERE
// 	コーステーブル.コースＣＤ = $CourseCd AND
// 	コーステーブル.部署CD = $BushoCd AND
// 	コースマスタ.コース区分 = $CourseKbn
// ORDER BY
// 	コーステーブル.コースＣＤ,ＳＥＱ
//         ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }
}
