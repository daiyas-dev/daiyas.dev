<?php

namespace App\Http\Controllers;

use App\Models\祝日マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;

class DAI05140Controller extends Controller
{
    /**
     * GetClaimList
     */
    public function GetClaimList($request)
    {
        $sql = "
			SELECT
				CL.*
				,KB.部署名 AS 管轄部門名
				,TM.得意先名
				,CK.各種名称 AS クレーム区分名
				,PM.商品名
				,UT.担当者名 AS 受付担当者名
				,CT.担当者名 AS クレーム処理者名
				,ISNULL(GBK.各種名称, CL.部門名) AS 原因部署名
				,GTK.各種名称 AS 原因部署担当
				,ISNULL(GNT.担当者名, CL.原因入力担当者名) AS 原因入力担当者名
                ,ISNULL(TNT.担当者名, CL.対策入力担当者名) AS 対策入力担当者名
                ,CASE WHEN CL.その後客先失客 = 0 THEN '継続'
                    WHEN CL.その後客先失客 = 1 THEN '失客'
                    WHEN CL.その後客先失客 IS NULL THEN '未処理'
                END AS ステータス
                ,CASE WHEN CL.その後客先失客 = 1 THEN CL.失客日 ELSE NULL END AS 失客日
			FROM
				クレームデータ CL
				LEFT JOIN 部署マスタ KB
					ON KB.部署CD=CL.管轄部門コード
				LEFT JOIN 各種テーブル CK
					ON  CK.各種CD=47
					AND CK.サブ各種CD1=CL.クレーム区分コード
				LEFT JOIN 得意先マスタ TM
					ON TM.得意先ＣＤ=CL.顧客コード
				LEFT JOIN 商品マスタ PM
					ON PM.商品ＣＤ=CL.商品コード
				LEFT JOIN 担当者マスタ UT
					ON UT.担当者ＣＤ=CL.受付担当者コード
				LEFT JOIN 担当者マスタ CT
					ON CT.担当者ＣＤ=CL.クレーム処理者コード
				LEFT JOIN 各種テーブル GBK
					ON  GBK.各種CD=49
					AND GBK.サブ各種CD1=CL.部門コード
				LEFT JOIN 各種テーブル GTK
					ON  GTK.各種CD=50
					AND GTK.サブ各種CD1=CL.原因部署担当コード
				LEFT JOIN 担当者マスタ GNT
					ON GNT.担当者ＣＤ=CL.原因入力担当者コード
				LEFT JOIN 担当者マスタ TNT
					ON TNT.担当者ＣＤ=CL.対策入力担当者コード
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
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $model = new 祝日マスタ();
        $model->fill($params);

        $data = collect($model)->all();

        // トランザクション開始
        DB::transaction(function() use ($params, $data) {
            $TargetDate = $params['対象日付'];

            DB::table('祝日マスタ')->updateOrInsert(
                ['対象日付' => $TargetDate],
                $data
            );
        });

        $savedData = array_merge(['対象日付' => $params['対象日付']], $data);

        return response()->json([
            'result' => true,
            'model' => $savedData,
        ]);
    }

    /**
     * Delete
     */
    public function Delete($request)
    {
        $TargetDate = $request->TargetDate;

        // トランザクション開始
        DB::transaction(function() use ($TargetDate) {

            DB::table('祝日マスタ')->where('対象日付', '=', $TargetDate)->delete();

        });

        return response()->json([
            'result' => true,
        ]);
    }
}
