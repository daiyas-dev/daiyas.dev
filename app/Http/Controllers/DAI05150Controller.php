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
			WITH COURSES AS (
				SELECT DISTINCT
					CT.得意先ＣＤ,
					CM.部署ＣＤ,
					FIRST_VALUE(CM.コースＣＤ) OVER(PARTITION BY CT.得意先ＣＤ, CM.部署ＣＤ ORDER BY CM.コースＣＤ) AS コースＣＤ,
					FIRST_VALUE(CM.コース名) OVER(PARTITION BY CT.得意先ＣＤ, CM.部署ＣＤ ORDER BY CM.コースＣＤ) AS コース名
				FROM
					コーステーブル CT
					LEFT OUTER JOIN コースマスタ CM
						ON CM.部署ＣＤ=CT.部署ＣＤ
						AND CM.コースＣＤ=CT.コースＣＤ
			)
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
                ,UK.各種名称 AS 受付方法名
                ,PM.商品名
				,COURSES.コースＣＤ
				,COURSES.コース名
				,KM.HACCP制定日
				,KM.HACCP改定日
				,KM.HACCP改定番号
			FROM
				クレームデータ CL
				LEFT OUTER JOIN 部署マスタ KB
					ON KB.部署CD=CL.管轄部門コード
				LEFT OUTER JOIN 得意先マスタ TM
					ON TM.得意先ＣＤ=CL.顧客コード
				LEFT OUTER JOIN 商品マスタ PM
					ON PM.商品ＣＤ=CL.商品コード
				LEFT OUTER JOIN COURSES
					ON COURSES.部署ＣＤ=CL.管轄部門コード
					AND COURSES.得意先ＣＤ=CL.顧客コード
				LEFT OUTER JOIN 各種テーブル CK
					ON  CK.各種CD=47
					AND CK.サブ各種CD1=CL.クレーム区分コード
				LEFT OUTER JOIN 各種テーブル UK
					ON  UK.各種CD=48
					AND UK.サブ各種CD1=CL.受付方法
				LEFT OUTER JOIN 各種テーブル GBK
					ON  GBK.各種CD=49
					AND GBK.サブ各種CD1=CL.部門コード
				LEFT OUTER JOIN 各種テーブル GTK
					ON  GTK.各種CD=50
					AND GTK.サブ各種CD1=CL.原因部署担当コード
				LEFT OUTER JOIN 管理マスタ KM
					ON KM.管理ＫＥＹ=1
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
