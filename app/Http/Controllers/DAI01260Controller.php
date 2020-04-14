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

class DAI01260Controller extends Controller
{

    /**
     * Search
     */
    public function Search($vm)
    {
        return response()->json($this->GetBunpaiList($vm));
    }

    /**
     * GetBunpaiList
     */
    public function GetBunpaiList($vm)
    {
        $BushoCd = $vm->BushoCd;
        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;

        $CourseCd = $vm->CourseCd;
        $WehreCourseCd = !!$CourseCd ? " AND T1.コースＣＤ = $CourseCd" : "";

        $sql = "
            WITH VW01 AS (
                SELECT
                    T1.部署ＣＤ AS 部署CD
                ,	T2.部署名
                ,	T1.コースＣＤ AS コースCD
                ,	T1.商品ＣＤ AS 商品CD
                ,	T3.コース名
                ,	T1.日付 AS 配送日
                ,	T4.得意先ＣＤ AS 分配元得意先CD
                ,	T4.得意先名 AS 分配元得意先名
                ,	T1.分配元数量
                FROM
                    売上データ明細 T1
                    LEFT JOIN 部署マスタ T2
                        ON	T1.部署CD = T2.部署CD
                    LEFT JOIN コースマスタ T3
                        ON	T1.部署ＣＤ = T3.部署ＣＤ AND T1.コースＣＤ = T3.コースＣＤ
                    LEFT JOIN 得意先マスタ T4
                        ON	T1.得意先ＣＤ = T4.得意先ＣＤ
                        AND (T4.受注得意先CD = 0 OR T4.受注得意先CD IS NULL)
                WHERE
                    T1.分配元数量 > 0
                    AND T1.部署CD = $BushoCd
                    AND T1.日付 >= '$DateStart'
                    AND T1.日付 <= '$DateEnd'
                    $WehreCourseCd
            ),
            VW02 AS (
                SELECT
                    T1.部署ＣＤ AS 部署CD
                ,   T1.日付 AS 配送日
                ,	T0.受注得意先CD
                ,	T1.コースＣＤ AS コースCD
                ,	T0.得意先ＣＤ AS 分配先得意先CD
                ,	T0.得意先名 AS 分配先得意先名
                ,	T1.商品ＣＤ AS 商品CD
                ,	T2.商品名
                ,	(T1.掛売個数 + T1.現金個数) AS 数量
                ,	(T1.掛売金額 + T1.現金金額) AS 売上金額
                ,	(T3.現金 + T3.小切手 + T3.振込 + T3.バークレー + T3.その他 + T3.相殺 + T3.値引) AS 入金額
                FROM
                    得意先マスタ T0
                    INNER JOIN 売上データ明細 T1
                        ON	T0.得意先ＣＤ = T1.得意先ＣＤ
                        AND	T0.得意先ＣＤ <> T0.受注得意先CD
                        AND T1.部署CD = $BushoCd
                        AND T1.日付 >= '$DateStart'
                        AND T1.日付 <= '$DateEnd'
                        $WehreCourseCd
                    LEFT JOIN 商品マスタ T2
                        ON T1.商品ＣＤ = T2.商品ＣＤ
                    LEFT JOIN 入金データ T3
                        ON	T3.部署ＣＤ = T1.部署ＣＤ
                        AND T3.得意先ＣＤ = T1.得意先ＣＤ
                        AND T3.入金日付 = T1.日付
            )
            SELECT
            	VW01.コースCD
            ,	VW01.コース名
            ,	CONVERT(VARCHAR,VW01.配送日,111) + '('
                    + CASE DATEPART(WEEKDAY, VW01.配送日)
                        WHEN 1 THEN '日'
                        WHEN 2 THEN '月'
                        WHEN 3 THEN '火'
                        WHEN 4 THEN '水'
                        WHEN 5 THEN '木'
                        WHEN 6 THEN '金'
                        WHEN 7 THEN '土'
                        END + ')' AS 配送日
            ,	VW01.分配元得意先CD
            ,	VW01.分配元得意先名
            ,	VW02.商品名
            ,	VW01.分配元数量
            ,	VW02.受注得意先CD
            ,	VW02.分配先得意先CD
            ,   VW02.分配先得意先名
            ,	ISNULL(VW02.数量, 0) AS 数量
            ,	ISNULL(VW02.売上金額, 0) AS 売上金額
            ,	ISNULL(VW02.入金額, 0) AS 入金額
            FROM
                VW01
                INNER JOIN VW02
                    ON	VW01.配送日 = VW02.配送日
                    AND VW01.部署CD = VW02.部署CD
                    AND VW01.コースCD = VW02.コースCD
                    AND VW01.商品CD = VW02.商品CD
                    AND VW01.分配元得意先CD = VW02.受注得意先CD
            ORDER BY
                VW01.部署CD
            ,   VW01.コースCD
            ,   VW01.配送日
            ,   VW01.分配元得意先CD
            ,   VW02.分配先得意先CD
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
            "edited" => $this->GetBunpaiList($request),
        ]);
    }
}
