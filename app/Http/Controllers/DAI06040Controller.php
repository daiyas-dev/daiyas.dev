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
use Illuminate\Support\Facades\Log;

class DAI06040Controller extends Controller
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
        WITH チケット発行得意先 AS
        (
            SELECT DISTINCT
                TK.*
            FROM
                得意先マスタ TK
                LEFT OUTER JOIN チケット発行 TH
                    ON  TH.発行日 < '$DateEnd'
                    AND TH.得意先ＣＤ=TK.得意先ＣＤ
                LEFT OUTER JOIN チケット調整 TC
                    ON  TC.日付 < '$DateEnd'
                    AND TC.得意先ＣＤ=TK.得意先ＣＤ
            WHERE
                TH.得意先ＣＤ IS NOT NULL OR TC.得意先ＣＤ IS NOT NULL
        ),

        COURSE_BASE AS
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
        FROM (SELECT
            *
        FROM
            チケット発行得意先 M1
        WHERE
            0=0
            AND 得意先CD IN (
                            SELECT 得意先CD
                            FROM
                                コーステーブル
                            WHERE
                                部署CD = $BushoCd
                            )
            ) TOK
            LEFT OUTER JOIN
                (
                    SELECT DISTINCT
                            部署CD
                        ,得意先CD
                        ,コースCD
                        ,ＳＥＱ
                    FROM
                        [コーステーブル]
                ) T_COURSE ON
                    TOK.部署CD = T_COURSE.部署CD
                AND TOK.得意先CD = T_COURSE.得意先CD
            LEFT OUTER JOIN
                (
                    SELECT DISTINCT 部署CD,コースCD,コース名,コース区分 FROM [コースマスタ]
                ) M_COURSE ON
                    T_COURSE.部署CD = M_COURSE.部署CD
                AND T_COURSE.コースCD = M_COURSE.コースCD
        ),

        得意先コースマスタ AS
        (
            SELECT
                COURSE_BASE.*
            FROM
                (
                    -- コース区分が最小のものを採用
                    SELECT DISTINCT 部署CD, 得意先CD,MIN(GROUPKEY) AS GROUPKEY FROM COURSE_BASE GROUP BY 部署CD, 得意先CD
                ) MIN_COURSE, COURSE_BASE
            WHERE
                MIN_COURSE.GROUPKEY = COURSE_BASE.GROUPKEY
            AND MIN_COURSE.部署CD   = COURSE_BASE.部署CD
        ),

        チケット残_SV AS (
            select
                T1.得意先ＣＤ
                ,( select min(商品CD) from 得意先単価マスタ where 得意先CD = T1.得意先ＣＤ ) as 商品ＣＤ
                ,ISNULL(チケット内数,0) - ISNULL(チケット弁当数,0) - ISNULL(チケット減数,0) as チケット残数
                , cast(ISNULL(SV内数,0) as decimal(10,1)) - cast(ISNULL(SVチケット弁当数,0) as decimal(10,1)) - cast(ISNULL(SV減数,0) as decimal(10,1)) as チケットSV
            from
                チケット発行得意先 T1
                left outer join
                (   -- チケット販売
                select
                    得意先ＣＤ
                    , SUM(チケット内数) as チケット内数
                    , SUM(SV内数) SV内数
                from チケット発行
                where
                    発行日 < '$DateStart'
                    and 廃棄 = 0
                group by 得意先ＣＤ
                ) T0 on T0.得意先ＣＤ = T1.得意先ＣＤ
                left outer join
                (   -- チケットでの売上
                select
                    得意先ＣＤ
                    , SUM(掛売個数) as チケット弁当数
                from 売上データ明細
                where
                    日付 < '$DateStart'
                    and 売掛現金区分 = 2
                    and 商品ＣＤ not in (select 商品ＣＤ from 商品マスタ where 商品区分 = 9)
                group by 得意先ＣＤ
                ) T2 on T1.得意先ＣＤ = T2.得意先ＣＤ
                left outer join
                (   -- サービスチケットでの売上
                select
                    得意先ＣＤ
                    , SUM(掛売個数) as SVチケット弁当数
                from 売上データ明細
                where
                    日付 < '$DateStart'
                    and 売掛現金区分 = 4
                group by 得意先ＣＤ
                ) T3 on T1.得意先ＣＤ = T3.得意先ＣＤ

                left outer join
                (   -- サービスチケットでの売上
                select
                    得意先ＣＤ
                    , SUM(掛売個数) as チケット売上
                from 売上データ明細
                where
                    日付 < '$DateStart'
                    and 売掛現金区分 = 4
                group by 得意先ＣＤ
                ) T4 on T1.得意先ＣＤ = T4.得意先ＣＤ
                left outer join
                (   -- チケット調整
                select
                    得意先ＣＤ
                    , SUM(チケット減数) as チケット減数
                    , SUM(SV減数) as SV減数
                from チケット調整
                where
                    日付 < '$DateStart'
                group by 得意先ＣＤ
                ) T5 on T1.得意先ＣＤ = T5.得意先ＣＤ
        ),

        チケット残数 AS
        (
        select
             得意先コースマスタ.コースＣＤ
            ,得意先コースマスタ.コース名
            ,得意先コースマスタ.ＳＥＱ
            ,得意先マスタ.得意先ＣＤ
            ,得意先マスタ.得意先名 + '（' + 商品マスタ.商品名 + '）' as 得意先商品名
            ,NULL AS 日付
            ,NULL AS 曜日
            ,NULL AS チケット販売
            ,NULL AS チケット販売SV
            ,NULL AS 弁当売上
            ,NULL AS 弁当売上SV
            ,NULL AS 調整
            ,NULL AS 調整SV
            ,NULL AS チケット内数
            ,NULL AS SV内数
            ,チケット残数
            ,チケットSV AS チケット残数SV
        from
            チケット残_SV MAIN
            inner join      得意先マスタ       on MAIN.得意先ＣＤ = 得意先マスタ.得意先ＣＤ
            left outer join 得意先コースマスタ on 得意先コースマスタ.得意先ＣＤ = MAIN.得意先ＣＤ
            left outer join 商品マスタ         on 商品マスタ.商品ＣＤ = MAIN.商品ＣＤ
        where
            (チケット残数 > 0 OR チケットSV > 0)
        and 得意先マスタ.部署ＣＤ = $BushoCd
        and 得意先コースマスタ.コースＣＤ is not null
        ),

        抽出データ AS
        (
            select
             q.*
            ,rank() over(partition by 得意先名,日付 order by 得意先名,日付,商品ＣＤ)as RNK
            from(
                select
                        URIAGE_MEISAI.部署ＣＤ
                    ,BUSYO.部署名
                    ,COU.コースＣＤ
                    ,COU.コース名
                    ,COU.コース区分
                    ,COU.ＳＥＱ
                    ,URIAGE_MEISAI.得意先ＣＤ
                    ,TOKUISAKI.得意先名
                    ,URIAGE_MEISAI.商品ＣＤ
                    ,TOKUISAKI.得意先名 + '（' + SHOHIN.商品名 + '）' as 得意先商品名
                    ,URIAGE_MEISAI.日付
                    ,REPLACE(DATENAME(W, URIAGE_MEISAI.日付), '曜日', '') AS 曜日
                    ,URIAGE_MEISAI.売掛現金区分
                    ,(case when URIAGE_MEISAI.売掛現金区分 = 2
                        then URIAGE_MEISAI.掛売個数
                        when  URIAGE_MEISAI.売掛現金区分 = 4
                        then NULL end )as 弁当売上
                    ,(case when URIAGE_MEISAI.売掛現金区分 = 2
                        then NULL
                        when  URIAGE_MEISAI.売掛現金区分 = 4
                        then URIAGE_MEISAI.掛売個数 end) as 弁当売上SV
                    ,SHOHIN.商品区分
                    ,URIAGE_MEISAI.食事区分
                    ,(ISNULL(URIAGE_MEISAI.現金個数, 0) + ISNULL(URIAGE_MEISAI.掛売個数, 0)) as 個数
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
                where
                    URIAGE_MEISAI.部署ＣＤ = $BushoCd
                and URIAGE_MEISAI.日付 >= '$DateStart'
                and URIAGE_MEISAI.日付 <= '$DateEnd'
                and URIAGE_MEISAI.売掛現金区分 in (0, 1, 2, 4)
                union
                select distinct
                        BUSYO.部署ＣＤ
                    ,BUSYO.部署名
                    ,COU.コースＣＤ
                    ,COU.コース名
                    ,COU.コース区分
                    ,COU.ＳＥＱ
                    ,チケット調整.得意先ＣＤ as 得意先ＣＤ
                    ,TOKUISAKI.得意先名 as 得意先名
                    ,チケット調整.商品ＣＤ
                    ,TOKUISAKI.得意先名 + '（' + SHOHIN.商品名 + '）' as 得意先商品名
                    ,チケット調整.日付
                    ,REPLACE(DATENAME(W, チケット調整.日付), '曜日', '') AS 曜日
                    ,9999 as 	売掛現金区分
                    ,0 as 	弁当売上
                    ,0 as 	弁当売上SV
                    ,9999 as 	商品区分
                    ,9 as 	食事区分
                    ,0 as 	個数
                from
                    チケット調整
                    inner join 得意先コースマスタ COU on
                        チケット調整.得意先ＣＤ = COU.得意先ＣＤ
                    left join 部署マスタ BUSYO on
                        COU.部署ＣＤ = BUSYO.部署CD
                    left join 得意先マスタ TOKUISAKI on
                        チケット調整.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                    inner join 商品マスタ SHOHIN on
                        チケット調整.商品ＣＤ = SHOHIN.商品ＣＤ
                    left outer join 売上データ明細 on
                        チケット調整.得意先ＣＤ = 売上データ明細.得意先ＣＤ
                        and 売上データ明細.日付 >= '$DateStart'
                        and 売上データ明細.日付 <= '$DateEnd'
                        and 売上データ明細.部署CD   = $BushoCd
                        and 売上データ明細.商品区分 = 9
                        and 売上データ明細.日付 >= チケット調整.日付
                        and 売上データ明細.日付 <= チケット調整.日付
                where チケット調整.日付 >= '$DateStart'
                    and チケット調整.日付 <= '$DateEnd'
                    and 売上データ明細.日付 is null
            )q
        ),

        抽出データ2 AS
        (
        SELECT
            T.得意先ＣＤ,
            T.得意先商品名,
            部署名,
            コースＣＤ,
            コース名,
            コース区分,
            ＳＥＱ,
            商品区分,
            個数,
            IIF(((売掛現金区分 = 2 OR 売掛現金区分 = 4) AND 商品区分 != 9) OR 商品区分 = 9, T.日付, NULL) AS 日付,
            IIF(((売掛現金区分 = 2 OR 売掛現金区分 = 4) AND 商品区分 != 9) OR 商品区分 = 9, T.曜日, NULL) AS 曜日,
            IIF(((売掛現金区分 = 2 OR 売掛現金区分 = 4) AND 商品区分 != 9) OR 商品区分 = 9, T.弁当売上, NULL) AS 弁当売上,
            IIF(((売掛現金区分 = 2 OR 売掛現金区分 = 4) AND 商品区分 != 9) OR 商品区分 = 9, T.弁当売上SV, NULL) AS 弁当売上SV,
            --IIF(((売掛現金区分 = 2 OR 売掛現金区分 = 4) AND 商品区分 != 9) OR 商品区分 = 9, C.発行日, NULL) AS 発行日,
            --IIF(((売掛現金区分 = 2 OR 売掛現金区分 = 4) AND 商品区分 != 9) OR 商品区分 = 9, C.曜日, NULL) AS 曜日2,
            --IIF(((売掛現金区分 = 2 OR 売掛現金区分 = 4) AND 商品区分 != 9) OR 商品区分 = 9, C.チケット内数, NULL) AS チケット内数,
            --IIF(((売掛現金区分 = 2 OR 売掛現金区分 = 4) AND 商品区分 != 9) OR 商品区分 = 9, C.SV内数, NULL) AS SV内数,
            IIF(商品区分 = 9, ROW_NUMBER() OVER (PARTITION BY コースＣＤ, ＳＥＱ, T.得意先ＣＤ, 商品区分, T.日付 ORDER BY 発行日 DESC), NULL) AS 発行日順,
            IIF(商品区分 = 9, C.発行日, NULL) AS 発行日,
            IIF(商品区分 = 9, C.曜日, NULL) AS 曜日2,
            IIF(商品区分 = 9, C.チケット内数, NULL) AS チケット内数,
            IIF(商品区分 = 9, C.SV内数, NULL) AS SV内数,
            A.チケット減数 * -1 AS 調整,
            A.SV減数 * -1.0 AS 調整SV,
            Z.チケット残数,
            Z.チケットSV
        FROM
            抽出データ T
        LEFT OUTER JOIN
            (
            SELECT
                得意先ＣＤ,
                発行日,
                REPLACE(DATENAME(W, 発行日), '曜日', '') AS 曜日,
                MAX(チケット内数) as チケット内数,
                MAX(SV内数)       as SV内数
            FROM チケット発行 group by 得意先ＣＤ, 発行日
            ) C ON
                C.得意先ＣＤ = T.得意先ＣＤ
            AND C.発行日 > DATEADD(day, -14, T.日付)
            AND C.発行日 <= T.日付
        LEFT OUTER JOIN
            (
            SELECT
                得意先ＣＤ,
                日付,
                REPLACE(DATENAME(W, 日付), '曜日', '') AS 曜日,
                チケット減数,
                SV減数
            FROM チケット調整
            WHERE
                日付 >= '$DateStart'
            AND 日付 <= '$DateEnd'
            ) A ON
                A.得意先ＣＤ = T.得意先ＣＤ
            AND A.日付 = T.日付
			AND T.RNK=1
        LEFT OUTER JOIN
            チケット残_SV Z ON
                Z.得意先ＣＤ = T.得意先ＣＤ
        WHERE
            T.日付 IS NOT NULL
        ),

        抽出データ3 AS
        (
        SELECT DISTINCT
            IIF(商品区分=9, ROW_NUMBER() OVER (PARTITION BY コースＣＤ, ＳＥＱ, 得意先ＣＤ, 商品区分, 日付 ORDER BY 商品区分), NULL) AS ROWNUMBER,
            コースＣＤ,
            コース名,
            ＳＥＱ,
            得意先ＣＤ,
            得意先商品名,
            FIRST_VALUE(得意先商品名) OVER (PARTITION BY コースＣＤ, ＳＥＱ ORDER BY コースＣＤ, ＳＥＱ, 日付) AS 得意先商品,
            日付,
            曜日,
            弁当売上,
            弁当売上SV,
            調整,
            調整SV,
            チケット内数,
            SV内数,
            IIF(商品区分 = 9, チケット内数 * 個数, NULL) AS チケット販売,
            IIF(商品区分 = 9, SV内数 * 個数, NULL) AS チケット販売SV,
            --MAX(チケット残数) OVER (PARTITION BY ＳＥＱ ORDER BY ＳＥＱ ASC) AS チケット残数,
            --MAX(チケットSV) OVER (PARTITION BY ＳＥＱ ORDER BY ＳＥＱ ASC) AS チケット残数SV
            IIF(発行日 < '$DateStart', MAX(チケット残数) OVER (PARTITION BY コースＣＤ, ＳＥＱ ORDER BY コースＣＤ, ＳＥＱ ASC) - (チケット内数 * 個数), MAX(チケット残数) OVER (PARTITION BY コースＣＤ, ＳＥＱ ORDER BY コースＣＤ, ＳＥＱ ASC)) AS チケット残数,
            IIF(発行日 < '$DateStart', MAX(チケットSV) OVER (PARTITION BY コースＣＤ, ＳＥＱ ORDER BY コースＣＤ, ＳＥＱ ASC) - (SV内数 * 個数), MAX(チケットSV) OVER (PARTITION BY コースＣＤ, ＳＥＱ ORDER BY コースＣＤ, ＳＥＱ ASC)) AS チケット残数SV
        FROM
            抽出データ2
        WHERE
            日付 IS NOT NULL AND (発行日順 = 1 OR 発行日順 IS NULL)
        ),

        抽出データ4 AS
        (
        SELECT
            コースＣＤ,
            MIN(コース名) AS コース名,
            ＳＥＱ,
            得意先ＣＤ,
            MIN(得意先商品) AS 得意先商品名,
            日付,
            曜日,
            SUM(チケット販売) AS チケット販売,
            SUM(チケット販売SV) AS チケット販売SV,
            SUM(弁当売上) AS 弁当売上,
            SUM(弁当売上SV) AS 弁当売上SV,
            SUM(調整) AS 調整,
            SUM(調整SV) AS 調整SV,
            SUM(チケット内数) AS チケット内数,
            SUM(SV内数) AS SV内数,
            MIN(ISNULL(チケット残数, 0)) AS チケット残数,
            MIN(ISNULL(チケット残数SV, 0)) AS チケット残数SV,
            SUM(SUM(ISNULL(チケット販売, 0)+ISNULL(調整, 0))) OVER (PARTITION BY コースＣＤ, ＳＥＱ ORDER BY コースＣＤ, ＳＥＱ, 日付) AS チケット販売累計,
            SUM(SUM(ISNULL(チケット販売SV, 0)+ISNULL(調整SV, 0))) OVER (PARTITION BY コースＣＤ, ＳＥＱ ORDER BY コースＣＤ, ＳＥＱ, 日付) AS チケット販売累計SV,
            SUM(SUM(ISNULL(弁当売上, 0))) OVER (PARTITION BY コースＣＤ, ＳＥＱ ORDER BY コースＣＤ, ＳＥＱ, 日付) AS 弁当売上累計,
            SUM(SUM(ISNULL(弁当売上SV, 0))) OVER (PARTITION BY コースＣＤ, ＳＥＱ ORDER BY コースＣＤ, ＳＥＱ, 日付) AS 弁当売上累計SV
        FROM
            抽出データ3
        WHERE
            ROWNUMBER = 1 OR ROWNUMBER IS NULL
        GROUP BY
            コースＣＤ, ＳＥＱ, 得意先ＣＤ, 日付, 曜日
        ),

        抽出データ5 AS
        (
        SELECT
            コースＣＤ,
            コース名,
            ＳＥＱ,
            得意先ＣＤ,
            得意先商品名,
            NULL AS 日付,
            NULL AS 曜日,
            NULL AS チケット販売,
            NULL AS チケット販売SV,
            NULL AS 弁当売上,
            NULL AS 弁当売上SV,
            NULL AS 調整,
            NULL AS 調整SV,
            NULL AS チケット内数,
            NULL AS SV内数,
            MIN(チケット残数) OVER (PARTITION BY コースＣＤ, ＳＥＱ ORDER BY コースＣＤ, ＳＥＱ ASC) AS チケット残数,
            MIN(チケット残数SV) OVER (PARTITION BY コースＣＤ, ＳＥＱ ORDER BY コースＣＤ, ＳＥＱ ASC) AS チケット残数SV
        FROM 抽出データ4
        GROUP BY
            コースＣＤ, コース名, ＳＥＱ, 得意先ＣＤ, 得意先商品名, チケット残数, チケット残数SV
        UNION
        SELECT
            コースＣＤ,
            コース名,
            ＳＥＱ,
            得意先ＣＤ,
            得意先商品名,
            日付,
            曜日,
            チケット販売,
            チケット販売SV,
            弁当売上,
            弁当売上SV,
            調整,
            調整SV,
            チケット内数,
            SV内数,
            チケット販売累計 + MIN(チケット残数) OVER (PARTITION BY コースＣＤ, ＳＥＱ ORDER BY コースＣＤ, ＳＥＱ ASC) - 弁当売上累計 AS チケット残数,
            チケット販売累計SV + MIN(チケット残数SV) OVER (PARTITION BY コースＣＤ, ＳＥＱ ORDER BY コースＣＤ, ＳＥＱ ASC) - 弁当売上累計SV AS チケット残数SV
        FROM
            抽出データ4
        )

        SELECT
            1 AS 処理区分,
            ROW_NUMBER() OVER (ORDER BY T.コースＣＤ, T.ＳＥＱ, T.得意先ＣＤ, T.日付) AS ROWNUMBER,
            T.*
        FROM 抽出データ5 T
        UNION
        SELECT
            2 AS 処理区分
            ,ROW_NUMBER() OVER (ORDER BY ASCII(Z.得意先ＣＤ)) AS ROWNUMBER
            ,Z.コースＣＤ
            ,Z.コース名
            ,Z.ＳＥＱ
            ,Z.得意先ＣＤ
            ,Z.得意先商品名
            ,Z.日付
            ,Z.曜日
            ,Z.チケット販売
            ,Z.チケット販売SV
            ,Z.弁当売上
            ,Z.弁当売上SV
            ,0-CT.sum_チケット減数 as 調整
            ,0-CT.sum_SV減数 AS 調整SV
            ,Z.チケット内数
            ,Z.SV内数
            ,Z.チケット残数-CT.sum_チケット減数
            ,Z.チケット残数SV-CT.sum_SV減数
        FROM
            チケット残数 Z
            LEFT OUTER JOIN (
                SELECT DISTINCT
                    得意先ＣＤ
                FROM
                    抽出データ5
            ) D
                ON D.得意先ＣＤ = Z.得意先ＣＤ
            LEFT OUTER JOIN (
                SELECT
                    得意先ＣＤ
                    , SUM(チケット減数) as sum_チケット減数
                    , SUM(SV減数) as sum_SV減数
                FROM チケット調整
                WHERE 日付 >= '$DateStart' and 日付 <= '$DateEnd'
                GROUP BY 得意先ＣＤ
                ) CT on Z.得意先ＣＤ = CT.得意先ＣＤ and not exists(select 1 from 売上データ明細 ud where ud.得意先ＣＤ=Z.得意先ＣＤ and  ud.日付 >= '$DateStart' and ud.日付 <= '$DateEnd')
            WHERE
            IIF(D.得意先ＣＤ IS NULL, 2, 1) = 2
        ";

        //Log::info('DAI06040_Search_SQL:\n' . $sql);

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
