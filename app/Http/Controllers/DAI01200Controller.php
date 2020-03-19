<?php

namespace App\Http\Controllers;

use App\Models\コーステーブル;
use App\Models\各種テーブル;
use App\Models\商品マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Facades\DB as IlluminateDB;

class DAI01200Controller extends Controller
{
    /**
     * GetCourseMeisaiData
     */
    public function GetCourseMeisaiData($request){
        $BushoCd = $request->BushoCd;
        $DateStart = $request->TargetDate;
        $DateEnd = $request->TargetDate;
        $CourseCd = $request->CourseCd;
        //TODO:検索条件初期値を埋め込み
        $BushoCd = 101;
        $CourseCd = 101;
        $DateStart = "2019/08/05";
        $DateEnd = "2019/08/09";

        //コース別明細データ.部署CDの「CD」は半角が正しいです。入力間違いではありません。
        $sql = "
                SELECT
                    コース別明細データ.*
                    ,コースマスタ.コース名
                    ,担当者マスタ.担当者名
                FROM
                    コース別明細データ
                    LEFT JOIN コースマスタ
                        ON コースマスタ.部署ＣＤ=コース別明細データ.部署CD
                    AND コースマスタ.担当者ＣＤ=コース別明細データ.配送担当者ＣＤ
                    INNER JOIN [担当者マスタ]
                    ON 担当者マスタ.担当者ＣＤ=コースマスタ.担当者ＣＤ
                WHERE
                        コース別明細データ.部署CD = $BushoCd
                    AND コース別明細データ.コースＣＤ = $CourseCd
                    AND コース別明細データ.日付 >= '$DateStart'
                    AND コース別明細データ.日付 <= '$DateEnd'
                ";

        $data = DB::select($sql);
        return $data;
    }

    /**
     * GetUriageMeisaiData
     */
    public function GetUriageMeisaiData($request){
        $BushoCd = $request->BushoCd;
        $DateStart = $request->TargetDate;
        $DateEnd = $request->TargetDate;
        $CourseCd = $request->CourseCd;
        //TODO:検索条件初期値を埋め込み
        $BushoCd = 101;
        $CourseCd = 101;
        $DateStart = "2019/08/05";
        $DateEnd = "2019/08/09";

        $sql = "
                SELECT
                    日付
                    , uriage.コースＣＤ
                    , uriage.商品区分
                    , SUM(uriage.現金個数) AS 現金個数
                    , SUM(uriage.現金金額) AS 現金金額
                    , SUM(uriage.現金値引) AS 現金値引
                    , SUM(uriage.掛売個数) AS 掛売個数
                    , SUM(uriage.掛売金額) AS 掛売金額
                    , SUM(uriage.掛売値引) AS 掛売値引
                    , SUM(uriage.分配元数量) 分配元数量
                    , uriage.売掛現金区分
                FROM
                    [売上データ明細] uriage
                    inner join 得意先マスタ tokui
                    on tokui.得意先ＣＤ = uriage.得意先ＣＤ
                    and tokui.部署ＣＤ = uriage.部署ＣＤ
                WHERE
                    　　uriage.部署ＣＤ = $BushoCd
                    AND uriage.コースＣＤ = $CourseCd
                    AND uriage.日付 >= '$DateStart'
                    AND uriage.日付 <= '$DateEnd'
                    AND (
                        tokui.得意先ＣＤ = tokui.受注得意先ＣＤ
                        OR tokui.受注得意先ＣＤ = 0
                    )
                GROUP BY
                    　uriage.日付
                    , uriage.コースＣＤ
                    , uriage.商品区分
                    , uriage.売掛現金区分
                ";

        $data = DB::select($sql);
        return $data;
    }

    /**
     * GetNyukinData
     */
    public function GetNyukinData($request){
        $BushoCd = $request->BushoCd;
        $DateStart = $request->TargetDate;
        $DateEnd = $request->TargetDate;
        $CourseCd = $request->CourseCd;
        //TODO:検索条件初期値を埋め込み
        $BushoCd = 101;
        $CourseCd = 101;
        $DateStart = "2019/08/05";
        $DateEnd = "2019/08/09";

        $sql = "
                SELECT
                    　コーステーブル.部署ＣＤ
                    , コーステーブル.コースＣＤ
                    , 入金データ.入金日付
                    , 入金データ.入金区分
                    , SUM(入金データ.現金) AS 現金
                    , SUM(入金データ.小切手) AS 小切手
                    , SUM(入金データ.振込) AS 振込
                    , SUM(入金データ.バークレー) AS バークレー
                    , SUM(入金データ.その他) AS その他
                    , SUM(入金データ.相殺) AS 相殺
                    , SUM(入金データ.値引) AS 値引
                FROM
                    入金データ
                    INNER JOIN 得意先マスタ ON 得意先マスタ.受注得意先ＣＤ = 1
                    INNER JOIN コーステーブル ON コーステーブル.得意先ＣＤ = 入金データ.得意先ＣＤ
                WHERE
                    入金データ.得意先ＣＤ = 得意先マスタ.得意先ＣＤ
                    AND 入金データ.入金日付 >= '$DateStart'
                    AND 入金データ.入金日付 <= '$DateEnd'
                    AND コーステーブル.部署ＣＤ = $BushoCd
                    AND コーステーブル.コースＣＤ = $CourseCd
                GROUP BY
                      コーステーブル.部署ＣＤ
                    , コーステーブル.コースＣＤ
                    , 入金データ.入金日付
                    , 入金データ.入金区分
                ";

        $data = DB::select($sql);
        return $data;
    }

    /**
     * Search
     */
    public function Search($request) {
        $jsn= response()->json(
            [
                "UriageMeisaiData" => $this->GetUriageMeisaiData($request),
                "NyukinData" => $this->GetNyukinData($request),
                "CourseMeisaiData" => $this->GetCourseMeisaiData($request),
            ]
        );
        return $jsn;
    }

//TODO:これより下はコピー元のコードのため、不要
    /**
     * GetDataList
     */
    public function GetDataList($request)
    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;
        $CourseKbn = $request->CourseKbn;

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
            ,残数
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

                ,ISNULL(MOB_MOTI.個数,0) + ISNULL(MOB_IDO_UKE_KOJO.個数,0) + ISNULL(MOB_IDO_UKE_IPPAN.個数,0) - ISNULL(MOB_IDO_WATASHI.個数,0) - ISNULL(MOB_HAN.販売数,0) AS 残数

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
            WHERE  COU.部署ＣＤ = $BushoCd AND COU.コース区分 = $CourseKbn

            ) MAIN
            ORDER BY
                コースＣＤ, ソート順
        ";

        $DataList = DB::select($sql);
        return $DataList;
    }

    /**
     * Search1
     */
    public function Search1($request) {
        $jsn= response()->json($this->GetDataList($request));
        return $jsn;
    }

    /**
     * UpdateCheck
     */
    public function UpdateCheck($request)    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;
        $UpdateDate = $request->UpdateDate;

        $sql = "
            SELECT
                MAX(最新修正日時) AS 最新修正日時
            FROM
            (
                SELECT
                    MAX(修正日) AS 最新修正日時
                FROM モバイル_持ち出し入力
                WHERE
                    部署ＣＤ= $BushoCd
                AND	持ち出し日付 = '$TargetDate'
                UNION
                SELECT
                    MAX(修正日) AS 最新修正日時
                FROM モバイル_販売入力
                WHERE
                    部署ＣＤ= $BushoCd
                AND	日付 = '$TargetDate'
                UNION
                SELECT
                    MAX(修正日) AS 最新修正日時
                FROM モバイル_移動入力
                WHERE
                    部署ＣＤ= $BushoCd
                AND	日付 = '$TargetDate'
            ) MOBILE
        ";

        $Result = DB::selectOne($sql);

        if (!!$UpdateDate && $Result->最新修正日時 != $UpdateDate) {
            $Result->list = $this->GetDataList($request);
            return response()->json($Result);
        } else {
            return response()->json($Result);
        }
    }
}
