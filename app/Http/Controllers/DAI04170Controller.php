<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Carbon;
use PDO;

class DAI04170Controller extends Controller
{
    /**
     * GetManufacturedList
     */
    public function GetManufacturedList($request)
    {
        $sql = "
            SELECT
                MM.*
                ,ISNULL(BG.各種名称, '') AS 部署グループ名
                ,ISNULL(KP.各種名称, '') AS 既定製造パターン名
                ,ISNULL(SK.各種名称, '') AS 商品区分名
                ,ISNULL(BK.各種名称, '') AS 弁当区分名
                ,ISNULL(HK.各種名称, '') AS 表示区分名
                ,ISNULL(MP.商品名, '') AS 主食名
                ,ISNULL(SP.商品名, '') AS 副食名
            FROM
                製造品マスタ MM
                LEFT OUTER JOIN 各種テーブル BG
                    ON  BG.各種CD=18
                    AND BG.行NO=MM.部署グループ
                LEFT OUTER JOIN 各種テーブル KP
                    ON  KP.各種CD=35
                    AND KP.行NO=MM.既定製造パターン
                LEFT OUTER JOIN 各種テーブル SK
                    ON  SK.各種CD=14
                    AND SK.行NO=MM.商品区分
                LEFT OUTER JOIN 各種テーブル BK
                    ON  BK.各種CD=15
                    AND BK.行NO=MM.弁当区分
                LEFT OUTER JOIN 各種テーブル HK
                    ON  HK.各種CD=17
                    AND HK.行NO=MM.表示ＦＬＧ
                LEFT OUTER JOIN 商品マスタ MP
                    ON  MP.弁当区分 = 2
                    AND MP.商品ＣＤ=MM.主食ＣＤ
                LEFT OUTER JOIN 商品マスタ SP
                    ON  SP.弁当区分 = 1
                    AND SP.商品ＣＤ=MM.副食ＣＤ
            ORDER BY
                MM.部署グループ,
                MM.商品ＣＤ,
                MM.既定製造パターン
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
     * Search
     */
    public function Search($request)
    {
        return response()->json($this->GetManufacturedList($request));
    }
}
