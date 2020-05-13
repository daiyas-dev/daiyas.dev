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

class DAI08040Controller extends Controller
{
    /**
     * GetChumonList
     */
    public function GetChumonList($request) {
        $BushoCd = $request->BushoCd;

        $DateStart = $request->DateStart;
        $DateEnd = $request->DateEnd;

        $sql = "
            SELECT
                CHUMON.部署ＣＤ
                ,CHUMON.受注Ｎｏ
                ,CHUMON.配達日付
                ,CHUMON.配達時間
                ,CHUMON.得意先ＣＤ
                ,TOK.得意先名
                ,TOK.電話番号１
                ,TOK.ＦＡＸ１
                ,CHUMON.注文日付
                ,ISNULL(TOK.住所１,'')  +  ISNULL(TOK.住所２,'') AS 住所
                ,ISNULL(CHUMON.配達先１,'')  + ISNULL(CHUMON.配達先２,'') AS 配達先
                ,CHUMON.エリアＣＤ
                ,COUM.コース名 AS エリア名称
                ,CHUMON.地域区分
                ,KAKUSHU_TIKU.各種名称 AS 地区名称
                ,CHUMON.配達区分
                ,KAKUSHU_HAITATSU.各種名称 AS 配達名称
                ,CHUMON.税区分
                ,KAKUSHU_ZEI.各種名称 AS 税名称
            FROM
                仕出し注文データ CHUMON
                LEFT OUTER JOIN 得意先マスタ TOK ON
                    CHUMON.得意先ＣＤ = TOK.得意先ＣＤ
                LEFT OUTER JOIN 各種テーブル KAKUSHU_TIKU ON
                    KAKUSHU_TIKU.各種CD = 32
                AND KAKUSHU_TIKU.行NO = CHUMON.地域区分
                LEFT OUTER JOIN 各種テーブル KAKUSHU_HAITATSU ON
                    KAKUSHU_HAITATSU.各種CD = 31
                AND KAKUSHU_HAITATSU.行NO = CHUMON.配達区分
                LEFT OUTER JOIN 各種テーブル KAKUSHU_ZEI ON
                    KAKUSHU_ZEI.各種CD = 20
                    AND KAKUSHU_ZEI.行NO = CHUMON.税区分
                LEFT OUTER JOIN コースマスタ COUM ON
                    COUM.部署ＣＤ = CHUMON.部署ＣＤ
                AND COUM.コースＣＤ = CHUMON.エリアＣＤ
            WHERE
                CHUMON.部署ＣＤ=$BushoCd
            AND CHUMON.配達日付 >= '$DateStart' AND CHUMON.配達日付 <= '$DateEnd'
            ORDER BY
                CHUMON.配達日付,
                CHUMON.配達時間
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
