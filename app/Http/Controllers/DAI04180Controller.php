<?php

namespace App\Http\Controllers;

use App\Models\商品種類マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use PDO;

class DAI04180Controller extends Controller
{

    /**
     * Search
     */
    public function Search($request)
    {
        $Utilities = new UtilitiesController();

        return response()->json([
            [
                "ProductKindList" => $this->GetProductKindList($request),
                "BushoGroupList" => $this->GetBushoGroupList($request),
                "ProductList" => $this->GetProductList($request),
            ]
        ]);
    }

    /**
     * GetProductKindList
     */
    public function GetProductKindList($request)
    {
        $BushoCd = $request->BushoCd;

        $sql = "
            SELECT
                SK.*
				,B.部署CD
				,B.部署名
				,P.商品名
            FROM
                商品種類マスタ SK
					LEFT OUTER JOIN 各種テーブル BG
						ON  BG.サブ各種CD2 = SK.部署グループ
                        AND BG.各種CD = 26
                        AND BG.サブ各種CD1 = $BushoCd
					INNER JOIN 部署マスタ B
						ON B.部署CD = BG.サブ各種CD1
					INNER JOIN 商品マスタ P
						ON P.商品ＣＤ = SK.商品ＣＤ
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
     * GetBushoGroupList
     */
    public function GetBushoGroupList($request)
    {
        $BushoCd = $request->BushoCd;

        $sql = "
			SELECT
				BG.*
				,B.部署名
			FROM (
				SELECT
					サブ各種CD1 AS 部署ＣＤ,
					MAX(サブ各種CD2) AS 部署グループ
				FROM 各種テーブル
				WHERE
					各種CD = 26
				GROUP BY
					サブ各種CD1
			) BG
				INNER JOIN 部署マスタ B
					ON B.部署CD = BG.部署ＣＤ
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
     * GetProductList
     */
    public function GetProductList($request)
    {
        $BushoCd = $request->BushoCd;

        $sql = "
            SELECT
                商品ＣＤ AS Cd,
                商品名 AS CdNm,
                商品ＣＤ,
                商品名,
                商品区分,
                売価単価,
                表示ＦＬＧ
            FROM
                商品マスタ
            /*
            WHERE
                表示ＦＬＧ = 0
            AND 部署グループ = (
                SELECT
                    MAX(サブ各種CD2) AS 部署グループ
                FROM 各種テーブル
                WHERE
                    各種CD = 26
                AND	サブ各種CD1=$BushoCd
            )
            */
            ORDER
                BY 商品ＣＤ
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
        $duplicateList = [];
        try {
            DB::beginTransaction();

            $saveList = $params['SaveList'];

            $AddList = $saveList['AddList'];
            $UpdateList = $saveList['UpdateList'];
            $OldList = $saveList['OldList'];
            $DeleteList = $saveList['DeleteList'];

            //UpdateList
            foreach ($OldList as $index => $rec) {
                $data = $UpdateList[$index];

                try{
                    商品種類マスタ::query()
                        ->where('部署グループ', $rec['部署グループ'])
                        ->where('商品種類', $rec['商品種類'])
                        ->where('商品ＣＤ', $rec['商品ＣＤ'])
                        ->update($data);

                } catch (Exception $exception) {
                    $errMs = $exception->getCode();

                    //主キー重複
                    if($errMs == "23000"){
                        $duplicateList = collect($duplicateList) ->push([$data]);
                    } else {
                        throw $exception;
                    }
                }
            }

            //AddList
            foreach ($AddList as $rec) {
                $data = $rec;
                $cnt = DB::table('商品種類マスタ')
                    ->where('部署グループ', $rec['部署グループ'])
                    ->where('商品種類', $rec['商品種類'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->count();

                if ($cnt == 0){
                    商品種類マスタ::insert($data);
                }else{
                    //主キー重複
                    $duplicateList = collect($duplicateList) ->push([$data]);
                }
            }

            //DeleteList
            foreach ($DeleteList as $index => $rec) {
                商品種類マスタ::query()
                    ->where('部署グループ', $rec['部署グループ'])
                    ->where('商品種類', $rec['商品種類'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->delete();
            }

            DB::commit();

        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            "result" => true,
            'duplicateList' => $duplicateList,
        ]);
    }
}
