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
use Illuminate\Support\Facades\Log;

class DAI08020Controller extends Controller
{
    /**
     * GetNouhinList
     */
    public function GetNouhinList($request)
    {
        $BushoCd = $request->BushoCd;
        $DeliveryDate = $request->DeliveryDate;
        $DeliveryKbn = $request->DeliveryKbn;
        $IsPrintOut = $request->IsPrintOut;
        $PrintOrder = $request->PrintOrder;

        $WhereBusho = !!$BushoCd ? "AND T1.部署ＣＤ =$BushoCd" : "";
        $WherePrintout = $IsPrintOut=="0"? "AND T1.納品書発行フラグ = 0" : "";

        $WhereDeliveryKbn = "";
        if ($DeliveryKbn == "0") $WhereDeliveryKbn = "AND T1.配達区分 = 0";
        if ($DeliveryKbn == "1") $WhereDeliveryKbn = "AND T1.配達区分 = 1";

        $OrderPrint="T6.エリアＣＤ, T6.配達順";
        if ($PrintOrder=="0") $OrderPrint = "T6.エリアＣＤ, T6.配達順";
        if ($PrintOrder=="1") $OrderPrint = "T3.得意先名カナ";
        if ($PrintOrder=="2") $OrderPrint = "T1.得意先ＣＤ";
        if ($PrintOrder=="3") $OrderPrint = "T1.受注Ｎｏ";

        $sql = "
        SELECT
            0 AS ページ
            ,CONVERT(VARCHAR, T1.部署ＣＤ) + '-' + CONVERT(VARCHAR, T1.受注Ｎｏ) + '-' + CONVERT(VARCHAR, T1.配達日付) + '-' + CONVERT(VARCHAR, T1.得意先ＣＤ) AS page_key
            ,T3.郵便番号
            ,T3.住所１
            ,T3.住所２
            ,T1.得意先ＣＤ
            ,T3.得意先名カナ
            ,T3.得意先名
            ,T1.税区分
            ,RIGHT('0000' + CONVERT(VARCHAR, YEAR(T1.配達日付)), 4) + '年'
                +RIGHT('00' + CONVERT(VARCHAR, MONTH(T1.配達日付)), 2) + '月'
                +RIGHT('00' + CONVERT(VARCHAR, DAY(T1.配達日付)), 2) + '日('
                +   CASE DATEPART(WEEKDAY, T1.配達日付)
                        WHEN 1 THEN '日'
                        WHEN 2 THEN '月'
                        WHEN 3 THEN '火'
                        WHEN 4 THEN '水'
                        WHEN 5 THEN '木'
                        WHEN 6 THEN '金'
                        WHEN 7 THEN '土'
                    END + ')' AS 配達日付
            ,T1.配達時間
            ,   T1.製造締切時間
            ,T1.受注Ｎｏ
            ,(ISNULL(T3.電話番号１,'') + CHAR(13) + CHAR(10) + ISNULL(T3.ＦＡＸ１,'')) AS 電話番号
            ,ISNULL(T1.配達先１,'') + ISNULL(T1.配達先２,'') AS 配達先
            ,   ( SELECT 各種名称 FROM 各種テーブル WHERE 各種CD = 31 AND 行NO = T1.配達区分 ) AS 配達区分
            ,T2.商品名
            ,T2.数量
            ,T2.単価
            ,(
                    CASE T1.税区分
                        WHEN 0 THEN T2.金額 - ISNULL(T2.消費税, 0)
                        ELSE T2.金額
                    END
                ) AS 金額
            ,(
                    CASE T1.税区分
                        WHEN 0 THEN T1.合計金額 - ISNULL(T1.合計消費税, 0)
                        ELSE T1.合計金額
                    END
                ) AS 小計
            ,(
                    CASE T1.税区分
                        WHEN 0 THEN ISNULL(T1.合計消費税, 0)
                        ELSE 0
                    END
                ) AS 消費税
            ,T1.合計金額 AS 合計

            -- 2016/06/21 文言修正、製造用特記、事務用特記の追加 S.Nakahara Rep Start
            -- ,T1.事務用特記 AS 備考
            ,isnull(T1.製造用特記,'') + '  ' + isnull(T1.事務用特記,'') AS 備考
            -- 2016/06/21 文言修正、製造用特記、事務用特記の追加 S.Nakahara Rep End

            ,CONVERT(VARCHAR, T1.注文日付, 111) AS 注文日付
            ,T5.会社名称
            ,('　〒' + T5.郵便番号 + ' ' + T5.住所) AS 住所欄
            ,('TEL' + T5.電話番号 + '(代) FAX' + T5.FAX) AS TEL欄
            ,   ( CASE WHEN LEN(T1.預り日付) > 0 THEN
                    (   RIGHT('0000' + CONVERT(VARCHAR, YEAR(T1.配達日付)), 4) + '年'
                    +RIGHT('00' + CONVERT(VARCHAR, MONTH(T1.配達日付)), 2) + '月'
                    +RIGHT('00' + CONVERT(VARCHAR, DAY(T1.配達日付)), 2) + '日'
                    )
                    ELSE '' END
                ) AS 預り日付
            ,   ( CASE WHEN T1.預り金 = 0 THEN NULL ELSE T1.預り金 END ) AS 預り金
            ,   ( CASE WHEN T1.預り金 > 0 THEN 'ご入金頂きました。'
                    ELSE '' END ) AS 預りコメント
            ,   T1.部署ＣＤ AS key部署ＣＤ
            ,   T1.受注Ｎｏ AS key受注Ｎｏ
            ,   T1.配達日付 AS key配達日付
            ,   T6.エリアＣＤ
            ,   T6.配達順

            -- 2016/06/21 文言修正、製造用特記、事務用特記の追加 S.Nakahara Add Start
            ,   T1.部署ＣＤ
            ,   T5.部署名
            ,   T1.製造用特記
            ,   T1.事務用特記
            ,   M3.金融機関CD1 AS 会社_金融機関CD1
            ,   M4.銀行名 AS 会社_銀行名1
            ,   M3.金融機関支店CD1 AS 会社_金融機関支店CD1
            ,   M5.支店名  AS 会社_支店名1
            ,   M3.口座種別1 AS 会社_口座種別1
            ,   M8.各種名称 AS 会社_口座種別名1
            ,   M3.口座番号1 AS 会社_口座番号1
            ,   M3.口座名義人1 AS 会社_口座名義人1
            ,   M3.金融機関CD2 AS 会社_金融機関CD2
            ,   M6.銀行名 AS 会社_銀行名2
            ,   M3.金融機関支店CD2 AS 会社_金融機関支店CD2
            ,   M7.支店名  AS 会社_支店名2
            ,   M3.口座種別2 AS 会社_口座種別2
            ,   M9.各種名称 AS 会社_口座種別名2
            ,   M3.口座番号2 AS 会社_口座番号2
            ,   M3.口座名義人2 AS 会社_口座名義人2
            -- 2016/06/21 文言修正、製造用特記、事務用特記の追加 S.Nakahara Add End

        FROM
            仕出し注文データ T1
            LEFT JOIN 得意先マスタ T3 ON T1.得意先ＣＤ = T3.得意先ＣＤ
            LEFT JOIN 部署マスタ T5 ON T1.部署ＣＤ = T5.部署CD
            LEFT JOIN 仕出しエリアデータ T6 ON T6.部署ＣＤ = T1.部署ＣＤ AND T6.受注Ｎｏ = T1.受注Ｎｏ AND T6.注文日付 = T1.配達日付

            -- 2016/06/21 文言修正、製造用特記、事務用特記の追加 S.Nakahara Add Start
            LEFT OUTER JOIN 部署マスタ M3 ON T1.部署ＣＤ = M3.部署ＣＤ
            LEFT OUTER JOIN 金融機関名称 M4 ON M3.金融機関CD1 = M4.銀行CD
            LEFT OUTER JOIN 金融機関支店名称 M5 ON M3.金融機関CD1 = M5.銀行CD AND M3.金融機関支店CD1 = M5.支店CD
            LEFT OUTER JOIN 金融機関名称 M6 ON M3.金融機関CD2 = M6.銀行CD
            LEFT OUTER JOIN 金融機関支店名称 M7 ON M3.金融機関CD2 = M7.銀行CD AND M3.金融機関支店CD2 = M7.支店CD
            LEFT OUTER JOIN 各種テーブル M8 ON M8.各種CD = 7 AND M8.行ＮＯ = M3.口座種別1
            LEFT OUTER JOIN 各種テーブル M9 ON M9.各種CD = 7 AND M9.行ＮＯ = M3.口座種別2
            -- 2016/06/21 文言修正、製造用特記、事務用特記の追加 S.Nakahara Add End

            ,仕出し注文明細データ T2
        WHERE
            T1.部署ＣＤ=T2.部署ＣＤ
        AND T1.受注Ｎｏ=T2.受注Ｎｏ
        AND T1.配達日付=T2.配達日付
        AND T1.配達日付 ='$DeliveryDate'
        $WhereDeliveryKbn
        $WhereBusho
        $WherePrintout
        ORDER BY $OrderPrint
        ";

        Log::info('DAI08020 :GetNouhinListSQL' . "\n" . $sql);//TODO:

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
     * UpdatePrintoutFlag
     */
    public function UpdatePrintoutFlag($request)
    {
        DB::beginTransaction();

        try {
            $BushoCd = $request->BushoCd;
            $DeliveryDate = $request->DeliveryDate;
            $SaveList = $request['SaveList'];

            //更新実施
            foreach($SaveList as $SaveItem)
            {
                $BushoCd=$SaveItem['key部署ＣＤ'];
                $OrderNo=$SaveItem['key受注Ｎｏ'];
                $DeliveryDate=$SaveItem['key配達日付'];

                $sql = "
                UPDATE
                    仕出し注文データ
                SET
                    納品書発行フラグ = 1
                WHERE
                    部署ＣＤ = $BushoCd
                AND 受注Ｎｏ = $OrderNo
                AND 配達日付 = '$DeliveryDate'
                ";

                $result = DB::update($sql);
            }

            DB::commit();

        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            //'lastupdatedate'=>$this->LastUpdateDateSearch($request),
            //'edited' => $this->Search($request),
        ]);
    }
}
