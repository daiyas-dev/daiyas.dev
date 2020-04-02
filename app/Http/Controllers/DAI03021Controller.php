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

class DAI03021Controller extends Controller
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
        日付
        , 売掛データ.請求先ＣＤ
        , 前月残高
        , 今月入金額
        , 差引繰越額
        , 今月売上額
        , 今月残高
        , 得意先マスタ.得意先名
      FROM
        [売掛データ]
        INNER JOIN [得意先マスタ]
          ON 得意先マスタ.得意先ＣＤ = 売掛データ.請求先ＣＤ
          AND 得意先マスタ.部署ＣＤ = 売掛データ.部署ＣＤ
      WHERE
        日付 = '$TargetDate'
        AND 売掛データ.請求先ＣＤ = $CustomerCd
        AND 売掛データ.部署ＣＤ = $BushoCd
      order by
        売掛データ.請求先ＣＤ
        , 日付
        ";
        $Result = DB::selectOne($sql);
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
                売上データ明細.部署ＣＤ
                , 売上データ明細.得意先ＣＤ
                , 日付 AS 伝票日付
                , NULL AS 伝票Ｎｏ
                , 売上データ明細.商品ＣＤ
                , 商品マスタ.商品名
                , 掛売個数 AS 数量
                , 得意先単価マスタ.単価
                , 掛売金額-掛売値引 AS 金額
                ,NULL AS 入金金額
                ,NULL AS 摘要
                ,NULL AS 備考
            FROM
                [売上データ明細]
                INNER JOIN [商品マスタ]
                ON 商品マスタ.商品ＣＤ = 売上データ明細.商品ＣＤ
                INNER JOIN [得意先マスタ]
                ON 得意先マスタ.得意先ＣＤ = 売上データ明細.得意先ＣＤ
                AND 得意先マスタ.部署ＣＤ = 売上データ明細.部署ＣＤ
                LEFT JOIN  得意先単価マスタ ON 得意先単価マスタ.得意先ＣＤ = 得意先マスタ.得意先ＣＤ AND 得意先単価マスタ.商品ＣＤ = 売上データ明細.商品ＣＤ
            WHERE
                得意先マスタ.請求先ＣＤ = $CustomerCd
                AND 日付 >= '$DateStart'
                AND 日付 <= '$DateEnd'
                AND 売上データ明細.部署ＣＤ = $BushoCd
                AND (売上データ明細.売掛現金区分 = 1)
            UNION
            SELECT
                入金データ.部署ＣＤ,
                入金データ.得意先ＣＤ,
                入金データ.入金日付 AS 伝票日付,
                入金データ.伝票Ｎｏ,
                NULL AS 商品ＣＤ,
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
                入金データ.摘要,
                入金データ.備考
            FROM
                [入金データ]
                INNER JOIN [得意先マスタ]
                ON 得意先マスタ.得意先ＣＤ = 入金データ.得意先ＣＤ
            WHERE
                得意先マスタ.請求先ＣＤ = $CustomerCd
                AND 入金データ.部署ＣＤ = $BushoCd
                AND 入金日付 >= '$DateStart'
                AND 入金日付 <= '$DateEnd'
            ORDER BY
                得意先ＣＤ,
                日付,
                伝票Ｎｏ
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
