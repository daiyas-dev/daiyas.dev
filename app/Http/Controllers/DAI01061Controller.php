<?php

namespace App\Http\Controllers;

use App\Models\コーステーブル;
use App\Models\各種テーブル;
use App\Models\商品マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Facades\DB as IlluminateDB;

class DAI01061Controller extends Controller
{
    /**
     * GetSendList
     */
    public function GetSendList($request)
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
	MOBILE.相手確認フラグ
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
        return response()->json($DataList);
    }
    /**
     * GetReceiveList
     */
    public function GetReceiveList($request)
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
	MOBILE.相手確認フラグ
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
        return response()->json($DataList);
    }

    /**
     * GetTargetProducts
     */
    public function GetTargetProducts($request)
    {
        $sql = "
SELECT
    CONVERT(int,KAKU.サブ各種CD1) AS 商品ＣＤ
    ,KAKU.各種略称 AS 商品名
    ,KAKU.行NO AS ソート順
FROM
    各種テーブル KAKU
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
        $params = $request->all();

        // トランザクション開始
        // DB::transaction(function () use ($params) {
        //     $IsSend = $params['IsSend'];

        //     $AddList = $params['AddList'];
        //     $UpdateList = $params['UpdateList'];
        //     $DeleteList = $params['DeleteList'];

        //     $model = new 担当者マスタ();
        //     $model->fill($params);

        //     $data = collect($model)->all();


        //     $TantoCd = $params['担当者ＣＤ'];

        //     DB::table('担当者マスタ')->updateOrInsert(
        //         ['担当者ＣＤ' => $TantoCd],
        //         $data
        //     );
        // });


        $sql = "
SELECT 1
        ";

        $Result = DB::selectOne($sql);

        return response()->json($Result);
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
            $Result->list = $IsSend ? $this->GetSendList($request) : $this->GetReceiveListList($request);
            return response()->json($Result);
        } else {
            return response()->json($Result);
        }
    }
}
