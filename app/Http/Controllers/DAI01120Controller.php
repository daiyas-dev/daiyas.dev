<?php

namespace App\Http\Controllers;

use App\Models\入金データ;
use App\Models\管理マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use PDO;

class DAI01120Controller extends Controller
{

    /**
     *  ColumSerch
     */

    public function ColSearch($vm)
    {
        $BushoCd = $vm->BushoCd;
        $sql = "
            SELECT
                サブ各種CD1 AS 商品ＣＤ,
                各種名称 AS 商品名,
                各種略称 AS 商品略称
            FROM 各種テーブル
            WHERE 各種CD=
                (
                    SELECT
                        IIF(サブ各種CD2=1, 24, IIF(サブ各種CD2=2, 25, IIF(サブ各種CD2=3, 41 ,NULL)))
                    FROM 各種テーブル
                    WHERE 各種CD=26
                    AND サブ各種CD1=$BushoCd
                )
                AND サブ各種CD2=0
            ORDER BY 各種CD,行NO
        ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }

    /**
     * GetProductList
     */
    public function GetProductList($request)
    {
        $CustomerCd = $request->CustomerCd;

        $sql = "
SELECT
	PM.商品ＣＤ,
	PM.商品名,
	PM.商品区分
FROM
	商品マスタ PM
WHERE
     表示ＦＬＧ=0
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
     * Search
     */
    public function Search($vm)
    {
        return response()->json($this->GetSalesList($vm));
    }

    /**
     * GetSalesList
     */
    public function GetSalesList($vm)
    {
        $BushoCd = $vm->BushoCd;
        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;

        $CourseCd = $vm->CourseCd;
        $WehreCourseCd = !!$CourseCd ? " AND 売上データ明細.コースＣＤ = $CourseCd" : "";

        $CustomerCd = $vm->CustomerCd;
        $WehreCustomerCd = !!$CustomerCd ? " AND 得意先マスタ.得意先ＣＤ = $CustomerCd" : " AND 得意先マスタ.請求先ＣＤ != 0";

        $SeikyuCd = $vm->SeikyuCd;
        $WehreSeikyuCd = !!$SeikyuCd ? " AND 得意先マスタ.請求先ＣＤ = $SeikyuCd" : "";

        $JuchuCd = $vm->JuchuCd;
        $WehreJuchuCd = !!$JuchuCd ? " AND 得意先マスタ.受注得意先ＣＤ = $JuchuCd" : "";

        $sql = "
            SELECT
                売上データ明細.日付,
                売上データ明細.部署ＣＤ,
                売上データ明細.コースＣＤ,
                コースマスタ.コース名,
                コースマスタ.コース区分,
                売上データ明細.得意先ＣＤ,
                売上データ明細.行Ｎｏ,
                得意先マスタ.得意先名,
                売上データ明細.商品ＣＤ,
                商品マスタ.商品名,
                売上データ明細.商品区分,
                IIF(売上データ明細.売掛現金区分 = 0, 売上データ明細.現金個数, 売上データ明細.掛売個数) AS 個数,
                IIF(売上データ明細.売掛現金区分 = 0, 売上データ明細.現金金額, 売上データ明細.掛売金額) AS 金額,
                IIF(売上データ明細.売掛現金区分 = 0, 売上データ明細.現金値引, 売上データ明細.掛売値引) AS 値引,
                --売上データ明細.現金個数,
                --売上データ明細.現金金額,
                --売上データ明細.現金値引,
                --売上データ明細.掛売個数,
                --売上データ明細.掛売金額,
                --売上データ明細.掛売値引,
                予備金額１,
                売上データ明細.売掛現金区分,
                売上データ明細.食事区分,
                IIF(商品マスタ.主食ＣＤ != 売上データ明細.主食ＣＤ OR 商品マスタ.副食ＣＤ != 売上データ明細.副食ＣＤ, '1', '0') AS 代替品
            FROM
                [売上データ明細]
                INNER JOIN [得意先マスタ]
                    ON 得意先マスタ.得意先ＣＤ = 売上データ明細.得意先ＣＤ
                INNER JOIN [商品マスタ]
                    ON 商品マスタ.商品ＣＤ = 売上データ明細.商品ＣＤ
                LEFT JOIN [コースマスタ]
                    ON コースマスタ.部署ＣＤ = 売上データ明細.部署ＣＤ
                    AND コースマスタ.コースＣＤ = 売上データ明細.コースＣＤ
            WHERE
                (日付 >= '$DateStart' AND   日付 <= '$DateEnd')
            AND 売上データ明細.部署ＣＤ = $BushoCd
            $WehreCourseCd
            $WehreCustomerCd
            $WehreSeikyuCd
            $WehreJuchuCd
            ORDER BY
                日付,
                売上データ明細.部署ＣＤ,
                売上データ明細.コースＣＤ,
                売上データ明細.得意先ＣＤ,
                売上データ明細.商品ＣＤ
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        // $DataList = DB::select(DB::raw($sql));

        return $DataList;
    }

    /**
     * Save
     */
    public function Save($request)
    {
        $skip = [];

        DB::beginTransaction();

        try {
            $params = $request->all();

            $SaveList = $params['SaveList'];

            $date = Carbon::now()->format('Y-m-d H:i:s');
            foreach ($SaveList as $rec) {
                if (isset($rec['伝票Ｎｏ']) && !!$rec['伝票Ｎｏ']) {
                    $r = 入金データ::query()
                        ->where('入金日付', $rec['入金日付'])
                        ->where('伝票Ｎｏ', $rec['伝票Ｎｏ'])
                        ->get();

                    if (count($r) != 1) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => null]);
                        continue;
                    } else if ($rec['修正日'] != $r[0]->修正日) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                        continue;
                    }

                    //現金が0の場合は削除扱い
                    if (!isset($rec['現金']) && !isset($rec['現金'])) {
                        入金データ::query()
                            ->where('入金日付', $rec['入金日付'])
                            ->where('伝票Ｎｏ', $rec['伝票Ｎｏ'])
                            ->delete();
                    } else {
                        $rec['修正日'] = $date;

                        入金データ::query()
                            ->where('入金日付', $rec['入金日付'])
                            ->where('伝票Ｎｏ', $rec['伝票Ｎｏ'])
                            ->update($rec);
                    }
                } else {
                    $no = 管理マスタ::query()
                        ->where('管理ＫＥＹ', 1)
                        ->max('入金伝票Ｎｏ') + 1;

                    管理マスタ::query()
                        ->where('管理ＫＥＹ', 1)
                        ->update(['入金伝票Ｎｏ' => $no]);

                    $rec['伝票Ｎｏ'] = $no;
                    $rec['修正日'] = $date;

                    入金データ::insert($rec);
                }
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            "edited" => $this->GetSalesList($request),
        ]);
    }
}
