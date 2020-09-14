<?php

namespace App\Http\Controllers;

use App\Models\コーステーブル;
use App\Models\コース別明細データ;
use App\Models\各種テーブル;
use App\Models\商品マスタ;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Facades\DB as IlluminateDB;

class DAI01110Controller extends Controller
{
    /**
     * GetProductList
     */
    public function GetProductList($request)
    {
        $BushoCd = $request->BushoCd;

        $sql = "
            WITH SUB AS (
                SELECT
                    サブ各種CD2
                FROM 各種テーブル
                WHERE 各種CD=26
                AND	サブ各種CD1=$BushoCd
            )
            SELECT TOP(7)
                各種テーブル.*
            FROM 各種テーブル, SUB
            WHERE 各種CD = (
                CASE
                    WHEN SUB.サブ各種CD2=2 THEN 27
                    WHEN SUB.サブ各種CD2 IS NOT NULL THEN 14
                    ELSE 33
                END
            )
        ";

        $Result = DB::select($sql);

        return response()->json($Result);
    }

    /**
     * GetIdouDataList
     */
    public function GetIdouDataList($request)
    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;
        $CourseCd = $request->CourseCd;

        $sql = "
            DECLARE @日付WK              DATETIME
            set @日付WK  = CONVERT(datetime, '$TargetDate', $BushoCd);

            WITH TAISHO_SHOHIN AS (
            SELECT
            CONVERT(int,KAKU.サブ各種CD1) AS 商品ＣＤ
            ,KAKU.各種略称 AS 商品名
            ,KAKU.行NO AS ソート順
            FROM
            各種テーブル KAKU
            WHERE
            KAKU.各種CD = 33
            ),
            MOBILE_MOTI AS(
            SELECT
            T1.部署ＣＤ
            ,T1.コースＣＤ
            ,T1.商品ＣＤ
            ,SUM(T1.個数) AS 個数
            FROM
            モバイル_持ち出し入力 T1
            WHERE
            T1.持ち出し日付 = @日付WK
            GROUP BY
            T1.部署ＣＤ
            ,T1.コースＣＤ
            ,T1.商品ＣＤ
            ),
            MOBILE_HANBAI AS(
            SELECT
                T1.部署ＣＤ
                ,T1.コースＣＤ
                ,T1.商品ＣＤ
                ,SUM(CASE
                WHEN T1.実績入力 = 1 THEN T1.実績数
                WHEN T1.注文入力 = 1 THEN T1.注文数
                WHEN T1.見込入力 = 1 THEN T1.見込数
                ELSE 0
                END) 販売数
            FROM
                (
            SELECT
                SHO_SHU.部署ＣＤ
                ,SHO_SHU.コースＣＤ
                ,SHO_SHU.得意先ＣＤ
                ,SHO_MST.商品ＣＤ
                ,SHO_MST.商品区分
                ,SHO_SHU.実績入力
                ,SHO_SHU.実績数
                ,SHO_SHU.注文入力
                ,SHO_SHU.注文数
                ,SHO_SHU.見込入力
                ,SHO_SHU.見込数
            FROM
                モバイル_販売入力 SHO_SHU
                LEFT OUTER JOIN 商品マスタ SHO_MST ON
                SHO_SHU.主食ＣＤ = SHO_MST.商品ＣＤ
            WHERE
                SHO_SHU.部署ＣＤ = $BushoCd
                AND SHO_SHU.日付 = @日付WK
                AND SHO_SHU.主食ＣＤ != 0
                AND (SHO_SHU.実績数 != 0 OR SHO_SHU.注文数 != 0 OR SHO_SHU.見込数 != 0)
            UNION ALL
            SELECT
                SHO_FUKU.部署ＣＤ
                ,SHO_FUKU.コースＣＤ
                ,SHO_FUKU.得意先ＣＤ
                ,SHO_MST.商品ＣＤ
                ,SHO_MST.商品区分
                ,SHO_FUKU.実績入力
                ,SHO_FUKU.実績数
                ,SHO_FUKU.注文入力
                ,SHO_FUKU.注文数
                ,SHO_FUKU.見込入力
                ,SHO_FUKU.見込数
            FROM
                モバイル_販売入力 SHO_FUKU
                LEFT OUTER JOIN 商品マスタ SHO_MST ON
                SHO_FUKU.副食ＣＤ = SHO_MST.商品ＣＤ
            WHERE
                SHO_FUKU.部署ＣＤ = $BushoCd
                AND SHO_FUKU.日付 = @日付WK
                AND SHO_FUKU.副食ＣＤ != 0
                AND (SHO_FUKU.実績数 != 0 OR SHO_FUKU.注文数 != 0 OR SHO_FUKU.見込数 != 0)
                ) T1
                LEFT OUTER JOIN (
                SELECT
                    T1.部署ＣＤ
                    ,T1.コースＣＤ
                    ,T1.得意先ＣＤ
                    ,MAX(CAST(T1.注文入力 AS INT)) AS 注文入力
                FROM
                    モバイル_販売入力 T1
                WHERE
                    T1.部署ＣＤ = $BushoCd
                    AND T1.日付 = @日付WK
                GROUP BY
                    T1.部署ＣＤ
                    ,T1.コースＣＤ
                    ,T1.得意先ＣＤ
                ) T3 ON
                T1.部署ＣＤ = T3.部署ＣＤ
                AND T1.コースＣＤ = T3.コースＣＤ
                AND T1.得意先ＣＤ = T3.得意先ＣＤ
            WHERE
                (
                (T1.実績入力 = 1 AND T1.実績数 != 0)
                OR( T3.注文入力 = 1 AND T1.注文入力 = 1 AND T1.注文数 != 0)
                OR (T3.注文入力 = 0 AND T1.見込入力 = 1 AND T1.見込数 != 0)
                )
            GROUP BY
                T1.部署ＣＤ
                ,T1.コースＣＤ
                ,T1.商品ＣＤ
            ),
            MOBILE_IDOU_WATASHI AS(
            SELECT
                T1.部署ＣＤ
                ,T1.コースＣＤ
                ,T1.商品ＣＤ
                ,SUM(個数) AS 個数
            FROM
                モバイル_移動入力 T1
            WHERE
                T1.日付 = @日付WK
            GROUP BY
                T1.部署ＣＤ
                ,T1.コースＣＤ
                ,T1.商品ＣＤ
            ),
            MOBILE_IDOU_UKE_IPPAN AS(
            SELECT
                T1.部署ＣＤ
                ,T1.相手コースＣＤ
                ,T1.商品ＣＤ
                ,SUM(個数) AS 個数
            FROM
                モバイル_移動入力 T1
                LEFT OUTER JOIN コースマスタ T3 ON
                T1.部署ＣＤ = T3.部署ＣＤ
                AND T1.コースＣＤ = T3.コースＣＤ
            WHERE
                T1.日付 = @日付WK
                AND T3.工場区分 != 1
            GROUP BY
                T1.部署ＣＤ
                ,T1.相手コースＣＤ
                ,T1.商品ＣＤ
            ),
            MOBILE_IDOU_UKE_KOJO AS(
            SELECT
                T1.部署ＣＤ
                ,T1.相手コースＣＤ
                ,T1.商品ＣＤ
                ,SUM(個数) AS 個数
            FROM
                モバイル_移動入力 T1
                LEFT OUTER JOIN コースマスタ T3 ON
                T1.部署ＣＤ = T3.部署ＣＤ
                AND T1.コースＣＤ = T3.コースＣＤ
            WHERE
                T1.日付 = @日付WK
                AND T3.工場区分 = 1
            GROUP BY
                T1.部署ＣＤ
                ,T1.相手コースＣＤ
                ,T1.商品ＣＤ
            ),
            MOBILE_HANBAI_BONUS AS(
            SELECT
                T1.部署ＣＤ
                ,T1.コースＣＤ
                ,T1.商品ＣＤ AS 商品ＣＤ
                ,SUM(CASE
                WHEN T1.実績入力 = 1 THEN T1.実績数
                WHEN T1.注文入力 = 1 THEN T1.注文数
                WHEN T1.見込入力 = 1 THEN T1.見込数
                ELSE 0
                END) 販売数
            FROM
                (SELECT
                SHO_SHU.部署ＣＤ
                ,SHO_SHU.コースＣＤ
                ,SHO_SHU.得意先ＣＤ
                ,SHO_MST.商品ＣＤ
                ,SHO_MST.商品区分
                ,SHO_SHU.実績入力
                ,SHO_SHU.実績数
                ,SHO_SHU.注文入力
                ,SHO_SHU.注文数
                ,SHO_SHU.見込入力
                ,SHO_SHU.見込数
                FROM
                モバイル_販売入力 SHO_SHU
                LEFT OUTER JOIN 商品マスタ SHO_MST ON
                    SHO_SHU.主食ＣＤ = SHO_MST.商品ＣＤ
                WHERE
                SHO_SHU.部署ＣＤ = $BushoCd
                AND SHO_SHU.日付 = '$TargetDate'
                AND SHO_SHU.主食ＣＤ != 0
                AND (SHO_SHU.実績数 != 0 OR SHO_SHU.注文数 != 0 OR SHO_SHU.見込数 != 0)
                AND SHO_SHU.現金売掛区分 = 4
                UNION ALL
                SELECT
                    SHO_FUKU.部署ＣＤ
                    ,SHO_FUKU.コースＣＤ
                    ,SHO_FUKU.得意先ＣＤ
                    ,SHO_MST.商品ＣＤ
                    ,SHO_MST.商品区分
                    ,SHO_FUKU.実績入力
                    ,SHO_FUKU.実績数
                    ,SHO_FUKU.注文入力
                    ,SHO_FUKU.注文数
                    ,SHO_FUKU.見込入力
                    ,SHO_FUKU.見込数
                FROM
                モバイル_販売入力 SHO_FUKU
                LEFT OUTER JOIN 商品マスタ SHO_MST ON
                    SHO_FUKU.副食ＣＤ = SHO_MST.商品ＣＤ
                WHERE
                    SHO_FUKU.部署ＣＤ = $BushoCd
                    AND SHO_FUKU.日付 = '$TargetDate'
                    AND SHO_FUKU.副食ＣＤ != 0
                    AND (SHO_FUKU.実績数 != 0 OR SHO_FUKU.注文数 != 0 OR SHO_FUKU.見込数 != 0)
                    AND SHO_FUKU.現金売掛区分 = 4
                ) T1
                LEFT OUTER JOIN (
                SELECT
                    T1.部署ＣＤ
                    ,T1.コースＣＤ
                    ,T1.得意先ＣＤ
                    ,MAX(CAST(T1.注文入力 AS INT)) AS 注文入力
                FROM
                    モバイル_販売入力 T1
                WHERE
                    T1.部署ＣＤ = $BushoCd
                    AND T1.日付 = '$TargetDate'
                GROUP BY
                    T1.部署ＣＤ
                    ,T1.コースＣＤ
                    ,T1.得意先ＣＤ
                ) T3 ON
                T1.部署ＣＤ = T3.部署ＣＤ
                AND T1.コースＣＤ = T3.コースＣＤ
                AND T1.得意先ＣＤ = T3.得意先ＣＤ
                WHERE
                (
                    (T1.実績入力 = 1 AND T1.実績数 != 0)
                    OR( T3.注文入力 = 1 AND T1.注文入力 = 1 AND T1.注文数 != 0)
                    OR( T3.注文入力 = 0 AND T1.見込入力 = 1 AND T1.見込数 != 0)
                )
                GROUP BY
                T1.部署ＣＤ
                ,T1.コースＣＤ
                ,T1.商品ＣＤ
            )
            SELECT
            工場区分
            ,コースＣＤ
            ,コース名
            ,商品名
            ,商品ＣＤ
            ,持ち出し数
            ,売上予定
            ,受取数_工場
            ,受取数_一般
            ,受取詳細
            ,引渡数
            ,引渡詳細
            ,ボーナス販売数
            ,残数
            ,合計
            ,ソート順
            FROM (
                SELECT
                COU.工場区分
                ,COU.コースＣＤ
                ,COU.コース名
                ,TSHO.商品名
                ,TSHO.商品ＣＤ
                ,ISNULL(MOB_MOTI.個数,0) AS 持ち出し数
                ,ISNULL(MOB_HAN.販売数,0) AS 売上予定
                ,ISNULL(MOB_IDO_UKE_KOJO.個数,0) AS 受取数_工場
                ,ISNULL(MOB_IDO_UKE_IPPAN.個数,0) AS 受取数_一般
                ,REPLACE('START'+ISNULL(
                (
                    SELECT
                    ',' + REPLACE(IDO_SHOSAI_COU.コース名,'コース','') + ' ' + CONVERT(VARCHAR,SUM(IDO_SHOSAI.個数))
                    from
                    モバイル_移動入力 IDO_SHOSAI
                    LEFT OUTER JOIN コースマスタ IDO_SHOSAI_COU ON
                        IDO_SHOSAI.部署ＣＤ = IDO_SHOSAI_COU.部署ＣＤ
                        AND IDO_SHOSAI.コースＣＤ = IDO_SHOSAI_COU.コースＣＤ
                    LEFT OUTER JOIN 商品マスタ IDO_SHOSAI_SHO ON
                        IDO_SHOSAI.商品ＣＤ = IDO_SHOSAI_SHO.商品ＣＤ
                    WHERE
                    IDO_SHOSAI.部署ＣＤ = COU.部署ＣＤ
                    AND IDO_SHOSAI.相手コースＣＤ = COU.コースＣＤ
                    AND IDO_SHOSAI.日付 = @日付WK
                    AND IDO_SHOSAI_COU.工場区分 != 1
                    AND IDO_SHOSAI_SHO.商品ＣＤ = TSHO.商品ＣＤ
                    GROUP BY
                    IDO_SHOSAI_COU.コース名
                    FOR XML PATH('')
                    )
                    ,','),'START,','') AS 受取詳細
                ,ISNULL(MOB_IDO_WATASHI.個数,0) AS 引渡数
                ,REPLACE('START'+ISNULL(
                (
                    SELECT
                    ',' + REPLACE(IDO_SHOSAI_COU.コース名,'コース','') + ' ' + CONVERT(VARCHAR,SUM(IDO_SHOSAI.個数))
                    from
                    モバイル_移動入力 IDO_SHOSAI
                    LEFT OUTER JOIN コースマスタ IDO_SHOSAI_COU ON
                        IDO_SHOSAI.部署ＣＤ = IDO_SHOSAI_COU.部署ＣＤ
                        AND IDO_SHOSAI.相手コースＣＤ = IDO_SHOSAI_COU.コースＣＤ
                    LEFT OUTER JOIN 商品マスタ IDO_SHOSAI_SHO ON
                        IDO_SHOSAI.商品ＣＤ = IDO_SHOSAI_SHO.商品ＣＤ
                    WHERE
                    IDO_SHOSAI.部署ＣＤ = COU.部署ＣＤ
                    AND IDO_SHOSAI.コースＣＤ = COU.コースＣＤ
                    AND IDO_SHOSAI.日付 = @日付WK
                    AND IDO_SHOSAI_SHO.商品ＣＤ = TSHO.商品ＣＤ
                    GROUP BY
                    IDO_SHOSAI_COU.コース名
                    FOR XML PATH('')
                    )
                    ,','),'START,','') AS 引渡詳細
                ,ISNULL(MOB_HAN_BONUS.販売数,0) AS ボーナス販売数
                ,ISNULL(MOB_MOTI.個数,0) + ISNULL(MOB_IDO_UKE_KOJO.個数,0) + ISNULL(MOB_IDO_UKE_IPPAN.個数,0) - ISNULL(MOB_IDO_WATASHI.個数,0) - ISNULL(MOB_HAN.販売数,0) AS 残数
                ,ISNULL(MOB_HAN.販売数,0) AS 合計
                ,TSHO.ソート順
                FROM
                [コースマスタ] COU
                INNER JOIN [担当者マスタ] TAN ON
                    COU.担当者ＣＤ = TAN.担当者ＣＤ
                CROSS JOIN TAISHO_SHOHIN TSHO
                LEFT OUTER JOIN MOBILE_MOTI MOB_MOTI ON
                    COU.部署ＣＤ = MOB_MOTI.部署ＣＤ
                    AND COU.コースＣＤ = MOB_MOTI.コースＣＤ
                    AND TSHO.商品ＣＤ = MOB_MOTI.商品ＣＤ
                LEFT OUTER JOIN MOBILE_HANBAI MOB_HAN ON
                    COU.部署ＣＤ = MOB_HAN.部署ＣＤ
                    AND COU.コースＣＤ = MOB_HAN.コースＣＤ
                    AND TSHO.商品ＣＤ = MOB_HAN.商品ＣＤ
                LEFT OUTER JOIN MOBILE_IDOU_UKE_KOJO MOB_IDO_UKE_KOJO ON
                    COU.部署ＣＤ = MOB_IDO_UKE_KOJO.部署ＣＤ
                    AND COU.コースＣＤ = MOB_IDO_UKE_KOJO.相手コースＣＤ
                    AND TSHO.商品ＣＤ = MOB_IDO_UKE_KOJO.商品ＣＤ
                LEFT OUTER JOIN MOBILE_IDOU_UKE_IPPAN MOB_IDO_UKE_IPPAN ON
                    COU.部署ＣＤ = MOB_IDO_UKE_IPPAN.部署ＣＤ
                    AND COU.コースＣＤ = MOB_IDO_UKE_IPPAN.相手コースＣＤ
                    AND TSHO.商品ＣＤ = MOB_IDO_UKE_IPPAN.商品ＣＤ
                LEFT OUTER JOIN MOBILE_IDOU_WATASHI MOB_IDO_WATASHI ON
                    COU.部署ＣＤ = MOB_IDO_WATASHI.部署ＣＤ
                    AND COU.コースＣＤ = MOB_IDO_WATASHI.コースＣＤ
                    AND TSHO.商品ＣＤ = MOB_IDO_WATASHI.商品ＣＤ
                LEFT OUTER JOIN MOBILE_HANBAI_BONUS MOB_HAN_BONUS ON
                    COU.部署ＣＤ = MOB_HAN_BONUS.部署ＣＤ
                    AND COU.コースＣＤ = MOB_HAN_BONUS.コースＣＤ
                    AND TSHO.商品ＣＤ = MOB_HAN_BONUS.商品ＣＤ
            WHERE  COU.部署ＣＤ = $BushoCd AND COU.コースＣＤ = $CourseCd

            ) MAIN
            ORDER BY
                コースＣＤ, ソート順
        ";

        $DataList = DB::select($sql);
        return $DataList;
    }

    /**
     * GetMeisaiDataList
     */
    public function GetMeisaiDataList($request)
    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;
        $CourseCd = $request->CourseCd;

        $sql = "
            SELECT
                0 AS 受注得意先,
                売上データ明細.*,
                得意先マスタ.売掛現金区分 AS マスタ売掛現金区分,
                入金データ.入金日付,
                入金データ.現金 AS 入金額
            FROM
                売上データ明細
                INNER JOIN 得意先マスタ
                    ON 得意先マスタ.得意先ＣＤ=売上データ明細.得意先ＣＤ
                LEFT OUTER JOIN [入金データ]
                    ON 入金データ.入金日付 = '$TargetDate'
                    AND 入金データ.得意先ＣＤ = 得意先マスタ.得意先ＣＤ
                    AND 入金データ.入金区分 = 1
            WHERE
                (得意先マスタ.得意先ＣＤ = 得意先マスタ.受注得意先ＣＤ OR 得意先マスタ.受注得意先ＣＤ = 0)
            AND	売上データ明細.部署ＣＤ = $BushoCd
            AND	売上データ明細.コースＣＤ = $CourseCd
            AND 売上データ明細.日付 = '$TargetDate'
            UNION
            SELECT
                1 AS 受注得意先,
                受注得意先売上データ明細.日付,
                受注得意先売上データ明細.部署ＣＤ,
                受注得意先売上データ明細.コースＣＤ,
                受注得意先売上データ明細.行Ｎｏ,
                受注得意先売上データ明細.得意先ＣＤ,
                受注得意先売上データ明細.明細行Ｎｏ,
                受注得意先売上データ明細.受注Ｎｏ,
                受注得意先売上データ明細.配送担当者ＣＤ,
                受注得意先売上データ明細.商品ＣＤ,
                受注得意先売上データ明細.商品区分,
                受注得意先売上データ明細.現金個数,
                受注得意先売上データ明細.現金金額,
                受注得意先売上データ明細.現金値引,
                受注得意先売上データ明細.現金値引事由ＣＤ,
                SUM(売上データ明細.掛売個数)
                    OVER(PARTITION BY
                            受注得意先.部署ＣＤ,
                            受注得意先.得意先ＣＤ,
                            受注得意先.得意先名,
                            受注得意先.受注得意先ＣＤ,
                            受注得意先売上データ明細.商品ＣＤ,
                            受注得意先売上データ明細.売掛現金区分,
                            受注得意先売上データ明細.商品区分
                    )
                AS 掛売個数,
                MAX(受注得意先売上データ明細.掛売金額)
                    OVER(PARTITION BY
                            受注得意先.部署ＣＤ,
                            受注得意先.得意先ＣＤ,
                            受注得意先.得意先名,
                            受注得意先.受注得意先ＣＤ,
                            受注得意先売上データ明細.商品ＣＤ,
                            受注得意先売上データ明細.売掛現金区分,
                            受注得意先売上データ明細.商品区分
                    )
                AS 掛売金額,
                受注得意先売上データ明細.掛売値引,
                受注得意先売上データ明細.掛売値引事由ＣＤ,
                受注得意先売上データ明細.請求日付,
                受注得意先売上データ明細.予備金額１,
                受注得意先売上データ明細.予備金額２,
                受注得意先売上データ明細.売掛現金区分,
                受注得意先売上データ明細.予備ＣＤ２,
                受注得意先売上データ明細.主食ＣＤ,
                受注得意先売上データ明細.副食ＣＤ,
                受注得意先売上データ明細.分配元数量,
                受注得意先売上データ明細.食事区分,
                受注得意先売上データ明細.修正担当者ＣＤ,
                受注得意先売上データ明細.修正日,
                受注得意先売上データ明細.備考,
				得意先マスタ.売掛現金区分 AS マスタ売掛現金区分,
				入金データ.入金日付,
				入金データ.現金 AS 入金額
            FROM
                売上データ明細
                INNER JOIN 得意先マスタ
                    ON 得意先マスタ.得意先ＣＤ=売上データ明細.得意先ＣＤ
                LEFT OUTER JOIN 得意先マスタ 受注得意先
                    ON 受注得意先.得意先ＣＤ=得意先マスタ.受注得意先ＣＤ
                LEFT OUTER JOIN 売上データ明細 受注得意先売上データ明細
                    ON	受注得意先売上データ明細.部署ＣＤ = 受注得意先.部署CD
                    AND 受注得意先売上データ明細.得意先ＣＤ = 受注得意先.得意先ＣＤ
                    AND 受注得意先売上データ明細.日付 = 売上データ明細.日付
                    AND 受注得意先売上データ明細.商品ＣＤ = 売上データ明細.商品ＣＤ
				LEFT OUTER JOIN [入金データ]
					ON 入金データ.入金日付 = '$TargetDate'
					AND 入金データ.得意先ＣＤ = 受注得意先.得意先ＣＤ
					AND 入金データ.入金区分 = 1
            WHERE
                (得意先マスタ.得意先ＣＤ != 得意先マスタ.受注得意先ＣＤ OR 得意先マスタ.受注得意先ＣＤ = 0)
            AND	売上データ明細.部署ＣＤ = $BushoCd
            AND	売上データ明細.コースＣＤ = $CourseCd
            AND 売上データ明細.日付 = '$TargetDate'
            AND 受注得意先.得意先ＣＤ IS NOT NULL
        ";

        $DataList = DB::select($sql);
        return $DataList;
    }

    /**
     * GetNyukinData
     */
    public function GetNyukinData($request)
    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;
        $CourseCd = $request->CourseCd;

        $sql = "
            SELECT
                COUNT(*) AS 件数
                , NYU.入金日付
                ,SUM(NYU.現金) AS 現金
            FROM
                入金データ NYU
            WHERE
                CONVERT(VARCHAR, 入金日付, 112) = '$TargetDate'
                AND NYU.得意先ＣＤ IN (
                    SELECT
                    DMY_TOK.得意先ＣＤ
                    FROM
                    得意先マスタ DMY_TOK
                    WHERE
                    DMY_TOK.受注得意先ＣＤ IN (
                        SELECT
                        DMY.得意先ＣＤ
                        FROM
                        コーステーブル DMY
                        WHERE
                        DMY.部署CD = $BushoCd
                        AND DMY.コースＣＤ = $CourseCd
                        )
                    OR DMY_TOK.得意先ＣＤ IN (
                        SELECT
                        DMY.得意先ＣＤ
                        FROM
                        コーステーブル DMY
                        WHERE
                        DMY.部署CD = $BushoCd
                        AND DMY.コースＣＤ = $CourseCd
                        )
                )
                AND NYU.入金区分 = 1
            GROUP BY
                NYU.入金日付
        ";

        $data = DB::selectOne($sql);
        return $data;
    }

    /**
     * GetCourseMeisaiData
     */
    public function GetCourseMeisaiData($request)
    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;
        $CourseCd = $request->CourseCd;

        $sql = "
            SELECT
                コース別明細データ.*,
                担当者マスタ.担当者名 AS 修正担当者名
            FROM
                コース別明細データ
                LEFT JOIN 担当者マスタ
                    ON 担当者マスタ.担当者ＣＤ = コース別明細データ.修正担当者ＣＤ
            WHERE コース別明細データ.日付 = '$TargetDate'
            AND   コース別明細データ.部署ＣＤ = $BushoCd
            AND	  コース別明細データ.コースＣＤ = $CourseCd
        ";

        $data = DB::selectOne($sql);
        return $data;
    }

    /**
     * Search
     */
    public function Search($request) {
        return response()->json(
            [
                "IdouDataList" => $this->GetIdouDataList($request),
                "MeisaiDataList" => $this->GetMeisaiDataList($request),
                "NyukinData" => $this->GetNyukinData($request),
                "CourseMeisaiData" => $this->GetCourseMeisaiData($request),
            ]
        );
    }

    /**
     * Delete
     */
    public function Delete($request)
    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;
        $CourseCd = $request->CourseCd;
        $LastEditDate = $request->LastEditDate;

        DB::beginTransaction();
        $ret = 1;
        try {

            $sql = "
                DELETE
                FROM
                    コース別明細データ
                WHERE コース別明細データ.日付 = '$TargetDate'
                AND   コース別明細データ.部署CD = $BushoCd
                AND	  コース別明細データ.コースＣＤ = $CourseCd
                AND	  コース別明細データ.修正日 = '$LastEditDate'
            ";

            $ret = DB::delete($sql);


            if ($ret != 1) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            // "edited" => $ret != 1 ? $this->GetOrderList($request) : [],
        ]);
        return;
    }

    /**
     * Save
     */
    public function Save($request)
    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;
        $CourseCd = $request->CourseCd;
        $LastEditDate = $request->LastEditDate;
        $Target = $request->Target;

        DB::beginTransaction();

        $edited = false;

        try {

            $date = Carbon::now()->format('Y-m-d H:i:s');

            $r = コース別明細データ::query()
                ->where('日付', $Target['日付'])
                ->where('部署CD', $Target['部署CD'])
                ->where('コースＣＤ', $Target['コースＣＤ'])
                ->get();

            if (count($r) == 1) {
                if (isset($LastEditDate) && !!$LastEditDate) {
                    if ($LastEditDate != $r[0]->修正日) {
                        $edited = true;
                    } else {
                        $Target['修正日'] = $date;

                        コース別明細データ::query()
                            ->where('日付', $Target['日付'])
                            ->where('部署CD', $Target['部署CD'])
                            ->where('コースＣＤ', $Target['コースＣＤ'])
                            ->update($Target);
                    }
                }
            } else {
                $Target['修正日'] = $date;
                コース別明細データ::insert($Target);
            }

            if ($edited) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            "result" => true,
            "CourseMeisaiData" => $this->GetCourseMeisaiData($request),
            // "edited" => $ret != 1 ? $this->GetOrderList($request) : [],
        ]);
        return;
    }
}
