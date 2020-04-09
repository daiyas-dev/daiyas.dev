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
use SebastianBergmann\Environment\Console;

class DAI01210Controller extends Controller
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
                各種テーブル.サブ各種CD2 AS 商品区分,
                各種テーブル.各種名称 AS 商品名
            FROM 各種テーブル, SUB
            WHERE 各種CD = (
                CASE
                    WHEN SUB.サブ各種CD2=2 THEN 27
                    WHEN SUB.サブ各種CD2 IS NOT NULL THEN 14
                    ELSE 33
                END
            )
        ";

        $DataList = DB::select($sql);
        // $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        // $user = 'daiyas';
        // $password = 'daiyas';

        // $pdo = new PDO($dsn, $user, $password);
        // $stmt = $pdo->query($sql);
        // $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // $pdo = null;

        return response()->json($DataList);
    }

    /**
     * GetDataList
     */
    public function GetDataList($request)
    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;

        $PrintOrder = $request->PrintOrder;
        $OrderCol = $PrintOrder == '0' ? "URIAGE.得意先ＣＤ" : "URIAGE.コースＣＤ";

        $sql = "
            SELECT
                TOKUI.得意先ＣＤ,
				TOKUI.得意先名,
                URIAGE.コースＣＤ,
                COURSE.コース名,
				URIAGE.日付,
                URIAGE.商品区分,
                SUM(ISNULL(URIAGE.現金個数, 0) + ISNULL(URIAGE.掛売個数, 0) + ISNULL(URIAGE.分配元数量, 0)) AS 個数
            FROM 得意先マスタ TOKUI
                INNER JOIN [売上データ明細] URIAGE
		            ON (URIAGE.日付 >= DATEADD(dd, 1, EOMONTH ('$TargetDate' , -1)) AND URIAGE.日付 <= EOMONTH('$TargetDate'))
					AND URIAGE.部署ＣＤ = TOKUI.部署CD
                    AND URIAGE.得意先ＣＤ = TOKUI.得意先ＣＤ
				LEFT OUTER JOIN コースマスタ COURSE
					ON  COURSE.部署ＣＤ=URIAGE.部署ＣＤ
					AND COURSE.コースＣＤ=URIAGE.コースＣＤ
            WHERE
                TOKUI.部署ＣＤ = $BushoCd
            AND (TOKUI.得意先ＣＤ = TOKUI.受注得意先ＣＤ OR TOKUI.受注得意先ＣＤ = 0)
            GROUP BY
                TOKUI.得意先ＣＤ,
				TOKUI.得意先名,
                URIAGE.コースＣＤ,
                COURSE.コース名,
				URIAGE.日付,
                URIAGE.商品区分
        ";

        // $DataList = DB::select($sql);
        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
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
        return response()->json($this->GetDataList($request));
    }
}
