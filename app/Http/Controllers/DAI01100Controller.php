<?php

namespace App\Http\Controllers;

use App\Models\入金データ;
use App\Models\管理マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;

class DAI01100Controller extends Controller
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
	PM.商品区分,
	IIF(MTT.商品ＣＤ IS NOT NULL, MTT.単価, PM.売価単価) AS 売価単価
FROM
	商品マスタ PM
	LEFT JOIN 得意先単価マスタ MTT
		ON	PM.商品ＣＤ=MTT.商品ＣＤ
		AND MTT.得意先ＣＤ=$CustomerCd
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
        $DeliveryDate = $vm->DeliveryDate;
        $CourseCd = $vm->CourseCd;

        $sql = "
            SELECT
                コーステーブル.コースＣＤ,
                コースマスタ.コース名,
                コーステーブル.ＳＥＱ,
                コーステーブル.得意先ＣＤ,
                得意先マスタ.得意先名略称 AS 得意先名,
                得意先マスタ.受注得意先ＣＤ,
                支払方法.行NO AS 売掛現金区分,
                支払方法.各種名称 AS 売掛現金区分名称,
                得意先マスタ.売掛現金区分 AS 得意先売掛現金区分,
                得意先売掛現金区分.各種名称 AS 得意先売掛現金区分名称,
                得意先マスタ.支払方法１ AS 得意先支払方法,
                得意先支払方法.各種名称 AS 得意先支払方法名称,
                売上データ明細.日付,
                売上データ明細.行Ｎｏ,
                売上データ明細.明細行Ｎｏ,
                売上データ明細.商品ＣＤ,
                売上データ明細.現金個数,
                売上データ明細.現金金額,
                売上データ明細.現金値引,
                売上データ明細.現金値引事由ＣＤ,
                売上データ明細.掛売個数,
                売上データ明細.掛売金額,
                売上データ明細.掛売値引,
                売上データ明細.掛売値引事由ＣＤ,
                売上データ明細.食事区分,
                売上データ明細.売掛現金区分 AS 売上売掛現金区分,
                売上支払方法.各種名称 AS 売上売掛現金区分名称,
                商品マスタ.商品区分,
                商品マスタ.商品名,
                売上データ明細.分配元数量,
                CASE WHEN 売上データ明細.食事区分 IS NULL OR 売上データ明細.食事区分 = 0 THEN 商品マスタ.食事区分 ELSE 売上データ明細.食事区分 END 食事区分,
                CASE WHEN 売上データ明細.主食ＣＤ = 0 THEN 商品マスタ.主食ＣＤ ELSE 売上データ明細.主食ＣＤ END 主食ＣＤ,
                CASE WHEN 売上データ明細.副食ＣＤ = 0 THEN 商品マスタ.副食ＣＤ ELSE 売上データ明細.副食ＣＤ END 副食ＣＤ,
                入金データ.入金日付,
                入金データ.伝票Ｎｏ,
                入金データ.現金 AS 入金額,
                入金データ.摘要 AS 備考,
                入金データ.備考 AS 備考テキスト,
                入金データ.請求日付,
                入金データ.修正日 AS 入金データ修正日,
                分配先.分配先件数
            FROM
                [コーステーブル]
                    LEFT JOIN 各種テーブル 支払方法
                        ON 支払方法.各種CD=1
                    INNER JOIN [コースマスタ]
                        ON コースマスタ.コースＣＤ = コーステーブル.コースＣＤ
                        AND コースマスタ.部署ＣＤ = コーステーブル.部署ＣＤ
                    INNER JOIN [得意先マスタ]
                        ON 得意先マスタ.得意先ＣＤ = コーステーブル.得意先ＣＤ
                        AND 得意先マスタ.部署ＣＤ = コーステーブル.部署ＣＤ
                    LEFT JOIN 各種テーブル 得意先売掛現金区分
                        ON 得意先売掛現金区分.各種CD=1
                        AND 得意先売掛現金区分.行NO = 得意先マスタ.売掛現金区分
                    LEFT JOIN 各種テーブル 得意先支払方法
                        ON 得意先支払方法.各種CD=6
                        AND 得意先支払方法.行NO = 得意先マスタ.支払方法１
                    LEFT OUTER JOIN [売上データ明細]
                        ON 売上データ明細.日付 = '$DeliveryDate'
                        AND 売上データ明細.部署ＣＤ = コーステーブル.部署ＣＤ
                        AND 売上データ明細.得意先ＣＤ = コーステーブル.得意先ＣＤ
                        AND (
                            (支払方法.行NO = 0 AND 売上データ明細.売掛現金区分 = 0)
                            OR
                            (支払方法.行NO != 0 AND 売上データ明細.売掛現金区分 != 0)
                        )
                    LEFT JOIN 各種テーブル 売上支払方法
                        ON 売上支払方法.各種CD=1
                        AND 売上支払方法.行NO = 売上データ明細.売掛現金区分
                    LEFT OUTER JOIN [商品マスタ]
                        ON 商品マスタ.商品ＣＤ = 売上データ明細.商品ＣＤ
                    LEFT OUTER JOIN [入金データ]
                        ON 入金データ.入金日付 = '$DeliveryDate'
                        AND 入金データ.得意先ＣＤ = コーステーブル.得意先ＣＤ
                        AND 入金データ.入金区分 = 1
                    LEFT OUTER JOIN (
                        SELECT
                            受注得意先ＣＤ,
                            COUNT(受注得意先ＣＤ) AS 分配先件数
                        FROM 得意先マスタ
                        WHERE 受注得意先ＣＤ != 得意先ＣＤ
                        GROUP BY
                            受注得意先ＣＤ
                    ) 分配先
                        ON 分配先.受注得意先ＣＤ = 得意先マスタ.得意先ＣＤ
            WHERE
                コーステーブル.コースＣＤ = $CourseCd
            AND コーステーブル.部署ＣＤ = $BushoCd
            ORDER BY
                コーステーブル.コースＣＤ,
                コーステーブル.ＳＥＱ,
                支払方法.行NO,
                売上データ明細.日付,
                売上データ明細.行Ｎｏ,
                売上データ明細.商品ＣＤ
        ";

        $DataList = DB::select(DB::raw($sql));

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
