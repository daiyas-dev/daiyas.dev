<?php

namespace App\Http\Controllers;

use App\Models\モバイル対象商品;
use App\Models\入金データ;
use App\Models\管理マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
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

        try {
            if ($request->file('file')->isValid([])) {
                $spl = new \SplFileInfo($request->file);

                $Contents = mb_convert_encoding(file_get_contents($spl->getPathname()), "utf-8", "sjis");
                $TargetDate = $request->TargetDate;

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
                'exception' => $ex->getMessage(),
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
                        $Customer->口座種別名 = isset($knames) ? $knames["種別名"] : "";

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
                        $Customer->入金額 = isset($price["入金額"]) ? $price["入金額"] : 0;

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
                "exception" => $ex,
            ]);
        } finally {
            $pdo = null;
        }
    }

    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();
        $SaveList = $params['SaveList'];
        $Contents = $request->Contents;
        $TargetDate = $request->TargetDate;

        DB::beginTransaction();
        try {
            foreach ($SaveList as $rec) {
                $exists = 入金データ::query()
                    ->where('入金日付', $rec['入金日付'])
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                    ->where('入金区分', $rec['入金区分'])
                    ->get();

                if (count($exists) == 0) {
                    $no = 管理マスタ::query()
                        ->where('管理ＫＥＹ', 1)
                        ->max('入金伝票Ｎｏ') + 1;

                    管理マスタ::query()
                        ->where('管理ＫＥＹ', 1)
                        ->update(['入金伝票Ｎｏ' => $no]);

                    $rec['伝票Ｎｏ'] = $no;
                    入金データ::insert($rec);
                } else {
                    $rec["伝票Ｎｏ"] = $exists[0]->伝票Ｎｏ;

                    入金データ::query()
                        ->where('入金日付', $rec['入金日付'])
                        ->where('伝票Ｎｏ', $rec['伝票Ｎｏ'])
                        ->update($rec);
                }
            }

            DB::commit();

            //更新後内容返却
            return $this->GetResult($Contents, $TargetDate);
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
