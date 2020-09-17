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

class DAI02021Controller extends Controller
{

    /**
     * Search
     */
    public function Search($vm)
    {
        return response()->json(
            [
                [
                    "SeikyuData" => $this->GetSeikyuData($vm),
                    "MeisaiList" => $this->GetMeisaiList($vm),
                ]
            ]
        );
    }

    /**
     * GetSeikyuData
     */
    public function GetSeikyuData($vm)
    {
        $BushoCd = $vm->BushoCd;
        $CustomerCd = $vm->CustomerCd;
        $TargetDate = $vm->TargetDate;

        $sql = "
            SELECT
                請求データ.請求日付,
                請求データ.請求先ＣＤ,
                ISNULL(請求データ.[３回前残高], 0) +	ISNULL(請求データ.前々回残高, 0) + ISNULL(請求データ.前回残高, 0) AS 前回請求残高,
                請求データ.今回入金額,
                請求データ.差引繰越額,
                請求データ.今回売上額,
                請求データ.今回請求額,
                請求データ.請求日範囲開始,
                請求データ.請求日範囲終了
            FROM [請求データ]
                INNER JOIN [得意先マスタ]
                    ON 得意先マスタ.得意先ＣＤ = 請求データ.請求先ＣＤ
            WHERE
                請求データ.部署ＣＤ = $BushoCd
            AND 請求データ.請求日付 = '$TargetDate'
            AND 請求データ.請求先ＣＤ = $CustomerCd
        ";

        $Result = DB::selectOne(DB::raw($sql));

        return $Result;
    }

    /**
     * GetMeisaiList
     */
    public function GetMeisaiList($vm)
    {
        $BushoCd = $vm->BushoCd;
        $CustomerCd = $vm->CustomerCd;
        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;

        $sql = "
            SELECT
                売上データ明細.部署ＣＤ,
                売上データ明細.得意先ＣＤ,
                売上データ明細.コースＣＤ,
                コースマスタ.コース名,
                コースマスタ.コース区分,
                売上データ明細.日付 AS 伝票日付,
                NULL AS 伝票Ｎｏ,
                売上データ明細.商品ＣＤ,
                売上データ明細.食事区分,
                食事区分名称.各種名称 AS 食事区分名,
                商品マスタ.商品名 + IIF(売上データ明細.掛売値引 > 0, '(値引)', '') AS 商品名,
                SUM(売上データ明細.掛売個数) AS 数量,
                ISNULL(売上データ明細.予備金額１, 商品マスタ.売価単価) AS 単価,
                SUM(売上データ明細.掛売金額 - ISNULL(売上データ明細.掛売値引, 0)) AS 金額,
                NULL AS 入金金額,
                売上データ明細.備考 AS 備考
            FROM
                [売上データ明細]
                INNER JOIN [商品マスタ]
                    ON 商品マスタ.商品ＣＤ = 売上データ明細.商品ＣＤ
                INNER JOIN [得意先マスタ]
                    ON 得意先マスタ.得意先ＣＤ = 売上データ明細.得意先ＣＤ
                LEFT OUTER JOIN コースマスタ
                    ON  コースマスタ.部署ＣＤ = 売上データ明細.部署ＣＤ
                    AND コースマスタ.コースＣＤ = 売上データ明細.コースＣＤ
                LEFT OUTER JOIN 各種テーブル 食事区分名称
                    ON  食事区分名称.各種CD = 38
                    AND 食事区分名称.サブ各種CD1 = 売上データ明細.食事区分
            WHERE
                得意先マスタ.請求先ＣＤ = $CustomerCd
            AND 売上データ明細.日付 >= '$DateStart'
            AND 売上データ明細.日付 <= '$DateEnd'
            AND 売上データ明細.売掛現金区分 = 1
            AND 売上データ明細.掛売金額 - ISNULL(売上データ明細.掛売値引, 0) <> 0
			GROUP BY
                売上データ明細.部署ＣＤ,
                売上データ明細.得意先ＣＤ,
                売上データ明細.コースＣＤ,
                コースマスタ.コース名,
                コースマスタ.コース区分,
                売上データ明細.日付,
                売上データ明細.商品ＣＤ,
                売上データ明細.食事区分,
                食事区分名称.各種名称,
                商品マスタ.商品名 + IIF(売上データ明細.掛売値引 > 0, '(値引)', ''),
                ISNULL(売上データ明細.予備金額１, 商品マスタ.売価単価),
                売上データ明細.備考
            UNION
            SELECT
                入金データ.部署ＣＤ,
                入金データ.得意先ＣＤ,
                NULL AS コースＣＤ,
                NULL AS コース名,
                NULL AS コース区分,
                入金データ.入金日付 AS 伝票日付,
                入金データ.伝票Ｎｏ,
                NULL AS 商品ＣＤ,
                NULL AS 食事区分,
                NULL AS 食事区分名,
                (
                    '入金'
                        + IIF(入金データ.現金 > 0, '(現金)', '')
                        + IIF(入金データ.小切手 > 0, '(小切手)', '')
                        + IIF(入金データ.振込 > 0, '(振込)', '')
                        + IIF(入金データ.バークレー > 0, '(振替)', '')
                        + IIF(入金データ.その他 > 0, '(ﾁｹｯﾄ入金)', '')
                        + IIF(入金データ.相殺 > 0, '(振込料)', '')
                        + IIF(入金データ.値引 > 0, '(値引)', '')
                        + IIF(入金データ.摘要 IS NULL, '', ':') + ISNULL(入金データ.摘要, '')
                ) AS 商品名,
                NULL AS 数量,
                NULL AS 単価,
                NULL AS 金額,
                (
                    ISNULL(入金データ.現金, 0)
                        + ISNULL(入金データ.小切手, 0)
                        + ISNULL(入金データ.振込, 0)
                        + ISNULL(入金データ.バークレー, 0)
                        + ISNULL(入金データ.その他, 0)
                        + ISNULL(入金データ.相殺, 0)
                        + ISNULL(入金データ.値引, 0)
                ) AS 入金金額,
                入金データ.備考
            FROM
                [入金データ]
                INNER JOIN [得意先マスタ]
                    ON 得意先マスタ.得意先ＣＤ = 入金データ.得意先ＣＤ
            WHERE
                得意先マスタ.請求先ＣＤ = $CustomerCd
                AND 入金日付 >= '$DateStart'
                AND 入金日付 <= '$DateEnd'
            ORDER BY
                伝票日付,
                伝票Ｎｏ
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
