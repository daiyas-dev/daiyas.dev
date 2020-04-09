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

class DAI01140Controller extends Controller
{

    /**
     * Search
     */
    public function Search($vm)
    {
        return response()->json($this->GetNyukinList($vm));
    }

    /**
     * GetNyukinList
     */
    public function GetNyukinList($vm)
    {
        $DateStart = $vm->DateStart;
        $DateEnd = $vm->DateEnd;

        $BushoCd = $vm->BushoCd;
        $WehreBushoCd = !!$BushoCd ? " AND 得意先マスタ.部署ＣＤ = $BushoCd" : "";

        $CourseCd = $vm->CourseCd;
        $WehreCourseCd = !!$CourseCd
            ? " AND COUM.コースＣＤ = $CourseCd"
            : " AND
                    (
                        COUM.コース区分 = (
                            SELECT MIN(DMY_COUM.コース区分)
                            FROM コースマスタ DMY_COUM
                                LEFT OUTER JOIN コーステーブル DMY_COU
                                    ON  DMY_COUM.部署ＣＤ = DMY_COU.部署ＣＤ
                                    AND DMY_COUM.コースＣＤ = DMY_COU.コースＣＤ
                            WHERE  DMY_COUM.部署ＣＤ = COUM.部署ＣＤ
                            AND DMY_COU.得意先ＣＤ = 得意先マスタ.受注得意先ＣＤ
                            AND DMY_COUM.コース区分 IN (1,2,3,4)
                        )
                        OR
                        COUT.コースＣＤ IS NULL
                    )
            ";

        $CustomerCd = $vm->CustomerCd;
        $WehreCustomerCd = !!$CustomerCd ? " AND 得意先マスタ.得意先ＣＤ = $CustomerCd" : " AND 得意先マスタ.請求先ＣＤ != 0";

        $sql = "
            SELECT
                入金日付,
                伝票Ｎｏ,
                入金データ.得意先ＣＤ,
                得意先マスタ.得意先名略称 AS 得意先名,
                入金区分,
                現金,
                小切手,
                振込,
                バークレー,
                その他,
                相殺,
                値引,
            	(摘要 + 入金データ.備考) AS 備考,
                COUT.コースＣＤ,
                COUM.コース名
            FROM
                [入金データ]
            INNER JOIN [得意先マスタ]
                ON 得意先マスタ.得意先ＣＤ = 入金データ.得意先ＣＤ
            LEFT OUTER JOIN [コーステーブル] COUT
                ON COUT.得意先ＣＤ = 得意先マスタ.受注得意先ＣＤ
                AND 得意先マスタ.部署ＣＤ = COUT.部署ＣＤ
            LEFT OUTER JOIN [コースマスタ] COUM
                ON COUM.コースＣＤ = COUT.コースＣＤ
                AND 得意先マスタ.部署ＣＤ = COUM.部署ＣＤ
                AND COUM.コース区分 IN (1, 2, 3, 4)
            WHERE
                入金日付 >= '$DateStart' AND 入金日付 <= '$DateEnd'
                $WehreBushoCd
                $WehreCourseCd
                $WehreCustomerCd
            ORDER BY
                入金日付,
                伝票Ｎｏ
        ";

        // $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        // $user = 'daiyas';
        // $password = 'daiyas';

        // $pdo = new PDO($dsn, $user, $password);
        // $stmt = $pdo->query($sql);
        // $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // $pdo = null;

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
            "edited" => $this->GetNyukinList($request),
        ]);
    }
}
