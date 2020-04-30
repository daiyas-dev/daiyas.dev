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

class DAI05150Controller extends Controller
{
    /**
     * GetClaimList
     */
    public function GetClaimList($request) {
        $sql = "
			SELECT
				CL.*
				,KB.部署名 AS 管轄部門名
                ,TM.得意先名
                ,CK.各種名称 AS クレーム区分名
				,ISNULL(GBK.各種名称, CL.部門名) AS 原因部署名
				,GTK.各種名称 AS 原因部署担当
                ,CASE WHEN CL.その後客先失客 = 0 THEN '継続'
                    WHEN CL.その後客先失客 = 1 THEN '失客'
                    WHEN CL.その後客先失客 IS NULL THEN '未処理'
                END AS ステータス
                ,CASE WHEN CL.その後客先失客 = 1 THEN CL.失客日 ELSE NULL END AS 失客日
			FROM
				クレームデータ CL
				LEFT OUTER JOIN 部署マスタ KB
					ON KB.部署CD=CL.管轄部門コード
				LEFT OUTER JOIN 得意先マスタ TM
					ON TM.得意先ＣＤ=CL.顧客コード
				LEFT JOIN 各種テーブル CK
					ON  CK.各種CD=47
					AND CK.サブ各種CD1=CL.クレーム区分コード
				LEFT OUTER JOIN 各種テーブル GBK
					ON  GBK.各種CD=49
					AND GBK.サブ各種CD1=CL.部門コード
				LEFT OUTER JOIN 各種テーブル GTK
					ON  GTK.各種CD=50
					AND GTK.サブ各種CD1=CL.原因部署担当コード
			ORDER BY
				KB.部署ＣＤ ASC ,
				CL.クレームID DESC
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
