<?php

namespace App\Http\Controllers;

use App\Models\コーステーブル;
use App\Models\各種テーブル;
use App\Models\商品マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Facades\DB as IlluminateDB;
use PDO;

class DAI01200Controller extends Controller
{
    /**
     *  getSyouhinKubunData
     */
    public function getSyouhinKubunData($vm)
    {
        $BushoCd = $vm->BushoCd;
        $sql = "
            SELECT
                行NO AS 商品区分,
                各種名称
            FROM 各種テーブル
            WHERE 各種CD=
                (
                    SELECT
                        IIF(サブ各種CD2=2, 27, 14)
                    FROM 各種テーブル
                    WHERE 各種CD=26
                    AND サブ各種CD1=$BushoCd
                )
                AND 行NO<=7
            ORDER BY 各種CD,行NO
        ";
        //$data = DB::select($sql);
        //return $data;
        $dsn = 'sqlsrv:server=localhost;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        return $DataList;
    }

    /**
     * GetCourseMeisaiData
     */
    public function GetCourseMeisaiData($request){
        $BushoCd = $request->BushoCd;
        $CourseCd=$request->CourseCd;
        $DateStart = $request->DateStart;
        $DateEnd = $request->DateEnd;

        //コース別明細データ.部署CDの「CD」は半角が正しいです。入力間違いではありません。
        $sql = "
                SELECT
                    コース別明細データ.*
                    ,コースマスタ.コース名
                    ,コースマスタ.担当者ＣＤ
                    ,担当者マスタ.担当者名
                FROM
                    コース別明細データ
                    LEFT JOIN コースマスタ
                         ON コースマスタ.部署ＣＤ=コース別明細データ.部署CD
                        AND コースマスタ.コースＣＤ=コース別明細データ.コースＣＤ
                    LEFT JOIN [担当者マスタ]
                    ON 担当者マスタ.担当者ＣＤ=コースマスタ.担当者ＣＤ
                WHERE
                        コース別明細データ.部署CD = $BushoCd
                    AND コース別明細データ.日付 >= '$DateStart'
                    AND コース別明細データ.日付 <= '$DateEnd'
                ";
        if($CourseCd!=null)
        {
            $sql.=" AND コース別明細データ.コースＣＤ = '$CourseCd'";
        }

        //$data = DB::select($sql);
        //return $data;
        $dsn = 'sqlsrv:server=localhost;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        return $DataList;
    }

    /**
     * GetUriageMeisaiData
     */
    public function GetUriageMeisaiData($request){
        $BushoCd = $request->BushoCd;
        $DateStart = $request->DateStart;
        $DateEnd = $request->DateEnd;
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

        //$data = DB::select($sql);
        //return $data;
        $dsn = 'sqlsrv:server=localhost;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        return $DataList;
    }

    /**
     * GetNyukinData
     */
    public function GetNyukinData($request){
        $BushoCd = $request->BushoCd;
        $DateStart = $request->DateStart;
        $DateEnd = $request->DateEnd;

        $sql = "
                WITH WITH_コーステーブル AS(
                    SELECT * FROM [コーステーブル]  WHERE 部署ＣＤ = $BushoCd
                )
                SELECT
                WITH_コーステーブル.部署ＣＤ
                    , WITH_コーステーブル.コースＣＤ
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
                    ,WITH_コーステーブル
                    INNER JOIN 得意先マスタ ON 得意先マスタ.受注得意先ＣＤ = WITH_コーステーブル.得意先ＣＤ
                WHERE
                    入金データ.得意先ＣＤ = 得意先マスタ.得意先ＣＤ
                    AND 入金データ.入金日付 >= '$DateStart'
                    AND 入金データ.入金日付 <= '$DateEnd'
                    AND WITH_コーステーブル.部署ＣＤ = $BushoCd
                GROUP BY
                      WITH_コーステーブル.部署ＣＤ
                    , WITH_コーステーブル.コースＣＤ
                    , 入金データ.入金日付
                    , 入金データ.入金区分
                ";

        //$data = DB::select($sql);
        $dsn = 'sqlsrv:server=localhost;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        return $DataList;
    }

    /**
     * Search
     */
    public function Search($request) {
        return response()->json(
            [
                [
                    "UriageMeisaiData" => $this->GetUriageMeisaiData($request),
                    "NyukinData" => $this->GetNyukinData($request),
                    "CourseMeisaiData" => $this->GetCourseMeisaiData($request),
                    "SyouhinKubunData"=> $this->getSyouhinKubunData($request),
                ]
            ]
        );
    }
}
