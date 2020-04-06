<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI07040Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm) {
        $nextVm = clone $vm;
        $nextVm->TargetDateFrom = date("Ymd", strtotime("$vm->TargetDateFrom +7 days"));
        $nextVm->TargetDateTo = date("Ymd", strtotime("$vm->TargetDateTo +7 days"));

        return response()->json([
            [
                "DeliveryList" => $this->GetDeliveryList($vm),
                // "SeikyuList" => $this->GetSeikyuList($vm),
                // "UriageListDiff" => $this->GetUriageListDiff($vm),
                // "NyukinListDiff" => $this->GetNyukinListDiff($vm),
                // "UriageListThisWeek" => $this->GetUriageList($vm),
                // "NyukinListThisWeek" => $this->GetNyukinList($vm),
                // "UriageListNextWeek" => $this->GetUriageList($nextVm),
                // "NyukinListNextWeek" => $this->GetNyukinList($nextVm),
            ]
        ]);
    }

    /**
     * GetDeliveryList
     */
    public function GetDeliveryList($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDateFrom = $vm->TargetDateFrom;
        $TargetDateTo = $vm->TargetDateTo;

        $sql= "
            SELECT
                URIAGE_MEISAI.部署ＣＤ
                , BUSYO.部署名
                , URIAGE_MEISAI.コースＣＤ
                , COURSE.コース名
                , URIAGE_MEISAI.日付
                , URIAGE_MEISAI.商品ＣＤ
                , SHOHIN.商品名
                , SUM(URIAGE_MEISAI.現金個数 +  URIAGE_MEISAI.掛売個数) AS 数量
                , SUM((現金金額 - 現金値引) + (掛売金額 - 掛売値引)) AS 金額
            FROM
                売上データ明細 URIAGE_MEISAI
                LEFT JOIN 商品マスタ SHOHIN ON
                    URIAGE_MEISAI.商品ＣＤ = SHOHIN.商品ＣＤ
                LEFT JOIN 部署マスタ BUSYO ON
                    URIAGE_MEISAI.部署ＣＤ = BUSYO.部署CD
                LEFT JOIN コースマスタ COURSE ON
                    URIAGE_MEISAI.部署ＣＤ = COURSE.部署CD AND
                    URIAGE_MEISAI.コースＣＤ = COURSE.コースＣＤ
            WHERE
                URIAGE_MEISAI.部署ＣＤ = $BushoCd AND
                URIAGE_MEISAI.日付 >= '$TargetDateFrom' AND URIAGE_MEISAI.日付 <= '$TargetDateTo'
            GROUP BY
                URIAGE_MEISAI.部署ＣＤ
                , BUSYO.部署名
                , URIAGE_MEISAI.コースＣＤ
                , COURSE.コース名
                , URIAGE_MEISAI.日付
                , URIAGE_MEISAI.商品ＣＤ
                , SHOHIN.商品名
            ORDER BY
                URIAGE_MEISAI.商品ＣＤ
                , URIAGE_MEISAI.コースＣＤ
                , URIAGE_MEISAI.日付
        ";

        $dsn = 'sqlsrv:server=localhost;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        // $DataList = DB::select($sql);

        return $DataList;
    }

    /**
     * GetSeikyuList
     */
    public function GetSeikyuList($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDateFrom = $vm->TargetDateFrom;
        $TargetDateTo = $vm->TargetDateTo;

        $sql = "
            SELECT
                RANKED.得意先ＣＤ
                , RANKED.請求日付
                , 今回請求額
            FROM (
                SELECT
                    TOKUISAKI.得意先ＣＤ
                    , SEIKYU.請求日付
                    , SEIKYU.今回請求額
                    , RANK() OVER(PARTITION BY SEIKYU.請求先ＣＤ ORDER BY SEIKYU.請求日付 DESC) AS RK
                FROM
                    得意先マスタ TOKUISAKI
                    INNER JOIN コーステーブル CT
                        ON  CT.部署ＣＤ = TOKUISAKI.部署ＣＤ
                        AND CT.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                    INNER JOIN コースマスタ COU
                        ON  COU.部署ＣＤ = CT.部署ＣＤ
                        AND COU.コースＣＤ = CT.コースＣＤ
                    INNER JOIN 請求データ SEIKYU
                        ON  SEIKYU.部署ＣＤ = TOKUISAKI.部署ＣＤ
                        AND SEIKYU.請求先ＣＤ = TOKUISAKI.得意先ＣＤ
                WHERE
                    TOKUISAKI.部署ＣＤ = $BushoCd
                    AND 請求日付 < '$TargetDateFrom'
            ) RANKED
            WHERE
                RK=1
        ";

        $dsn = 'sqlsrv:server=localhost;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        // $DataList = DB::select($sql);

        return $DataList;
    }

    /**
     * GetUriageList
     */
    public function GetUriageList($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDateFrom = $vm->TargetDateFrom;
        $TargetDateTo = $vm->TargetDateTo;

        $sql = "
            SELECT
                URIAGE_MEISAI.得意先ＣＤ,
                SUM(URIAGE_MEISAI.掛売金額 - URIAGE_MEISAI.掛売値引) AS 売上
            FROM
                得意先マスタ TOKUISAKI
                INNER JOIN コーステーブル CT
                    ON  CT.部署ＣＤ = TOKUISAKI.部署ＣＤ
                    AND CT.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                INNER JOIN コースマスタ COU
                    ON  COU.部署ＣＤ = CT.部署ＣＤ
                    AND COU.コースＣＤ = CT.コースＣＤ
                INNER JOIN 売上データ明細 URIAGE_MEISAI
                    ON  URIAGE_MEISAI.部署ＣＤ = TOKUISAKI.部署ＣＤ
                    AND URIAGE_MEISAI.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                    AND URIAGE_MEISAI.日付 >= '$TargetDateFrom' AND URIAGE_MEISAI.日付 <= '$TargetDateTo'
            WHERE
                TOKUISAKI.部署ＣＤ=$BushoCd
            GROUP BY
                URIAGE_MEISAI.得意先ＣＤ
        ";

        $dsn = 'sqlsrv:server=localhost;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        // $DataList = DB::select($sql);

        return $DataList;
    }

    /**
     * GetUriageListDiff
     */
    public function GetUriageListDiff($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDateFrom = $vm->TargetDateFrom;

        $sql = "
            SELECT
                URIAGE_MEISAI.得意先ＣＤ,
                SUM(URIAGE_MEISAI.掛売金額 - URIAGE_MEISAI.掛売値引) AS 売上
            FROM
                (
                    SELECT
                        RANKED.得意先ＣＤ
                        , RANKED.請求日付
                        , 今回請求額
                    FROM (
                        SELECT
                            TOKUISAKI.得意先ＣＤ
                            , SEIKYU.請求日付
                            , SEIKYU.今回請求額
                            , RANK() OVER(PARTITION BY SEIKYU.請求先ＣＤ ORDER BY SEIKYU.請求日付 DESC) AS RK
                        FROM
                            得意先マスタ TOKUISAKI
                            INNER JOIN コーステーブル CT
                                ON  CT.部署ＣＤ = TOKUISAKI.部署ＣＤ
                                AND CT.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                            INNER JOIN コースマスタ COU
                                ON  COU.部署ＣＤ = CT.部署ＣＤ
                                AND COU.コースＣＤ = CT.コースＣＤ
                            INNER JOIN 請求データ SEIKYU
                                ON  SEIKYU.部署ＣＤ = TOKUISAKI.部署ＣＤ
                                AND SEIKYU.請求先ＣＤ = TOKUISAKI.得意先ＣＤ
                        WHERE
                            TOKUISAKI.部署ＣＤ = $BushoCd
                            AND 請求日付 < '$TargetDateFrom'
                    ) RANKED
                    WHERE
                        RK=1
                ) SEIKYU
                INNER JOIN 売上データ明細 URIAGE_MEISAI
                    ON  URIAGE_MEISAI.得意先ＣＤ = SEIKYU.得意先ＣＤ
                    AND URIAGE_MEISAI.日付 >= DATEADD(dd, 1, SEIKYU.請求日付) AND URIAGE_MEISAI.日付 <= DATEADD(dd, -1, '$TargetDateFrom')
            GROUP BY
                URIAGE_MEISAI.得意先ＣＤ
        ";

        $dsn = 'sqlsrv:server=localhost;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        // $DataList = DB::select($sql);

        return $DataList;
    }

    /**
     * GetNyukinList
     */
    public function GetNyukinList($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDateFrom = $vm->TargetDateFrom;
        $TargetDateTo = $vm->TargetDateTo;

        $sql = "
            SELECT
                NYUKIN.得意先ＣＤ
                , SUM(NYUKIN.現金 + NYUKIN.小切手 + NYUKIN.振込 + NYUKIN.バークレー + NYUKIN.その他 + NYUKIN.相殺 + NYUKIN.値引) AS 入金
            FROM
                得意先マスタ TOKUISAKI
                INNER JOIN コーステーブル CT
                    ON  CT.部署ＣＤ = TOKUISAKI.部署ＣＤ
                    AND CT.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                INNER JOIN コースマスタ COU
                    ON  COU.部署ＣＤ = CT.部署ＣＤ
                    AND COU.コースＣＤ = CT.コースＣＤ
                INNER JOIN 入金データ NYUKIN
                    ON  NYUKIN.部署ＣＤ = TOKUISAKI.部署ＣＤ
                    AND NYUKIN.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                    AND NYUKIN.入金日付 >= '$TargetDateFrom' AND NYUKIN.入金日付 <= '$TargetDateTo'
            WHERE
                TOKUISAKI.部署ＣＤ=$BushoCd
            GROUP BY
                NYUKIN.得意先ＣＤ
        ";

        $dsn = 'sqlsrv:server=localhost;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        // $DataList = DB::select($sql);

        return $DataList;
    }

    /**
     * GetNyukinListDiff
     */
    public function GetNyukinListDiff($vm)
    {
        $BushoCd = $vm->BushoCd;
        $TargetDateFrom = $vm->TargetDateFrom;

        $sql = "
            SELECT
                NYUKIN.得意先ＣＤ
                , SUM(NYUKIN.現金 + NYUKIN.小切手 + NYUKIN.振込 + NYUKIN.バークレー + NYUKIN.その他 + NYUKIN.相殺 + NYUKIN.値引) AS 入金
            FROM
                (
                    SELECT
                        RANKED.得意先ＣＤ
                        , RANKED.請求日付
                        , 今回請求額
                    FROM (
                        SELECT
                            TOKUISAKI.得意先ＣＤ
                            , SEIKYU.請求日付
                            , SEIKYU.今回請求額
                            , RANK() OVER(PARTITION BY SEIKYU.請求先ＣＤ ORDER BY SEIKYU.請求日付 DESC) AS RK
                        FROM
                            得意先マスタ TOKUISAKI
                            INNER JOIN コーステーブル CT
                                ON  CT.部署ＣＤ = TOKUISAKI.部署ＣＤ
                                AND CT.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                            INNER JOIN コースマスタ COU
                                ON  COU.部署ＣＤ = CT.部署ＣＤ
                                AND COU.コースＣＤ = CT.コースＣＤ
                            INNER JOIN 請求データ SEIKYU
                                ON  SEIKYU.部署ＣＤ = TOKUISAKI.部署ＣＤ
                                AND SEIKYU.請求先ＣＤ = TOKUISAKI.得意先ＣＤ
                        WHERE
                            TOKUISAKI.部署ＣＤ = $BushoCd
                            AND 請求日付 < '$TargetDateFrom'
                    ) RANKED
                    WHERE
                        RK=1
                ) SEIKYU
                INNER JOIN 入金データ NYUKIN
                    ON  NYUKIN.得意先ＣＤ = SEIKYU.得意先ＣＤ
                    AND NYUKIN.入金日付 >= DATEADD(dd, 1, SEIKYU.請求日付) AND NYUKIN.入金日付 <= DATEADD(dd, -1, '$TargetDateFrom')
            GROUP BY
                NYUKIN.得意先ＣＤ
        ";

        $dsn = 'sqlsrv:server=localhost;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        // $DataList = DB::select($sql);

        return $DataList;
    }
}
