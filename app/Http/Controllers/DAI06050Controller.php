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

class DAI06050Controller extends Controller
{

    /**
     * Search
     */
    public function Search($vm)
    {
        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;
        $BushoCd = $vm->BushoCd;

        $sql = "
        WITH COURSE_BASE AS
        (
        SELECT DISTINCT
            TOK.部署CD ,
            TOK.得意先CD ,
            M_COURSE.コース区分,
            M_COURSE.コースCD,
            M_COURSE.コース名,
            T_COURSE.ＳＥＱ,
            CONVERT(varchar, TOK.部署CD) + '_' +
            CONVERT(varchar, TOK.得意先CD) + '_' +
            CONVERT(varchar, ISNULL(M_COURSE.コース区分, 0)) + '_' +
            CONVERT(varchar, ISNULL(M_COURSE.コースCD, 0)) AS GROUPKEY
        FROM
            (
            SELECT
                *
            FROM 得意先マスタ M1
            WHERE
                0=0
            AND 得意先CD IN (
                            SELECT 得意先CD
                            FROM コーステーブル
                            WHERE
                                部署CD = $BushoCd
                            )
                ) TOK
        LEFT OUTER JOIN
            (
            SELECT DISTINCT 部署CD,得意先CD,コースCD,ＳＥＱ
            FROM [コーステーブル]) T_COURSE ON
                    TOK.部署CD = T_COURSE.部署CD
                AND TOK.得意先CD = T_COURSE.得意先CD
        LEFT OUTER JOIN
            (
            SELECT DISTINCT 部署CD,コースCD,コース名,コース区分
            FROM [コースマスタ]) M_COURSE ON
                    T_COURSE.部署CD = M_COURSE.部署CD
                AND T_COURSE.コースCD = M_COURSE.コースCD
        ),

        得意先コースマスタ AS
        (
        SELECT
            COURSE_BASE.*
        FROM
            (
            SELECT DISTINCT 部署CD, 得意先CD,MIN(GROUPKEY) AS GROUPKEY FROM COURSE_BASE GROUP BY 部署CD, 得意先CD
            ) MIN_COURSE, COURSE_BASE
        WHERE
            MIN_COURSE.GROUPKEY = COURSE_BASE.GROUPKEY
        AND MIN_COURSE.部署CD   = COURSE_BASE.部署CD
        ),

        --- 請求 ----------------------------------------------------
        ZAN_SEIKYU AS
        (
        SELECT
             T1.請求先ＣＤ
            ,T1.今回請求額
        FROM 請求データ T1
            INNER JOIN (
                SELECT 部署CD, 請求先CD, MAX(請求日付) AS 請求日付 FROM 請求データ
                    WHERE
                        部署CD = $BushoCd
                    AND 請求日付 < '$DateStart'
        GROUP BY 部署CD, 請求先CD
        ) T2 ON
            T1.請求日付   = T2.請求日付
        AND T1.部署ＣＤ   = T2.部署CD
        AND T1.請求先ＣＤ = T2.請求先ＣＤ
        ),

        --- 売上 ----------------------------------------------------
        ZAN_URIAGE AS
        (
        SELECT 得意先ＣＤ
            ,SUM(現金金額 - 現金値引) + SUM(掛売金額 - 掛売値引) AS 売上金額
        FROM 売上データ明細 T1
            LEFT JOIN (
                SELECT 請求先CD, MAX(請求日付) AS 請求日付 FROM 請求データ
                WHERE 部署CD = $BushoCd
                    AND 請求日付 < '$DateStart'
                GROUP BY 請求先CD
                ) T2 ON  T1.得意先ＣＤ = T2.請求先ＣＤ
        WHERE
            T1.部署ＣＤ     = $BushoCd
        AND T1.商品区分     = 9     -- チケット
        AND T1.売掛現金区分 = 1     -- 売掛
        AND T1.日付 > T2.請求日付
        AND T1.日付 < '$DateStart'
        GROUP BY 得意先ＣＤ
        ),

        --- 入金 ----------------------------------------------------
        ZAN_NYUKIN AS
        (
        SELECT
             得意先ＣＤ
            ,SUM(現金 + 小切手 + 振込 + バークレー + その他 + 相殺 + 値引) AS 入金金額
        FROM
            入金データ T1
            LEFT JOIN (
            SELECT 請求先CD, MAX(請求日付) AS 請求日付 FROM 請求データ
                WHERE 部署CD = $BushoCd
                AND 請求日付 < '$DateStart'
                GROUP BY 請求先CD
            ) T2 ON  T1.得意先ＣＤ = T2.請求先ＣＤ
        WHERE
            T1.部署ＣＤ = $BushoCd
        AND T1.入金日付 > T2.請求日付
        AND T1.入金日付 < '$DateStart'
        GROUP BY 得意先ＣＤ
        ),

        抽出データ AS
        (
        select
              URIAGE_MEISAI.部署ＣＤ
            , BUSYO.部署名
            , COU.コースＣＤ
            , COU.コース名
            , COU.コース区分
            , COU.ＳＥＱ
            , URIAGE_MEISAI.得意先ＣＤ
            , TOKUISAKI.得意先名
            , URIAGE_MEISAI.商品ＣＤ
            , TOKUISAKI.得意先名 + '（' + SHOHIN.商品名 + '）' as 得意先商品名
            , URIAGE_MEISAI.日付
            , REPLACE(DATENAME(W, URIAGE_MEISAI.日付), '曜日', '') AS 曜日
            , URIAGE_MEISAI.現金金額
            , URIAGE_MEISAI.掛売金額
            , (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額) as チケット券販売額
            , URIAGE_MEISAI.売掛現金区分
            , (case when URIAGE_MEISAI.売掛現金区分 = 0 then (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額) else 0 end) as 現金入金額
            , ((URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額) - (case when URIAGE_MEISAI.売掛現金区分 = 0 then (URIAGE_MEISAI.現金金額 + URIAGE_MEISAI.掛売金額) else 0 end)) as 残高
            , ISNULL(ZAN_SEIKYU.今回請求額, 0) + ISNULL(ZAN_URIAGE.売上金額, 0) - ISNULL(ZAN_NYUKIN.入金金額, 0) as 得意先残
            , 0 as 累計残
        from
            売上データ明細 URIAGE_MEISAI
            inner join 商品マスタ SHOHIN on
                URIAGE_MEISAI.商品ＣＤ = SHOHIN.商品ＣＤ
            left join 部署マスタ BUSYO on
                URIAGE_MEISAI.部署ＣＤ = BUSYO.部署CD
            inner join 得意先コースマスタ COU on
                URIAGE_MEISAI.得意先ＣＤ   = COU.得意先ＣＤ
            left join 得意先マスタ TOKUISAKI on
                URIAGE_MEISAI.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
            left join ZAN_SEIKYU on
                URIAGE_MEISAI.得意先ＣＤ = ZAN_SEIKYU.請求先ＣＤ
            left join ZAN_URIAGE on
                URIAGE_MEISAI.得意先ＣＤ = ZAN_URIAGE.得意先ＣＤ
            left join ZAN_NYUKIN on
                URIAGE_MEISAI.得意先ＣＤ = ZAN_NYUKIN.得意先ＣＤ
        where
                URIAGE_MEISAI.部署ＣＤ = $BushoCd
            and URIAGE_MEISAI.日付 >= '$DateStart'
            and URIAGE_MEISAI.日付 <= '$DateEnd'
            and URIAGE_MEISAI.得意先ＣＤ >= 0
            and URIAGE_MEISAI.得意先ＣＤ <= 9999999
            and URIAGE_MEISAI.商品区分 = 9
        ),

        抽出データ2 AS
        (
        SELECT 1 AS 処理区分, * FROM 抽出データ
        UNION ALL
        SELECT
            2 AS 処理区分,
            T.部署ＣＤ,
            T.部署名,
            T.コースＣＤ,
            T.コース名,
            T.コース区分,
            T.ＳＥＱ,
            N.得意先ＣＤ,
            T.得意先名,
            T.商品ＣＤ,
            T.得意先商品名,
            N.入金日付 AS 日付,
            REPLACE(DATENAME(W, N.入金日付), '曜日', '') AS 曜日,
            0 AS 現金金額,
            0 AS 掛売金額,
            0 AS チケット券販売額,
            0 AS 売掛現金区分,
            (N.現金 + N.小切手 + N.振込 + N.バークレー + N.その他 + N.相殺 + N.値引) AS 現金入金額,
            0 AS 残高,
            0 AS 得意先残,
            0 AS 累計残
        FROM
            入金データ N
            INNER JOIN
                (
                SELECT DISTINCT
                    部署ＣＤ,
                    部署名,
                    コースＣＤ,
                    コース名,
                    コース区分,
                    ＳＥＱ,
                    得意先ＣＤ,
                    得意先名,
                    商品ＣＤ,
                    得意先商品名
                FROM 抽出データ
                WHERE
                    部署ＣＤ = $BushoCd
                ) T on
                    T.得意先ＣＤ = N.得意先ＣＤ
                AND 入金日付 >= '$DateStart'
                AND 入金日付 <= '$DateEnd'
        ),

        抽出データ3 AS
        (
        SELECT
            処理区分,
            部署ＣＤ,
            部署名,
            コースＣＤ,
            コース名,
            コース区分,
            ＳＥＱ,
            得意先ＣＤ,
            得意先名,
            商品ＣＤ,
            得意先商品名,
            ROW_NUMBER() OVER (PARTITION BY コースＣＤ, ＳＥＱ ORDER BY コースＣＤ, ＳＥＱ, 日付) AS 日付順,
            日付,
            曜日,
            現金金額,
            掛売金額,
            チケット券販売額,
            売掛現金区分,
            現金入金額,
            残高,
            MAX(得意先残) OVER (PARTITION BY コースＣＤ, ＳＥＱ ORDER BY ＳＥＱ ASC) AS 得意先残,
            累計残
        FROM
            抽出データ2
        ),

        抽出データ4 AS
        (
		SELECT
			0 AS 処理区分,
            部署ＣＤ,
            部署名,
            コースＣＤ,
            コース名,
            ＳＥＱ,
            得意先ＣＤ,
            得意先名,
            得意先商品名,
			0 AS 日付順,
            NULL AS 日付,
            NULL AS 曜日,
            NULL AS 現金金額,
            NULL AS チケット券販売額,
            NULL AS 現金入金額,
            MAX(得意先残) AS 残額
		FROM
			抽出データ2
		GROUP BY
            部署ＣＤ,
            部署名,
            コースＣＤ,
            コース名,
            ＳＥＱ,
            得意先ＣＤ,
            得意先名,
            得意先商品名
		UNION
        SELECT
			処理区分,
            部署ＣＤ,
            部署名,
            コースＣＤ,
            コース名,
            ＳＥＱ,
            得意先ＣＤ,
            得意先名,
            得意先商品名,
			日付順,
            日付,
            曜日,
            現金金額,
            チケット券販売額,
            現金入金額,
            得意先残 + SUM(ISNULL(チケット券販売額, 0)) OVER (PARTITION BY コースＣＤ, ＳＥＱ ORDER BY コースＣＤ, ＳＥＱ, 日付, 日付順) -
                       SUM(ISNULL(現金入金額, 0)) OVER (PARTITION BY コースＣＤ, ＳＥＱ ORDER BY コースＣＤ, ＳＥＱ, 日付, 日付順) AS 残額
        FROM
            抽出データ3
		)

		SELECT * FROM 抽出データ4
        ORDER BY
            コースＣＤ, ＳＥＱ, 日付
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
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
