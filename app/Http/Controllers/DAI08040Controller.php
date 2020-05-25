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

class DAI08040Controller extends Controller
{
    /**
     * GetCourseList
     */
    public function GetCourseList($request)
    {
        $BushoCd = $request->BushoCd;
        $BushoCd = !!$BushoCd ? "$BushoCd" : "''";

        $sql = "
            SELECT
                *
                ,コースＣＤ AS Cd
                ,コース名 AS CdNm
            FROM
                コースマスタ
            WHERE
                部署ＣＤ=$BushoCd
            ORDER BY
                コースＣＤ
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pdo = null;

        return response()->json($DataList);
    }

    /**
     * GetChumonList
     */
    public function GetHaisoYoteiHyo($request) {
        $BushoCd = $request->BushoCd;
        $DeliveryDate = $request->DeliveryDate;
        $AreaCd = $request->AreaCd;
        $DeliveryKbn = $request->DeliveryKbn;
        $IsPrintOut = $request->IsPrintOut;
        $PrintOrder = $request->PrintOrder;

        $WhereArea = !!$AreaCd ? "AND area.エリアＣＤ = $AreaCd" : "";
        $WherePrintout = $IsPrintOut=="1"? "AND Hchu.預り金 <> 0" : "";

        $WhereDeliveryKbn = "";
        if ($DeliveryKbn == "0") $WhereDeliveryKbn = "AND Hchu.配達区分 = 0";
        if ($DeliveryKbn == "1") $WhereDeliveryKbn = "AND Hchu.配達区分 = 1";

        $OrderPrint="";
        if ($PrintOrder=="0") $OrderPrint = "部署ＣＤ,エリアＣＤ,配達順,受注Ｎｏ,明細Ｎｏ";
        if ($PrintOrder=="1") $OrderPrint = "部署ＣＤ,受注Ｎｏ,明細Ｎｏ";

        $sql = "
        WITH 抽出データ AS
        (
        SELECT
             Hchu.部署ＣＤ
            ,bmas.部署名
            ,area.エリアＣＤ
            ,cmas.コース名
            ,area.配達順
            ,Hchu.配達時間
            ,tmas.売掛現金区分 AS 売掛現金区分
            ,kmas3.各種名称 AS 売掛現金区分名称
            ,Hchu.受注Ｎｏ
            ,Hchu.連絡区分
            ,kmas.各種名称 AS 連絡区分名称
            ,Hchu.配達区分
            ,kmas4.各種名称 AS 配達区分名称
            ,Mchu.明細Ｎｏ
            ,Hchu.得意先ＣＤ
            ,tmas.得意先名
            ,tmas.住所１+tmas.住所２ as 住所
            ,Hchu.地域区分
            ,kmas2.各種名称 AS 地域区分名称
            ,Hchu.配達先１ + Hchu.配達先２ as 配達先
            ,tmas.電話番号１
            ,ssmas.商品種類
            ,ssmas.商品種類名
            ,Mchu.商品ＣＤ
            ,Mchu.商品名
            ,Mchu.備考
            ,Mchu.数量
            ,Mchu.単価
            ,Mchu.金額
            ,Mchu.消費税
            ,Hchu.預り金
            ,Hchu.AMPM区分
            ,IIF(Hchu.AMPM区分=0, 'AM', 'PM') AS AMPM区分名称
            ,Hchu.税区分
            ,Mchu.提げ袋
            ,Mchu.風呂敷
        FROM
            仕出しエリアデータ area
            left join 仕出し注文明細データ Mchu on Mchu.部署ＣＤ = area.部署ＣＤ AND Mchu.受注Ｎｏ = area.受注Ｎｏ AND Mchu.配達日付 = area.注文日付
            left join 仕出し注文データ Hchu on Hchu.部署ＣＤ = area.部署ＣＤ AND Hchu.受注Ｎｏ = area.受注Ｎｏ AND Hchu.配達日付 = area.注文日付
            left join 部署マスタ bmas on bmas.部署ＣＤ = area.部署ＣＤ
            left join 商品マスタ smas on smas.商品ＣＤ = Mchu.商品ＣＤ
            left join 得意先マスタ tmas on tmas.得意先ＣＤ = Hchu.得意先ＣＤ
            left join 各種テーブル kmas on kmas.各種CD = 30 AND kmas.行NO = Hchu.連絡区分
            left join 各種テーブル kmas2 on kmas2.各種CD = 32 AND kmas2.行NO = Hchu.地域区分
            left join 各種テーブル kmas3 on kmas3.各種CD = 1  AND kmas3.行NO = tmas.売掛現金区分
            left join 各種テーブル kmas4 on kmas4.各種CD = 31 AND kmas4.行NO = Hchu.配達区分
            left join 各種テーブル kmas5 on kmas5.各種CD = 26 AND kmas5.サブ各種CD1 = Hchu.部署ＣＤ
            left join コースマスタ cmas on cmas.部署ＣＤ = area.部署ＣＤ  AND cmas.コース区分 = 1 AND cmas.コースＣＤ = area.エリアＣＤ
            left join 商品種類マスタ ssmas on ssmas.商品ＣＤ = Mchu.商品ＣＤ AND ssmas.商品種類 = Mchu.商品種類 AND smas.部署ｸﾞﾙｰﾌﾟ = kmas5.サブ各種CD2
        where
        	smas.部署ｸﾞﾙｰﾌﾟ = kmas5.サブ各種CD2
        AND Hchu.部署ＣＤ = $BushoCd
        AND Hchu.配達日付 = '$DeliveryDate'
        $WhereArea
        $WherePrintout
        $WhereDeliveryKbn
        )
        SELECT
            *
        FROM
            抽出データ
        ORDER BY
            $OrderPrint
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
}
