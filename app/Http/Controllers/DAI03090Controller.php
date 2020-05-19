<?php

namespace App\Http\Controllers;

use App\Models\モバイル対象商品;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use PDO;

class DAI03090Controller extends Controller
{
    /**
     * UploadFile
     */
    public function UploadFile($request)
    {
        $this->validate($request, [
            'file' => [
                'required',
                'file',
            ]
        ]);

        $TargetDate = $request->TargetDate;

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';
        $pdo = new PDO($dsn, $user, $password);

        try {
            if ($request->file('file')->isValid([])) {
                $spl = new \SplFileInfo($request->file);
                $data = file_get_contents($spl->getPathname());
                $lines = explode("\r\n", mb_convert_encoding($data, "utf-8", "sjis"));

                $company = (object)[];
                $customers = [];
                foreach ($lines as $idx => $line) {
                    switch (mb_substr($line, 0, 1)) {
                        case "1":
                            $company->委託者 = mb_substr($line, 4, 10);
                            $company->委託者名 = mb_substr($line, 14, 40);
                            $company->引落日 = mb_substr($line, 54, 4);
                            $company->銀行CD = mb_substr($line, 58, 4);
                            $company->銀行名 = mb_substr($line, 62, 15);
                            $company->支店CD = mb_substr($line, 77, 3);
                            $company->支店名 = mb_substr($line, 80, 15);
                            $company->口座番号 = mb_substr($line, 95, 7);
                            break;
                        case "2":
                            $customer = (object)[];
                            $customer->金融機関CD = mb_substr($line, 1, 4);
                            $customer->金融機関支店CD = mb_substr($line, 20, 3);
                            $customer->口座番号 = mb_substr($line, 43, 7);
                            $customer->引落金額 = mb_substr($line, 80, 10);
                            $customer->得意先CD = mb_substr($line, 101, 10);

                            $stmt = $pdo->query("
                                SELECT
                                    得意先マスタ.得意先名,
                                    得意先マスタ.部署CD,
                                    部署マスタ.部署名
                                FROM
                                    得意先マスタ
                                    LEFT OUTER JOIN 部署マスタ
                                        ON 部署マスタ.部署CD = 得意先マスタ.部署CD
                                WHERE
                                    得意先マスタ.得意先ＣＤ=$customer->得意先CD
                            ");
                            $names = $stmt->fetch(PDO::FETCH_ASSOC);
                            $customer->得意先名 = $names["得意先名"];
                            $customer->部署CD = $names["部署CD"];
                            $customer->部署名 = $names["部署名"];

                            $stmt = $pdo->query("
                                SELECT
                                    B.銀行名,
                                    S.支店名
                                FROM
                                    金融機関名称 B
                                    LEFT OUTER JOIN 金融機関支店名称 S
                                        ON S.銀行CD = B.銀行CD
                                WHERE
                                    B.銀行CD = $customer->金融機関CD
                                AND S.支店CD = $customer->金融機関支店CD
                            ");
                            $bnames = $stmt->fetch(PDO::FETCH_ASSOC);
                            $customer->金融機関名 = $bnames["銀行名"];
                            $customer->金融機関支店名 = $bnames["支店名"];

                            $customer->口座種別 = mb_substr($line, 42, 1);
                            $stmt = $pdo->query("
                                SELECT
									K.各種名称 AS 種別名
                                FROM
                                    各種テーブル K
                                WHERE
                                    K.各種CD = 7
								AND K.行NO = $customer->口座種別
                            ");
                            $knames = $stmt->fetch(PDO::FETCH_ASSOC);
                            $customer->口座種別名 = $knames["種別名"];

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
                                    得意先ＣＤ=$customer->得意先CD
                                AND 入金日付 = '$TargetDate'
                            ");
                            $price = $stmt->fetch(PDO::FETCH_ASSOC);
                            $customer->入金額 = $price["入金額"];

                            $err = mb_substr($line, 111, 1);
                            $customer->エラー = $err != " " && $err != "0" ? "エラー" : "";

                            $customer->処理FLG = false;

                            array_push($customers, $customer);

                            break;
                    }
                }

                return response()->json([
                    'result' => true,
                    'lines' => $lines,
                    'company' => $company,
                    'customers' => $customers,
                ]);
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
        } finally {
            $pdo = null;
        }
    }

    /**
     * GetProductList
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
