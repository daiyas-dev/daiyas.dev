<?php

namespace App\Http\Controllers;

use App\Models\モバイル対象商品;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use PDO;

class DAI05070Controller extends Controller
{
    /**
     * UploadFile
     * 3090から移植
     */
    public function UploadFile($request)
    {
        $this->validate($request, [
            'file' => [
                'required',
                'file',
            ]
        ]);

        try {
            if ($request->file('file')->isValid([])) {
                $spl = new \SplFileInfo($request->file);

                $Contents = mb_convert_encoding(file_get_contents($spl->getPathname()), "utf-8", "sjis");
                $TargetDate = $request->TargetDate;

                //TODO:ひとまずここまで実行させる
                return response()->json([
                    "result" => true,
                ]);
                //アップロードされたファイルを読み込み、内容を返す。
                return $this->GetResult($Contents, $TargetDate);
            } else {
                return response()->json([
                    'result' => false,
                    "message" => '不正なデータです。',
                ]);
            }
        } catch (Exception $ex) {
            return response()->json([
                'result' => false,
                "message" => '取込に失敗しました。',
            ]);
        }
    }
    /**
     * GetResult
     */
    public function GetResult($Contents, $TargetDate)
    {
        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';
        $pdo = new PDO($dsn, $user, $password);

        try {
            $Lines = explode("\r\n", $Contents);

            $Company = (object)[];
            $Customers = [];
            foreach ($Lines as $Line) {
                switch (mb_substr($Line, 0, 1)) {
                    case "1":
                        $Company->委託者 = mb_substr($Line, 4, 10);
                        $Company->委託者名 = mb_substr($Line, 14, 40);
                        $Company->引落日 = mb_substr($Line, 54, 4);
                        $Company->銀行CD = mb_substr($Line, 58, 4);
                        $Company->銀行名 = mb_substr($Line, 62, 15);
                        $Company->支店CD = mb_substr($Line, 77, 3);
                        $Company->支店名 = mb_substr($Line, 80, 15);
                        $Company->口座番号 = mb_substr($Line, 95, 7);
                        break;
                    case "2":
                        $Customer = (object)[];
                        $Customer->金融機関CD = mb_substr($Line, 1, 4);
                        $Customer->金融機関支店CD = mb_substr($Line, 20, 3);
                        $Customer->口座番号 = mb_substr($Line, 43, 7);
                        $Customer->引落金額 = mb_substr($Line, 80, 10);
                        $Customer->得意先CD = mb_substr($Line, 101, 10);

                        $stmt = $pdo->query("
                            SELECT
                                得意先マスタ.得意先名略称,
                                得意先マスタ.部署CD,
                                部署マスタ.部署名
                            FROM
                                得意先マスタ
                                LEFT OUTER JOIN 部署マスタ
                                    ON 部署マスタ.部署CD = 得意先マスタ.部署CD
                            WHERE
                                得意先マスタ.得意先ＣＤ=$Customer->得意先CD
                        ");
                        $names = $stmt->fetch(PDO::FETCH_ASSOC);
                        $Customer->得意先名 = $names["得意先名略称"];
                        $Customer->部署CD = $names["部署CD"];
                        $Customer->部署名 = $names["部署名"];

                        $stmt = $pdo->query("
                            SELECT
                                B.銀行名,
                                S.支店名
                            FROM
                                金融機関名称 B
                                LEFT OUTER JOIN 金融機関支店名称 S
                                    ON  S.銀行CD = B.銀行CD
                                    AND S.支店CD = $Customer->金融機関支店CD
                            WHERE
                                B.銀行CD = $Customer->金融機関CD
                        ");
                        $bnames = $stmt->fetch(PDO::FETCH_ASSOC);
                        $Customer->金融機関名 = $bnames["銀行名"];
                        $Customer->金融機関支店名 = $bnames["支店名"];

                        $Customer->口座種別 = mb_substr($Line, 42, 1);
                        $stmt = $pdo->query("
                            SELECT
                                K.各種名称 AS 種別名
                            FROM
                                各種テーブル K
                            WHERE
                                K.各種CD = 7
                            AND K.行NO = $Customer->口座種別
                        ");
                        $knames = $stmt->fetch(PDO::FETCH_ASSOC);
                        $Customer->口座種別名 = $knames["種別名"];

                        $stmt = $pdo->query("
                            SELECT
                                ISNULL(現金, 0)
                                    + ISNULL(小切手, 0)
                                    + ISNULL(振込, 0)
                                    + ISNULL(バークレー, 0)
                                    + ISNULL(その他, 0)
                                    + ISNULL(相殺, 0)
                                    + ISNULL(値引, 0)
                                AS 入金額
                            FROM
                                入金データ
                            WHERE
                                得意先ＣＤ=$Customer->得意先CD
                            AND 入金日付 = '$TargetDate'
                        ");
                        $price = $stmt->fetch(PDO::FETCH_ASSOC);
                        $Customer->入金額 = $price["入金額"];

                        $err = mb_substr($Line, 111, 1);
                        $Customer->エラー = $err != " " && $err != "0" ? "エラー" : "";

                        $Customer->処理FLG = false;

                        array_push($Customers, $Customer);

                        break;
                }
            }

            return response()->json([
                'result' => true,
                'Contents' => $Contents,
                'Company' => $Company,
                'Customers' => $Customers,
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'result' => false,
                "message" => '取込に失敗しました。',
            ]);
        } finally {
            $pdo = null;
        }
    }

    /**
     * GetFurikomiList
     * 振替見出データを取得する
     */
    public function GetFurikomiList($request)
    {
        $BushoCd=$request->BushoCd;
        $StartDate=$request->StartDate;
        $EndDate=$request->EndDate;

        //TODO:引数は固定
        $StartDate='2020/05/01';
        $EndDate='2020/05/31';

        $sql = "
                    SELECT
                        0 AS No  ,     ----- 行番号
                        T1.ファイル名,
                        T1.ファイル日付 AS ファイル日時,
                        T1.処理日付,
                        M1.銀行名       AS 銀行,
                        M2.支店名       AS 本支店,
                        CASE WHEN 口座種別 = 1 THEN '普通'
                             WHEN 口座種別 = 2 THEN '当座'
                             WHEN 口座種別 = 3 THEN '定期'
                             WHEN 口座種別 = 9 THEN 'その他'
                        END AS 種別,
                        口座番号,
                        振込合計金額,
                        CASE WHEN T2.MEISAI_COUNT = T3.MEISAI_OK_COUNT THEN '◯'
                        ELSE '×'
                        END AS 結果,
                        '' AS 詳細      ----- 詳細ボタン
                    FROM
                        振込見出 T1
                    LEFT OUTER JOIN 金融機関名称     M1 ON (T1.金融機関ＣＤ = M1.銀行CD)
                    LEFT OUTER JOIN 金融機関支店名称 M2 ON (T1.金融機関ＣＤ = M2.銀行CD AND T1.金融機関支店ＣＤ = M2.支店CD)

                    LEFT OUTER JOIN (
                        SELECT 部署CD, ファイル名, COUNT(部署CD) AS MEISAI_COUNT
                        FROM 振込明細
                        WHERE 部署ＣＤ = $BushoCd
                        GROUP BY 部署CD, ファイル名
                    ) T2 ON (T1.部署CD = T2.部署CD AND T1.ファイル名 = T2.ファイル名)

                    LEFT OUTER JOIN (
                        SELECT 部署CD, ファイル名, COUNT(部署CD) AS MEISAI_OK_COUNT
                        FROM 振込明細
                        WHERE 部署ＣＤ = $BushoCd
                        AND 結果 = 1
                        GROUP BY 部署CD, ファイル名
                    ) T3 ON (T1.部署CD = T3.部署CD AND T1.ファイル名 = T3.ファイル名)

                    WHERE
                        T1.部署ＣＤ = $BushoCd
                    AND
                        T1.処理日付 BETWEEN '$StartDate' AND '$EndDate'
                    ORDER BY T1.ファイル日付 DESC
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
     * GetProductList
     * 3090から移植
     */
    public function GetProductList($request)
    {
        $sql = "
            SELECT
                PM.商品ＣＤ,
                PM.商品名,
                PM.商品区分,
                PM.売価単価
            FROM
                商品マスタ PM
            WHERE
                表示ＦＬＧ=0 AND 弁当区分 IN (0, 8, 9)
            ORDER BY 商品ＣＤ

        ";

        $Result = collect(DB::select($sql))
            ->map(function ($product) {
                $vm = (object) $product;

                $vm->Cd = $product->商品ＣＤ;
                $vm->CdNm = $product->商品名;

                return $vm;
            })
            ->values();

        return response()->json($Result);
    }

    /**
     * GetMobileProductList
     * 3090から移植
     */
    public function GetMobileProductList($request)
    {
        $sql = "
            SELECT
                T1.部署ＣＤ
                ,T1.商品ＣＤ
                ,T2.商品区分
                ,T2.商品名
                ,T1.主要商品FLG
                ,T1.期間限定FLG
                ,T1.販売期間開始
                ,T1.販売期間終了
                ,T1.修正日
            FROM
                モバイル_対象商品 T1
                LEFT OUTER JOIN 商品マスタ T2 ON
                    T1.商品ＣＤ = T2.商品ＣＤ
            ORDER BY
                T2.商品区分
                ,T2.商品ＣＤ
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
     * Save
     * 3090から移植
     */
    public function Save($request)
    {
        $params = $request->all();

        DB::beginTransaction();
        try {
            $saveList = $params['SaveList'];

            $AddList = $saveList['AddList'];
            $UpdateList = $saveList['UpdateList'];
            $OldList = $saveList['OldList'];
            $DeleteList = $saveList['DeleteList'];

            //DeleteList
            foreach ($DeleteList as $rec) {
                モバイル対象商品::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->delete();
            }

            //UpdateList
            foreach ($OldList as $index => $rec) {
                $data = $UpdateList[$index];

                モバイル対象商品::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->update($data);
            }

            //AddList
            foreach ($AddList as $rec) {
                $data = $rec;
                $cnt = モバイル対象商品::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('商品ＣＤ', $data['商品ＣＤ'])
                    ->count();

                if ($cnt == 0) {
                    モバイル対象商品::insert($data);
                } else {
                    モバイル対象商品::query()
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('商品ＣＤ', $rec['商品ＣＤ'])
                        ->update($data);
                }
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            "result" => true,
        ]);
    }

    /**
     * Delete
     * 3090から移植
     */
    public function Delete($request)
    {
        DB::beginTransaction();
        try {
            $params = $request->all();

            モバイル対象商品::query()
                ->where('部署ＣＤ', $params['部署ＣＤ'])
                ->where('商品ＣＤ', $params['商品ＣＤ'])
                ->delete();

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            "result" => true,
        ]);
    }
}
