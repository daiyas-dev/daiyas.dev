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

                $Contents = file_get_contents($spl->getPathname());//文字コードはShiftJIS

                //アップロードされたファイルを読み込み、内容を返す。
                return $this->GetResult($Contents);
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
    public function GetResult($Contents)
    {
        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';
        $pdo = new PDO($dsn, $user, $password);

        try {
            $Lines = explode("\r\n", $Contents);

            $Company = (object)[];
            $Customers = [];
            $Summary = (object)[];
            foreach ($Lines as $Line) {
                switch (substr($Line, 0, 1)) {
                    case "1":
                        $Company->店番 = $this->getResultElement($Line, 1, 3);
                        $Company->取引店 = $this->getResultElement($Line, 4, 30);
                        $Company->全銀科目コード = $this->getResultElement($Line, 34, 1);
                        $Company->預金種類コード = $this->getResultElement($Line, 35, 1);
                        $Company->預金種類科目 = $this->getResultElement($Line, 36, 16);
                        $Company->口座番号 = $this->getResultElement($Line, 52, 7);
                        $Company->口座名義 = $this->getResultElement($Line, 59, 142);
                        $Company->照会期間 = $this->getResultElement($Line, 201, 21);
                        $Company->照会方法 = $this->getResultElement($Line, 222, 12);
                        $Company->操作日 = $this->getResultElement($Line, 234, 10);
                        $Company->操作時刻= $this->getResultElement($Line, 244, 5);
                        break;
                    case "2":
                        $Customer = (object)[];
                        $Customer->取引日 = $this->getResultElement($Line, 1, 10);
                        $Customer->指定日 = $this->getResultElement($Line, 11, 10);
                        $Customer->取引区分 = $this->getResultElement($Line, 21, 10);
                        $Customer->依頼人名 = $this->getResultElement($Line, 31, 96);
                        $Customer->金融機関名 = $this->getResultElement($Line, 127, 30);
                        $Customer->支店名 = $this->getResultElement($Line, 157, 30);
                        $Customer->EDI情報 = $this->getResultElement($Line, 187, 20);
                        $Customer->入金金額 = $this->getResultElement($Line, 207, 13);

                        $stmt = $pdo->query("
                            SELECT DISTINCT M1.得意先ＣＤ,
                                            M1.振込依頼人名,
                                            M2.得意先名,
                                            M2.集金手数料
                            FROM 得意先振込依頼人名 M1,
                                得意先マスタ       M2
                            WHERE M1.得意先ＣＤ = M2.得意先ＣＤ
                              AND M1.振込依頼人名='$Customer->依頼人名'
                        ");
                        $furikomiirainin = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if(0<count($furikomiirainin))
                        {
                            $Customer->得意先ＣＤ = $furikomiirainin[0]['得意先ＣＤ'];
                            $Customer->得意先名 = $furikomiirainin[0]['得意先名'];
                            $Customer->集金手数料 = $furikomiirainin[0]['集金手数料'];
                            $Customer->結果 = count($furikomiirainin) != 1 ? "◎":"◯";
                        }
                        else{
                            $Customer->得意先ＣＤ = "";
                            $Customer->得意先名 = "";
                            $Customer->集金手数料 = "";
                            $Customer->結果 = "";
                        }
                        array_push($Customers, $Customer);

                        break;
                    case "9":
                        $Summary->出力明細数 = $this->getResultElement($Line, 1, 4);
                        //$Summary->ブランク = substr($Line, 5, 211);
                        $Summary->合計金額 = $this->getResultElement($Line, 216, 14);
                        break;
                }
            }

            return response()->json([
                'result' => true,
                'Contents' => mb_convert_encoding($Contents , "utf-8", "sjis"),
                'Company' => $Company,
                'Customers' => $Customers,
                'Summary' => $Summary,
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
     * getResultElement
     * ファイルの要素(S-JIS)を読み取って、UTF-8に変換して戻す
     */
    private function getResultElement($data,$pos,$len)
    {
        $str=trim(substr($data, $pos, $len));
        return mb_convert_encoding($str , "utf-8", "sjis");
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
}
