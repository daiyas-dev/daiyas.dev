<?php

namespace App\Http\Controllers;

use App\Models\祝日マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use \GuzzleHttp\Client;
use PDO;

class DAI08030Controller extends Controller
{
    /**
     * GetChumonList
     */
    public function GetChumonList($request) {
        $BushoCd = $request->BushoCd;
        $DeliveryDate = $request->DeliveryDate;

        $sql = "
            WITH HASAREA_CHUMON AS (
                SELECT
                    SCHUMON.部署ＣＤ
                    ,SCHUMON.配達日付
                    ,SCHUMON.受注Ｎｏ
                    ,SCHUMON.配達時間
                    ,SCHUMON.エリアＣＤ
                    ,SAREA.配達順
                FROM
                仕出し注文データ SCHUMON
                    INNER JOIN 仕出しエリアデータ SAREA
                        ON	SCHUMON.部署ＣＤ = SAREA.部署ＣＤ
                        AND SCHUMON.受注Ｎｏ = SAREA.受注Ｎｏ
                        AND SCHUMON.配達日付 = SAREA.注文日付
                WHERE
                SCHUMON.部署ＣＤ = $BushoCd AND SCHUMON.配達日付 = '$DeliveryDate'
            )
            SELECT
                SAREA.配達順 AS 配達順
                ,SCHUMON.配達時間 AS 配達時間
                ,SCHUMON.受注Ｎｏ AS 受注Ｎｏ
                ,SCHUMON.配達区分 AS 配達区分
                ,KAKUSHU_HAITATU.各種名称 AS  配達区分名
                ,SCHUMON.得意先ＣＤ AS 得意先ＣＤ
                ,TOK.得意先名 AS 得意先名
                ,TOK.電話番号１ AS 電話番号１
                ,ISNULL(TOK.住所１,'') + ISNULL(TOK.住所２,'') AS 配達先１
                ,ISNULL(SCHUMON.配達先１,'') +  ISNULL(SCHUMON.配達先２,'') AS 配達先２
                ,SCHUMON.連絡区分
                ,KAKUSHU_RENRAKU.各種名称 AS 連絡区分名
                ,ISNULL(SAREA.エリアＣＤ,SCHUMON.エリアＣＤ) AS エリアＣＤ
                ,COUM.コース名  AS エリア名
                ,SCHUMON.合計数量
                ,SCHUMON.合計金額
                ,SCHUMON.合計消費税
                ,URIM.修正日 AS 完了時間
            FROM
                仕出し注文データ SCHUMON
                    LEFT OUTER JOIN 仕出しエリアデータ SAREA ON
                            SCHUMON.部署ＣＤ = SAREA.部署ＣＤ
                        AND SCHUMON.受注Ｎｏ = SAREA.受注Ｎｏ
                        AND SCHUMON.配達日付 = SAREA.注文日付
                    LEFT OUTER JOIN HASAREA_CHUMON SCHUMON_EXISTS_AREA ON
                            SCHUMON.部署ＣＤ = SCHUMON_EXISTS_AREA.部署ＣＤ
                        AND SCHUMON.受注Ｎｏ != SCHUMON_EXISTS_AREA.受注Ｎｏ
                        AND SCHUMON.配達日付 = SCHUMON_EXISTS_AREA.配達日付
                        AND SCHUMON.配達時間 >= SCHUMON_EXISTS_AREA.配達時間
                    LEFT OUTER JOIN 得意先マスタ TOK ON
                        TOK.得意先ＣＤ = SCHUMON.得意先ＣＤ
                    LEFT OUTER JOIN 各種テーブル KAKUSHU_HAITATU ON
                            KAKUSHU_HAITATU.各種ＣＤ = 31
                        AND KAKUSHU_HAITATU.行ＮＯ = SCHUMON.配達区分
                    LEFT OUTER JOIN 各種テーブル KAKUSHU_RENRAKU ON
                            KAKUSHU_RENRAKU.各種ＣＤ = 30
                        AND KAKUSHU_RENRAKU.行ＮＯ = SCHUMON.連絡区分
                    LEFT OUTER JOIN コースマスタ COUM ON
                            COUM.部署ＣＤ = SCHUMON.部署ＣＤ
                        AND COUM.コースＣＤ = ISNULL(SAREA.エリアＣＤ,SCHUMON.エリアＣＤ)
                    LEFT OUTER JOIN 売上データ明細 URIM ON
                            URIM.部署ＣＤ = SCHUMON.部署ＣＤ
                        AND URIM.日付 = SCHUMON.配達日付
                        AND URIM.得意先ＣＤ = SCHUMON.得意先ＣＤ
                        AND URIM.受注Ｎｏ = SCHUMON.受注Ｎｏ
                        AND URIM.明細行Ｎｏ =
                            (
                                SELECT
                                    MIN(DUMY.明細行Ｎｏ)
                                FROM
                                    売上データ明細 DUMY
                                WHERE
                                    DUMY.部署ＣＤ = SCHUMON.部署ＣＤ
                                AND DUMY.日付 = SCHUMON.配達日付
                                AND DUMY.得意先ＣＤ = SCHUMON.得意先ＣＤ
                                AND DUMY.受注Ｎｏ = SCHUMON.受注Ｎｏ
                            )
            WHERE
                SCHUMON.部署ＣＤ = $BushoCd AND SCHUMON.配達日付 = '$DeliveryDate'
            AND (
                SCHUMON_EXISTS_AREA.受注Ｎｏ IS NULL
                OR (
                    SCHUMON_EXISTS_AREA.受注Ｎｏ =
                        (
                            SELECT
                                MAX(DMY.受注Ｎｏ)
                            FROM
                                HASAREA_CHUMON DMY
                            WHERE
                                DMY.部署ＣＤ = SCHUMON.部署ＣＤ
                            AND DMY.受注Ｎｏ != SCHUMON.受注Ｎｏ
                            AND DMY.配達日付 = SCHUMON.配達日付
                            AND DMY.配達時間 =
                            (
                                SELECT
                                    MAX(DMY_JIKAN.配達時間)
                                FROM
                                    HASAREA_CHUMON DMY_JIKAN
                                WHERE
                                    DMY_JIKAN.部署ＣＤ = SCHUMON.部署ＣＤ
                                AND DMY_JIKAN.受注Ｎｏ != SCHUMON.受注Ｎｏ
                                AND DMY_JIKAN.配達日付 = SCHUMON.配達日付
                                AND DMY_JIKAN.配達時間 <= SCHUMON.配達時間
                            )
                        )
                )
            )
            ORDER BY
                ISNULL(SAREA.配達順,ISNULL(SCHUMON_EXISTS_AREA.配達順 + 0.5,0))
                ,SCHUMON.配達時間
                ,SCHUMON.得意先ＣＤ
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        return $DataList;
    }
}
