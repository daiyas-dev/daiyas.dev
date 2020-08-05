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

class DAI01130Controller extends Controller
{

    /**
     * GetSeikyuDataList
     */
    public function GetSeikyuDataList($request)
    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;
        $CustomerCd = $request->CustomerCd;

        $sql = "
            SELECT
                SEIKYU.請求日付
                ,SEIKYU.回収予定日
                ,SEIKYU.今回売上額
                ,SEIKYU.今回入金額
                ,SEIKYU.今回請求額
                ,(
                    SEIKYU.今回請求額
                        - IIF(SEIKYU.請求日付 = FIRST_VALUE(SEIKYU.請求日付) OVER(ORDER BY SEIKYU.請求日付 DESC),
                            (
                                SELECT
                                    SUM(現金 + 小切手 + 振込 + バークレー + その他 + 相殺 + 値引) AS 入金額
                                FROM
                                    入金データ NYUKIN
                                WHERE
                                    NYUKIN.入金日付 >= SEIKYU.請求日付
                                AND NYUKIN.入金日付 >= '$TargetDate'
                                AND NYUKIN.得意先ＣＤ = $CustomerCd
                                GROUP BY
                                    NYUKIN.得意先ＣＤ
                            ),
                            0
                        )
                ) AS 未収残高
            FROM
                請求データ SEIKYU
            WHERE
                SEIKYU.部署ＣＤ = $BushoCd
            AND SEIKYU.請求日付 <= '$TargetDate'
            AND SEIKYU.請求先ＣＤ = $CustomerCd
            ORDER BY
                請求日付 DESC
        ";

        $DataList = DB::select($sql);
        return $DataList;
    }

    /**
     * GetDenpyoNoList
     */
    public function GetDenpyoNoList($vm)
    {
        $BushoCd = $vm->BushoCd;
        $CustomerCd = $vm->CustomerCd;

        $sql = "
            SELECT
                *
            FROM 	[入金データ]
            WHERE
                部署ＣＤ=$BushoCd
            AND	得意先ＣＤ = $CustomerCd
            ORDER BY
                入金日付 DESC
        ";

        $DataList = DB::select(DB::raw($sql));

        return $DataList;
    }

    /**
     * GetNyukinData
     */
    public function GetNyukinData($request)
    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;
        $CustomerCd = $request->CustomerCd;

        $sql = "
            SELECT
                *
                ,バークレー AS 振替
                ,その他 AS チケット入金
                ,相殺 AS 振込料
            FROM 	[入金データ]
            WHERE
                入金日付='$TargetDate'
            AND 部署ＣＤ=$BushoCd
            AND	得意先ＣＤ = $CustomerCd
            ORDER BY
                入金日付 DESC
        ";

        $Result = DB::select($sql);
        return $Result;
    }

    /**
     * CheckSeikyu
     */
    public function CheckSeikyu($request)
    {
        $TargetDate = $request->TargetDate;
        $CustomerCd = $request->CustomerCd;

        $sql = "
            SELECT
            	IIF('$TargetDate' <= 請求日付, 1, 0) AS 請求済
            FROM
                請求データ
            WHERE 請求データ.請求先ＣＤ = $CustomerCd
            AND 請求日付 = (
                SELECT MAX(請求日付) FROM 請求データ DUM
                WHERE DUM.請求先ＣＤ = $CustomerCd )
        ";

        $Result = DB::selectOne($sql);
        return !!$Result ? $Result->請求済 : 0;
    }

    /**
     * CheckKaikei
     */
    public function CheckKaikei($request)
    {
        $TargetDate = $request->TargetDate;
        $CustomerCd = $request->CustomerCd;

        $sql = "
            SELECT
                IIF('$TargetDate' <= EOMONTH(日付), 1, 0) AS 会計処理済
            FROM
                売掛データ
            WHERE 売掛データ.請求先ＣＤ = $CustomerCd
            AND 日付 = (
                SELECT MAX(日付) FROM 売掛データ DUM
                WHERE DUM.請求先ＣＤ = $CustomerCd )
        ";

        $Result = DB::selectOne($sql);
        return !!$Result ? $Result->会計処理済 : 0;
    }

    /**
     * GetData
     */
    public function GetData($request)
    {
        return [
            "SeikyuDataList" => $this->GetSeikyuDataList($request),
            "DenpyoNoList" => $this->GetDenpyoNoList($request),
            "NyukinData" => $this->GetNyukinData($request),
            "CheckSeikyu" => $this->CheckSeikyu($request),
            "CheckKaikei" => $this->CheckKaikei($request),
        ];
    }

    /**
     * Search
     */
    public function Search($request)
    {
        return response()->json($this->GetData($request));
    }

    /**
     * Save
     */
    public function Save($request)
    {
        $edited = false;
        DB::beginTransaction();

        $Target = $request->Target;

        try {
            $date = Carbon::now()->format('Y-m-d H:i:s');

            if (isset($Target['伝票Ｎｏ']) && !!$Target['伝票Ｎｏ']) {
                $r = 入金データ::query()
                    ->where('入金日付', $Target['入金日付'])
                    ->where('伝票Ｎｏ', $Target['伝票Ｎｏ'])
                    ->get();

                if (count($r) != 1) {
                    $edited = true;
                } else if ($Target['修正日'] != $r[0]->修正日) {
                    $edited = true;
                } else {
                    $Target['修正日'] = $date;

                    $Target['摘要'] = !!$Target['摘要'] ? $Target['摘要'] : "" ;
                    $Target['備考'] = !!$Target['備考'] ? $Target['備考'] : "" ;

                    入金データ::query()
                        ->where('入金日付', $Target['入金日付'])
                        ->where('伝票Ｎｏ', $Target['伝票Ｎｏ'])
                        ->update($Target);
                }
            } else {
                $no = 管理マスタ::query()
                    ->where('管理ＫＥＹ', 1)
                    ->max('入金伝票Ｎｏ') + 1;

                管理マスタ::query()
                    ->where('管理ＫＥＹ', 1)
                    ->update(['入金伝票Ｎｏ' => $no]);

                $Target['伝票Ｎｏ'] = $no;
                $Target['修正日'] = $date;
                $Target['摘要'] = !!$Target['摘要'] ? $Target['摘要'] : "" ;
                $Target['備考'] = !!$Target['備考'] ? $Target['備考'] : "" ;

                入金データ::insert($Target);
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            "edited" => $edited,
            "current" => $this->GetData($request),
        ]);
    }

    /**
     * Delete
     */
    public function Delete($request)
    {
        $edited = false;
        DB::beginTransaction();

        $Target = $request->Target;

        try {
            if (isset($Target['伝票Ｎｏ']) && !!$Target['伝票Ｎｏ']) {
                $r = 入金データ::query()
                    ->where('入金日付', $Target['入金日付'])
                    ->where('伝票Ｎｏ', $Target['伝票Ｎｏ'])
                    ->get();

                if (count($r) != 1) {
                    $edited = true;
                } else if ($Target['修正日'] != $r[0]->修正日) {
                    $edited = true;
                } else {
                    入金データ::query()
                        ->where('入金日付', $Target['入金日付'])
                        ->where('伝票Ｎｏ', $Target['伝票Ｎｏ'])
                        ->delete();

                    DB::commit();
                }
            }
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            "edited" => $edited,
            "current" => $this->GetData($request),
        ]);
    }
}
