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

        $WhereBusho = !!$BushoCd ? "AND Hchu.部署ＣＤ = $BushoCd" : "";
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
            ,area.エリアＣＤ
            ,cmas.コース名
            ,area.配達順
            ,Hchu.配達時間
            ,kmas3.各種名称 AS 売掛現金区分
            ,Hchu.受注Ｎｏ
            ,kmas.各種名称 AS 連絡区分
            ,kmas4.各種名称 AS 配達区分
            ,Mchu.明細Ｎｏ
            ,Hchu.得意先ＣＤ
            ,tmas.得意先名
            ,tmas.住所１+tmas.住所２ as 住所
            ,kmas2.各種名称 AS 地域区分
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
            ,Hchu.税区分
            ,Mchu.提げ袋
            ,Mchu.風呂敷
        FROM
            仕出しエリアデータ area
            left join 仕出し注文明細データ Mchu on Mchu.部署ＣＤ = area.部署ＣＤ AND Mchu.受注Ｎｏ = area.受注Ｎｏ AND Mchu.配達日付 = area.注文日付
            left join 仕出し注文データ Hchu on Hchu.部署ＣＤ = area.部署ＣＤ AND Hchu.受注Ｎｏ = area.受注Ｎｏ AND Hchu.配達日付 = area.注文日付
            left join 商品マスタ smas on smas.商品ＣＤ = Mchu.商品ＣＤ
            left join 商品種類マスタ ssmas on ssmas.商品ＣＤ = Mchu.商品ＣＤ
            left join 得意先マスタ tmas on tmas.得意先ＣＤ = Hchu.得意先ＣＤ
            left join 各種テーブル kmas on kmas.各種CD = 30 AND kmas.行NO = Hchu.連絡区分
            left join 各種テーブル kmas2 on kmas2.各種CD = 32 AND kmas2.行NO = Hchu.地域区分
            left join 各種テーブル kmas3 on kmas3.各種CD = 1  AND kmas3.行NO = tmas.売掛現金区分
            left join 各種テーブル kmas4 on kmas4.各種CD = 31 AND kmas4.行NO = Hchu.配達区分
            left join 各種テーブル kmas5 on kmas5.各種CD = 26 AND kmas5.サブ各種CD1 = Hchu.部署ＣＤ
            left join コースマスタ cmas on cmas.部署ＣＤ = area.部署ＣＤ  AND cmas.コース区分 = 1 AND cmas.コースＣＤ = area.エリアＣＤ
        where
            smas.部署ｸﾞﾙｰﾌﾟ = kmas5.サブ各種CD2
        $WhereBusho
        AND Hchu.配達日付 = '$DeliveryDate'
        $WhereArea
        $WherePrintout
        $WhereDeliveryKbn
        ),

        抽出データ2 AS
        (
        SELECT
             ROW_NUMBER() OVER(PARTITION BY B1.部署ＣＤ, 受注Ｎｏ ORDER BY B1.部署ＣＤ, 受注Ｎｏ,明細Ｎｏ) AS ROWNUMBER
            ,B1.部署名
            ,D1.*
        FROM
            抽出データ D1
            LEFT JOIN 部署マスタ B1 on B1.部署ＣＤ = D1.部署ＣＤ
        )

        SELECT
			 ROWNUMBER
            ,部署ＣＤ
            ,部署名
            ,エリアＣＤ
            ,コース名
            ,配達順
            ,IIF(ROWNUMBER = 1, 配達時間, NULL) AS 配達時間
            ,受注Ｎｏ
            ,IIF(ROWNUMBER = 2, 連絡区分, CONVERT(varchar, 受注Ｎｏ)) AS 受注連絡
            ,明細Ｎｏ
            ,得意先ＣＤ
            ,IIF(ROWNUMBER = 2, 住所, CONVERT(VARCHAR, 得意先ＣＤ) + '　' + ISNULL(得意先名, '')) AS 得意先
            ,住所
            ,IIF(ROWNUMBER = 2, IIF(AMPM区分 != '0', 'PM', 'AM'), 地域区分)  AS 地域区分
            ,IIF(ROWNUMBER = 1, 電話番号１, NULL) AS 電話番号１
            ,商品種類
            ,商品種類名
            ,商品ＣＤ
            ,商品名
            ,備考
            ,数量
            ,単価
            ,IIF(税区分 = '0', 金額 - 消費税, 金額) AS 金額
            ,金額 AS 合計
            ,IIF(税区分 = '0', 消費税, 0) AS 消費税
            ,IIF(ROWNUMBER = 1, 預り金, NULL) AS 預り金
            ,AMPM区分
            ,税区分
            ,提げ袋
            ,風呂敷
        FROM
            抽出データ2
        UNION
        SELECT
             2 AS ROWNUMBER
            ,D1.部署ＣＤ
            ,部署名
            ,エリアＣＤ
            ,コース名
            ,配達順
            ,NULL AS 配達時間
            ,D1.受注Ｎｏ
            ,連絡区分 AS 受注連絡
            ,99 AS 明細Ｎｏ
            ,得意先ＣＤ
            ,住所 AS 得意先
            ,NULL AS 住所
            ,IIF(AMPM区分 != '0', 'PM', 'AM') AS 地域区分
            ,NULL AS 電話番号１
            ,NULL AS 商品種類
            ,NULL AS 商品種類名
            ,NULL AS 商品ＣＤ
            ,NULL AS 商品名
            ,NULL AS 備考
            ,NULL AS 数量
            ,NULL AS 単価
            ,NULL AS 金額
            ,NULL AS 合計
            ,NULL AS 消費税
            ,NULL AS 預り金
            ,NULL AS AMPM区分
            ,NULL AS 税区分
            ,NULL AS 提げ袋
            ,NULL AS 風呂敷
        FROM
            抽出データ2 D1
            INNER JOIN (SELECT 部署ＣＤ, 受注Ｎｏ FROM 抽出データ2 GROUP BY 部署ＣＤ, 受注Ｎｏ HAVING COUNT(*) = 1) D2 ON
                    D1.部署ＣＤ = D2.部署ＣＤ
                AND D1.受注Ｎｏ = D2.受注Ｎｏ
        UNION
        SELECT
			 99 AS ROWNUMBER
            ,部署ＣＤ
            ,部署名
            ,エリアＣＤ
            ,コース名
            ,配達順
            ,売掛現金区分 AS 配達時間
            ,受注Ｎｏ
            ,配達区分 AS 受注連絡
            ,99 AS 明細Ｎｏ
            ,得意先ＣＤ
            ,配達先 AS 得意先
            ,NULL AS 住所
            ,NULL AS 地域区分
            ,NULL AS 電話番号１
            ,NULL AS 商品種類
            ,NULL AS 商品種類名
            ,NULL AS 商品ＣＤ
            ,NULL AS 商品名
            ,NULL AS 備考
            ,NULL AS 数量
            ,NULL AS 単価
            ,NULL AS 金額
            ,NULL AS 合計
            ,NULL AS 消費税
            ,NULL AS 預り金
            ,NULL AS AMPM区分
            ,NULL AS 税区分
            ,NULL AS 提げ袋
            ,NULL AS 風呂敷
        FROM
            抽出データ2
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
