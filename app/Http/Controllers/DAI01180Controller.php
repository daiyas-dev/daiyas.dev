<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI01180Controller extends Controller
{

    /**
     *  ColumSerch
     */

     public function ColSearch($vm)
     {
        $BushoCd = $vm->BushoCd;
        $sql = "SELECT
                    *
                    FROM 各種テーブル
                    where 各種CD=
                    (select
                    IIF(サブ各種CD2=1,24,IIF(サブ各種CD2=2,25,IIF(サブ各種CD2=3,41,NULL)))
                    FROM 各種テーブル
                    WHERE
                    各種CD = 26
                    and サブ各種CD1 =". $BushoCd.")
                    order by 各種CD,行NO";


        $DataList = DB::select($sql);

        return response()->json($DataList);


     }
    /**
     * Search
     */
    public function Search($vm)
    {
        $BushoCd = $vm->BushoCd;
        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;

        $sql_course="
            AND (
                CUM.コース区分 = (
                    SELECT
                        D.コース区分
                    FROM (
                        SELECT
                            CASE
                                WHEN (SELECT 対象日付 FROM 祝日マスタ WHERE 対象日付 = NUD.入金日付) IS NOT NULL THEN 4
                                ELSE
                                    CASE DATEPART(WEEKDAY, NUD.入金日付)
                                        WHEN 1 THEN 3
                                        WHEN 7 THEN 2
                                        ELSE 1
                                    END
                            END AS コース区分
                    ) D
                )
                OR CUM.コースＣＤ IS NULL
            )
        ";
        if ($BushoCd==501 || $BushoCd==602) {
            $sql_course="";//コース区分を見ない
        }

        $sql = "
SELECT
  CONVERT(NVARCHAR, D1.日付, 111) AS 日付
  , D1.部署ＣＤ
  , MAX(D1.部署名) AS 部署名
  , D1.コースＣＤ
  , MAX(D1.コース名) AS コース名
  , D1.ＳＥＱ
  , D1.得意先ＣＤ
  , MAX(D1.得意先名) AS 得意先名
  , D1.締日
  , SUM(日締現金) AS 日締現金
  , SUM(バークレー) AS バークレー
  , SUM(束売り) AS 束売り
  , SUM(売掛現金) AS 売掛現金
  , SUM(日締現金) + SUM(バークレー) + SUM(束売り) + SUM(売掛現金) AS 現金計
  , SUM(振込) + SUM(振込手数料) AS 振込
  , SUM(振替) AS 振替
  , SUM(チケット) AS チケット
  , SUM(調整額) AS 調整額
  , SUM(日締現金) + SUM(バークレー) + SUM(束売り) + SUM(売掛現金) + SUM(振込) + SUM(振込手数料) + SUM(振替)
   + SUM(チケット) + SUM(調整額) AS 合計
FROM
  (
    SELECT
      URID.日付
      , URID.部署ＣＤ AS 部署ＣＤ
      , MAX(BUM.部署名) AS 部署名
      , URID.コースＣＤ
      , MAX(CUM.コース名) AS コース名
      , CUT.ＳＥＱ
      , URID.得意先ＣＤ
      , MAX(TOM.得意先名) AS 得意先名
      , CASE
        WHEN TOM.締日１ = 0
          THEN '日締'
        ELSE CASE
          WHEN TOM.締日１ = 99
            THEN '末締'
          ELSE CONVERT(VARCHAR, TOM.締日１) + '日'
          END
        END AS 締日
      , CASE
        WHEN URID.売掛現金区分 = 0
        AND URID.商品区分 <> 9
          THEN SUM(URID.現金金額 - URID.現金値引) + SUM(URID.掛売金額 - URID.掛売値引)
        ELSE 0
        END AS 日締現金
      , CASE
        WHEN URID.売掛現金区分 = 3
          THEN SUM(URID.現金金額 - URID.現金値引) + SUM(URID.掛売金額 - URID.掛売値引)
        ELSE 0
        END AS バークレー
      , CASE
        WHEN URID.商品区分 = 9
          THEN SUM(URID.現金金額 - URID.現金値引) + SUM(URID.掛売金額 - URID.掛売値引)
        ELSE 0
        END AS 束売り
      , 0 AS 売掛現金
      , 0 AS 振込
      , 0 AS 振込手数料
      , 0 AS 振替
      , 0 AS チケット
      , 0 AS 調整額
    FROM
      売上データ明細 URID
      LEFT JOIN 部署マスタ BUM
        ON BUM.部署ＣＤ = URID.部署ＣＤ
      LEFT JOIN 得意先マスタ TOM
        ON TOM.得意先ＣＤ = URID.得意先ＣＤ
      LEFT JOIN コースマスタ CUM
        ON CUM.コースＣＤ = URID.コースＣＤ
        AND CUM.部署ＣＤ = URID.部署ＣＤ
      LEFT JOIN コーステーブル CUT
        ON CUT.コースＣＤ = URID.コースＣＤ
        AND CUT.得意先ＣＤ = TOM.受注得意先ＣＤ
    WHERE
      URID.部署ＣＤ = $BushoCd
      AND URID.日付 >= '$DateStart'
      AND URID.日付 <= '$DateEnd'
      AND URID.売掛現金区分 IN (0, 3)
    GROUP BY
      URID.部署ＣＤ
      , URID.コースＣＤ
      , CUT.ＳＥＱ
      , URID.得意先ＣＤ
      , URID.日付
      , URID.売掛現金区分
      , TOM.締日１
      , URID.商品区分
    UNION ALL
    SELECT
      NUD.入金日付 AS 日付
      , NUD.部署ＣＤ
      , MAX(BUM.部署名) AS 部署名
      , CUT.コースＣＤ
      , MAX(CUM.コース名) AS コース名
      , CUT.ＳＥＱ
      , NUD.得意先ＣＤ
      , MAX(TOM.得意先名) AS 得意先名
      , CASE
        WHEN TOM.締日１ = 0
          THEN '日締'
        ELSE CASE
          WHEN TOM.締日１ = 99
            THEN '末締'
          ELSE CONVERT(VARCHAR, TOM.締日１) + '日'
          END
        END AS 締日
      , 0 AS 日締現金
      , 0 AS バークレー
      , 0 AS 束売り
      , SUM(現金 + 小切手) AS 売掛現金
      , SUM(振込) AS 振込
      , SUM(相殺) AS 振込手数料
      , SUM(バークレー) AS 振替
      , SUM(その他) AS チケット
      , SUM(値引) AS 調整額
    FROM
      入金データ NUD
      LEFT JOIN 部署マスタ BUM
        ON BUM.部署ＣＤ = NUD.部署ＣＤ
      LEFT JOIN 得意先マスタ TOM
        ON TOM.得意先ＣＤ = NUD.得意先ＣＤ
      LEFT JOIN コーステーブル CUT
        ON CUT.得意先ＣＤ = TOM.受注得意先ＣＤ
        AND CUT.部署ＣＤ = $BushoCd
      LEFT JOIN コースマスタ CUM
        ON CUM.コースＣＤ = CUT.コースＣＤ
        AND CUM.部署ＣＤ = NUD.部署ＣＤ
    WHERE
      NUD.部署ＣＤ = $BushoCd
      AND NUD.入金日付 >= '$DateStart'
      AND NUD.入金日付 <= '$DateEnd'
      /*
      AND (
        CUM.コース区分 = (
            SELECT
                D.コース区分
            FROM (
                SELECT
                    CASE
                        WHEN (SELECT 対象日付 FROM 祝日マスタ WHERE 対象日付 = NUD.入金日付) IS NOT NULL THEN 4
                        ELSE
                            CASE DATEPART(WEEKDAY, NUD.入金日付)
                                WHEN 1 THEN 3
                                WHEN 7 THEN 2
                                ELSE 1
                            END
                    END AS コース区分
            ) D
        )
        OR CUM.コースＣＤ IS NULL
      )
      */
      $sql_course
    GROUP BY
      NUD.部署ＣＤ
      , CUT.コースＣＤ
      , CUT.ＳＥＱ
      , NUD.得意先ＣＤ
      , NUD.入金日付
      , TOM.締日１
  ) D1
WHERE
  D1.部署ＣＤ = $BushoCd
GROUP BY
  D1.部署ＣＤ
  , D1.コースＣＤ
  , D1.ＳＥＱ
  , D1.得意先ＣＤ
  , D1.日付
  , D1.締日
ORDER BY
  D1.日付
  , D1.部署ＣＤ
  , D1.コースＣＤ
  , D1.ＳＥＱ
";

        $DataList = DB::select(DB::raw($sql));

        return response()->json($DataList);
    }
}
