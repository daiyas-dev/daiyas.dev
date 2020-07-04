<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use App\Models\モバイル移動入力;
use Carbon\Carbon;
use DB;

class DAI01061Controller extends Controller
{
    /**
     * SearchSendList
     */
    public function SearchSendList($request)
    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;
        $CourseKbn = $request->CourseKbn;
        $CourseCd = $request->CourseCd;

        $sql = "
SELECT
    MOBILE.部署ＣＤ,
	MOBILE.コースＣＤ,
	ME.コース名,
	MOBILE.相手コースＣＤ,
	BUDDY.コース名 AS 相手コース名,
	MOBILE.日付 ,
	MOBILE.ＳＥＱ,
	MOBILE.商品ＣＤ,
	商品マスタ.商品名,
	商品マスタ.商品区分,
	MOBILE.個数,
	MOBILE.確認フラグ,
    MOBILE.相手確認フラグ,
    MOBILE.修正日
FROM
	[モバイル_移動入力] MOBILE
	LEFT JOIN コースマスタ ME
		ON ME.部署ＣＤ=MOBILE.部署ＣＤ AND ME.コースＣＤ=MOBILE.コースＣＤ AND ME.コース区分=$CourseKbn
	LEFT JOIN コースマスタ BUDDY
		ON BUDDY.部署ＣＤ=MOBILE.部署ＣＤ AND BUDDY.コースＣＤ=MOBILE.相手コースＣＤ AND BUDDY.コース区分=$CourseKbn
	LEFT JOIN 商品マスタ
		ON 商品マスタ.商品ＣＤ = MOBILE.商品ＣＤ
WHERE
	MOBILE.コースＣＤ = $CourseCd AND
	MOBILE.部署ＣＤ = $BushoCd AND
	MOBILE.日付 = '$TargetDate'
order by
	MOBILE.コースＣＤ,
	MOBILE.ＳＥＱ
        ";

        $DataList = DB::select($sql);
        return $DataList;
    }
    /**
     * GetSendList
     */
    public function GetSendList($request) {
        return response()->json($this->SearchSendList($request));
    }

    /**
     * SearchReceiveList
     */
    public function SearchReceiveList($request)
    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;
        $CourseKbn = $request->CourseKbn;
        $CourseCd = $request->CourseCd;

        $sql = "
SELECT
    MOBILE.部署ＣＤ,
	MOBILE.コースＣＤ AS 相手コースＣＤ,
	ME.コース名 AS 相手コース名,
	MOBILE.相手コースＣＤ AS コースＣＤ,
	BUDDY.コース名,
	MOBILE.日付 ,
	MOBILE.ＳＥＱ,
	MOBILE.商品ＣＤ,
	商品マスタ.商品名,
	商品マスタ.商品区分,
	MOBILE.個数,
	MOBILE.確認フラグ,
    MOBILE.相手確認フラグ,
    MOBILE.修正日
FROM
	[モバイル_移動入力] MOBILE
	LEFT JOIN コースマスタ ME
		ON ME.部署ＣＤ=MOBILE.部署ＣＤ AND ME.コースＣＤ=MOBILE.コースＣＤ AND ME.コース区分=$CourseKbn
	LEFT JOIN コースマスタ BUDDY
		ON BUDDY.部署ＣＤ=MOBILE.部署ＣＤ AND BUDDY.コースＣＤ=MOBILE.相手コースＣＤ AND BUDDY.コース区分=$CourseKbn
	LEFT JOIN 商品マスタ
		ON 商品マスタ.商品ＣＤ = MOBILE.商品ＣＤ
WHERE
	MOBILE.相手コースＣＤ = $CourseCd AND
	MOBILE.部署ＣＤ = $BushoCd AND
	MOBILE.日付 = '$TargetDate'
order by
	MOBILE.コースＣＤ,
	MOBILE.ＳＥＱ
        ";

        $DataList = DB::select($sql);
        return $DataList;
    }
    /**
     * GetReceiveList
     */
    public function GetReceiveList($request)
    {
        return response()->json($this->SearchReceiveList($request));
    }

    /**
     * GetTargetProducts
     */
    public function GetTargetProducts($request)
    {
        $sql = "
SELECT
	CONVERT(int,KAKU.サブ各種CD1) AS 商品ＣＤ
	,商品マスタ.商品名 AS 商品名
	,KAKU.行NO AS ソート順
FROM
	各種テーブル KAKU
	LEFT JOIN 商品マスタ
		ON 商品マスタ.商品ＣＤ = KAKU.サブ各種CD1
WHERE
	KAKU.各種CD = 33
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
     * Save
     */
    public function Save($request)
    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;
        $CourseCd = $request->CourseCd;

        $skip = [];
        $params = $request->all();

        // トランザクション開始
        $skip = DB::transaction(function () use ($request, $skip, $params) {

            $AddList = $params['AddList'];
            $UpdateList = $params['UpdateList'];
            $OldList = $params['OldList'];
            $DeleteList = $params['DeleteList'];

            $date = Carbon::now()->format('Y-m-d H:i:s');

            //DeleteList
            foreach($DeleteList as $rec) {
                $r = モバイル移動入力::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('コースＣＤ', $rec['コースＣＤ'])
                    ->where('相手コースＣＤ', $rec['相手コースＣＤ'])
                    ->where('日付', $rec['日付'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->get();

                if (count($r) != 1) {
                    $skip = collect($skip)->push(["target" => $rec, "current" => null]);
                    continue;
                } else if ($rec['修正日'] != $r[0]->修正日) {
                    $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                    continue;
                }

                モバイル移動入力::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('コースＣＤ', $rec['コースＣＤ'])
                    ->where('相手コースＣＤ', $rec['相手コースＣＤ'])
                    ->where('日付', $rec['日付'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->delete();
            }

            //UpdateList
            foreach ($OldList as $index => $rec) {
                $r = モバイル移動入力::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('コースＣＤ', $rec['コースＣＤ'])
                    ->where('相手コースＣＤ', $rec['相手コースＣＤ'])
                    ->where('日付', $rec['日付'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->get();

                if (count($r) != 1) {
                    $skip = collect($skip)->push(["target" => $rec, "current" => null]);
                    continue;
                } else if ($rec['修正日'] != $r[0]->修正日) {
                    $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                    continue;
                }

                $data = $UpdateList[$index];
                $data['修正日'] = $date;

                モバイル移動入力::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('コースＣＤ', $rec['コースＣＤ'])
                    ->where('相手コースＣＤ', $rec['相手コースＣＤ'])
                    ->where('日付', $rec['日付'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->update($data);
            }

            //AddList
            foreach ($AddList as $rec) {
                $r = モバイル移動入力::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('コースＣＤ', $rec['コースＣＤ'])
                    ->where('相手コースＣＤ', $rec['相手コースＣＤ'])
                    ->where('日付', $rec['日付'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->get();

                if (count($r) != 0) {
                    $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                    continue;
                }

                $seq = モバイル移動入力::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('コースＣＤ', $rec['コースＣＤ'])
                    ->where('日付', $rec['日付'])
                    ->max('ＳＥＱ') + 1;

                $data = $rec;
                $data['修正日'] = $date;
                $data['ＳＥＱ'] = $seq;

                モバイル移動入力::insert($data);
            }

            return $skip;
        });

        //モバイルsv更新
        $Message = $params['Message'];
        $ds = new DataSendWrapper();
        $ds->UpdateMovementInputData($BushoCd, $CourseCd,$TargetDate, $Message);

        $send = null;
        $receive = null;
        if (!!count($skip)) {
            $send = $this->SearchSendList($request);
            $receive = $this->SearchReceiveList($request);
        }

        return response()->json([
            "result" => true,
            "skip" => !!count($skip),
            "send" => $send,
            "receive" => $receive
        ]);
    }

    /**
     * UpdateCheck
     */
    public function UpdateCheck($request)
    {
        $BushoCd = $request->BushoCd;
        $TargetDate = $request->TargetDate;
        $UpdateDate = $request->UpdateDate;
        $IsSend = $request->IsSend;

        $sql = "
SELECT
    MAX(修正日) AS 最新修正日時
FROM モバイル_移動入力
WHERE
    部署ＣＤ= $BushoCd
AND	日付 = '$TargetDate'
        ";

        $Result = DB::selectOne($sql);

        if (!!$UpdateDate && $Result->最新修正日時 != $UpdateDate) {
            $Result->list = $IsSend ? $this->SearchSendList($request) : $this->SearchReceiveListList($request);
            return response()->json($Result);
        } else {
            return response()->json($Result);
        }
    }
}
