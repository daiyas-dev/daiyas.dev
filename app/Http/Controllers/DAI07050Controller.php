<?php

namespace App\Http\Controllers;

use App\Models\入金データ;
use App\Models\管理マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use PDO;

class DAI07050Controller extends Controller
{

    /**
     * Search
     */
    public function Search($vm)
    {
        return response()->json($this->GetSeikyuList($vm));
    }

    /**
     * GetSeikyuList
     */
    public function GetSeikyuList($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDate = $vm->TargetDate;

        $sql = "
            WITH BASE_DATA AS (
                SELECT
                    T1.請求先ＣＤ AS 得意先ＣＤ
                    ,M1.得意先名 AS 得意先名
                    ,M1.電話番号１ AS 得意先_電話番号
                    ,T1.今回売上額 AS 今回売上額
                    ,FLOOR(T1.今回売上額 / (100 + ISNULL(M4.消費税率,0)) * ISNULL(M4.消費税率,0)) AS 今回売上額_消費税額
                    ,T1.今回請求額 AS 今回請求額
                    ,FLOOR(T1.今回請求額 / (100 + ISNULL(M4.消費税率,0)) * ISNULL(M4.消費税率,0)) AS 今回請求額_消費税額
                    ,T1.請求日付 AS 請求日
                    ,'' 締日表示
                    ,T1.回収予定日 AS 支払日
                    ,'' AS 支払日表示
                    ,FORMAT(T1.請求日付, 'M月分') AS 但書
                    ,M2.会社名称 AS 会社名
                    ,M2.郵便番号 AS 郵便番号
                    ,M2.住所 AS 住所
                    ,M2.電話番号 AS 電話番号
                    ,M2.FAX AS ＦＡＸ
                    ,FORMAT(GETDATE(), 'yyyy/MM/dd') AS 発行日
                    ,0 AS 控区分
                    ,M3.各種名称 請求書敬称
                    ,M1.締日１
                    ,M1.支払サイト
                    ,M1.支払日 AS 支払日１
                    ,ISNULL(M5.コースＣＤ,0) AS コースＣＤ
                    ,ISNULL(COU_SEQ.ＳＥＱ,0) AS ＳＥＱ
                    ,T1.予備金額１ AS 予備金額１
                FROM
                    請求データ T1
                    INNER JOIN 得意先マスタ M1 ON
                            T1.請求先ＣＤ = M1.得意先ＣＤ
                        AND M1.得意先ＣＤ = M1.請求先ＣＤ
                    LEFT OUTER JOIN 部署マスタ M2 ON
                        T1.部署ＣＤ = M2.部署CD
                    LEFT OUTER JOIN 各種テーブル M3 ON
                        M1.請求書敬称 = M3.行NO AND 各種CD = 11
                    LEFT OUTER JOIN 消費税率マスタ M4 ON
                            M4.内外区分 = 20
                        AND M4.適用年月 <= T1.請求日付
                        AND M4.現在使用FLG = 1
                    LEFT OUTER JOIN (
                        SELECT
                            COU.部署ＣＤ
                            ,COU.得意先ＣＤ
                            ,MIN(COU.コースＣＤ) AS コースＣＤ
                        FROM
                            コーステーブル COU
                            INNER JOIN コースマスタ COUM ON
                            COUM.部署ＣＤ = COU.部署ＣＤ
                            AND COUM.コースＣＤ = COU.コースＣＤ
                            AND コース区分 IN (1,2,3)
                        WHERE
                            COU.部署ＣＤ = 101
                        GROUP BY
                            COU.部署ＣＤ
                            ,COU.得意先ＣＤ
                    ) M5 ON
                            M5.部署ＣＤ = T1.部署ＣＤ
                        AND CASE
                            WHEN M1.受注得意先ＣＤ = 0 THEN M1.得意先ＣＤ
                            ELSE M1.受注得意先ＣＤ
                        END = M5.得意先ＣＤ
                    LEFT OUTER JOIN コーステーブル COU_SEQ ON
                            COU_SEQ.部署ＣＤ = M5.部署ＣＤ
                        AND COU_SEQ.得意先ＣＤ = M5.得意先ＣＤ
                        AND COU_SEQ.コースＣＤ = M5.コースＣＤ
                WHERE
                    T1.部署ＣＤ = $BushoCd AND
                    T1.請求日付 =
                        (SELECT
                            MAX(DMY.請求日付)
                        FROM
                            請求データ DMY
                        WHERE
                            DMY.請求日付 = '$TargetDate'
                            AND DMY.部署ＣＤ = T1.部署ＣＤ
                            AND DMY.請求先ＣＤ = T1.請求先ＣＤ
                        )
                    AND (
                        M4.適用年月 IS NULL
                        OR
                        M4.適用年月 = (
                            SELECT
                                MAX(DMY.適用年月)
                            FROM
                                消費税率マスタ DMY
                            WHERE
                                DMY.適用年月 <= T1.請求日付
                            AND DMY.内外区分 = 20
                            AND DMY.現在使用FLG = 1
                        )
                    )
            )
            SELECT
                T1.得意先ＣＤ
                ,T1.得意先名
                ,T1.得意先_電話番号
                ,T1.今回売上額
                ,T1.今回売上額_消費税額
                ,T1.今回請求額
                ,T1.今回請求額_消費税額
                ,T1.請求日
                ,T1.締日表示
                ,T1.支払日
                ,T1.支払日表示
                ,T1.但書
                ,T1.会社名
                ,T1.郵便番号
                ,T1.住所
                ,T1.電話番号
                ,T1.ＦＡＸ
                ,T1.発行日
                ,0 AS 控区分
                ,T1.請求書敬称
                ,T1.締日１
                ,T1.支払サイト
                ,T1.支払日１
                ,T1.コースＣＤ
                ,T1.ＳＥＱ
                ,T1.予備金額１
            FROM
                BASE_DATA T1
            WHERE
                T1.今回請求額 != 0
            ORDER BY
                コースＣＤ
                ,ＳＥＱ
                ,得意先ＣＤ
                ,請求日
                ,控区分 DESC
		";

        $dsn = 'sqlsrv:server=localhost;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        // $DataList = DB::select(DB::raw($sql));

        return $DataList;
    }
}
