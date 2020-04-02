<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI07020Controller extends Controller
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
                "SeikyuList" => $this->GetSeikyuList($vm),
                "UriageListDiff" => $this->GetUriageListDiff($vm),
                "NyukinListDiff" => $this->GetNyukinListDiff($vm),
                "UriageListThisWeek" => $this->GetUriageList($vm),
                "NyukinListThisWeek" => $this->GetNyukinList($vm),
                "UriageListNextWeek" => $this->GetUriageList($nextVm),
                "NyukinListNextWeek" => $this->GetNyukinList($nextVm),
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
                TOKUISAKI.得意先ＣＤ
                , TOKUISAKI.得意先名
                , TOKUISAKI.電話番号１
                , URIAGE_MEISAI.日付
                , CT.コースＣＤ
                , COU.コース名
                , CT.ＳＥＱ AS 順
                , URIAGE_MEISAI.食事区分
                , (CASE URIAGE_MEISAI.食事区分
                    WHEN '1' THEN '朝食'
                    WHEN '2' THEN '昼食'
                    WHEN '3' THEN '夕食'
                    WHEN '4' THEN '夜食'
                    WHEN '5' THEN '別注'
                    ELSE '' END) AS 食事区分名
                , URIAGE_MEISAI.商品ＣＤ
                , SHOHIN.商品名
                , SHOHIN.商品略称
                , URIAGE_MEISAI.掛売金額
                , URIAGE_MEISAI.掛売値引
                , (URIAGE_MEISAI.掛売金額 - URIAGE_MEISAI.掛売値引) AS 売上
                , (ISNULL(TOKUISAKI.住所１, '')+ ISNULL(TOKUISAKI.住所２, '')) AS 住所
                , TOKUISAKI.備考１
				, (ISNULL((CASE TOKUISAKI.締区分
					WHEN '0' THEN '日締'
					WHEN '1' THEN '週締'
					WHEN '2' THEN '月締'
					ELSE '' END) , '')+ '・' +
				   ISNULL((CASE TOKUISAKI.支払方法１
					WHEN '1' THEN '現金'
					WHEN '2' THEN '小切手'
					WHEN '3' THEN '振込'
					WHEN '4' THEN '振替'
					WHEN '5' THEN 'チケット'
					WHEN '7' THEN '金券'
					ELSE '' END) , '')) AS 締支払
                , URIAGE_MEISAI.掛売個数
                , URIAGE_MEISAI.現金個数
            FROM
                得意先マスタ TOKUISAKI
                INNER JOIN コーステーブル CT
                    ON  CT.部署ＣＤ = TOKUISAKI.部署ＣＤ
                    AND CT.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                INNER JOIN コースマスタ COU
                    ON  COU.部署ＣＤ = CT.部署ＣＤ
                    AND COU.コースＣＤ = CT.コースＣＤ
                LEFT OUTER JOIN 売上データ明細 URIAGE_MEISAI
                    ON  URIAGE_MEISAI.部署ＣＤ = TOKUISAKI.部署ＣＤ
                    AND URIAGE_MEISAI.得意先ＣＤ = TOKUISAKI.得意先ＣＤ
                    AND (URIAGE_MEISAI.日付 >= '$TargetDateFrom' AND URIAGE_MEISAI.日付 <= '$TargetDateTo')
                LEFT OUTER JOIN 商品マスタ SHOHIN
                    ON  SHOHIN.商品ＣＤ = URIAGE_MEISAI.商品ＣＤ
            WHERE
                TOKUISAKI.部署ＣＤ=$BushoCd
                --AND TOKUISAKI.得意先ＣＤ IN (11271, 13664, 13734)
                --AND COU.コースＣＤ=102
            ORDER BY
                CT.コースＣＤ
                , CT.ＳＥＱ
                , URIAGE_MEISAI.日付
                , URIAGE_MEISAI.食事区分
                , URIAGE_MEISAI.商品ＣＤ
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
