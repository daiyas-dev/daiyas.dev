<?php

namespace App\Http\Controllers;

use App\Events\PrivateEvent;
use App\Events\PublicEvent;
use App\Models\コースマスタ;
use App\Models\商品マスタ;
use App\Models\担当者マスタ;
use App\Models\部署マスタ;
use Illuminate\Http\Request;
use DB;

class UtilitiesController extends Controller
{
    //TODO: 各種テーブルに変更
    /**
     * GetCodeList
     */
    public function GetCodeList($request)
    {
        $course = $request->code;

        $CodeList = collect(DB::table('codes')->where('cd', $course)->get())
            ->map(function ($course) {
                $vm = (object) $course;

                $vm->Cd = $course->ord;
                $vm->CdNm = $course->name;
                $vm->Abb = $course->abb;
                $vm->Sub1 = $course->sub1;
                $vm->Sub2 = $course->sub2;
                $vm->user_id = $course->user_id;

                return $vm;
            })
            ->values();

        return response()->json($CodeList);
    }

    /**
     * GetUserList
     */
    public function GetUserList($request)
    {
        $busho = $request->busho;

        $query = 担当者マスタ::with(['部署'])
            ->when(
                $busho,
                function ($q) use ($busho) {
                    return $q->where('所属部署ＣＤ', $busho);
                }
            )
            ->where('ユーザーＩＤ', '>', 0);

        $UserList = collect($query->get())
            ->map(function ($user) {
                $vm = (object) $user;

                $vm->Cd = $user->ユーザーＩＤ;
                $vm->CdNm = $user->担当者名;

                //password
                $vm->パスワード = '';

                return $vm;
            })
            ->values();

        return response()->json($UserList);
    }

    /**
     * GetBushoList
     */
    public function GetBushoList($request)
    {
        $group = $request->group;

        $query = 部署マスタ::query()
            ->when(
                $group,
                function($q) use($group) {
                    return $q->where('部署グループ', $group);
                });

        $BushoList = collect($query->get())
            ->map(function ($busho) {
                $vm = (object) $busho;

                $vm->Cd = $busho->部署CD;
                $vm->CdNm = $busho->部署名;
                $vm->Group = $busho->部署グループ;

                return $vm;
            })
            ->values();

        return response()->json($BushoList);
    }

    /**
     * GetCourseList
     */
    public function GetCourseList($request)
    {
        $bushoCd = $request->bushoCd;
        $courseKbn = $request->courseKbn;

        $query = コースマスタ::query()
            ->when(
                $bushoCd,
                function ($q) use ($bushoCd) {
                    return $q->where('部署ＣＤ', $bushoCd);
                }
            )
            ->when(
                $courseKbn,
                function ($q) use ($courseKbn) {
                    return $q->where('コース区分', $courseKbn);
                }
            );

        $CourseList = collect($query->get())
            ->map(function ($course) {
                $vm = (object) $course;

                $vm->Cd = $course->コースＣＤ;
                $vm->CdNm = $course->コース名;

                return $vm;
            })
            ->values();

        return response()->json($CourseList);
    }

    /**
     * GetAvailableProductList
     */
    public function GetAvailableProductList($request)
    {
        $group = $request->group;
        $isOthersGrouping = $request->isOthersGrouping;

        /*
select
	*
from
(
	select
		RANK() over(partition by 商品区分, ｸﾞﾙｰﾌﾟ区分 order by 商品区分, 商品ＣＤ) as rnk,
		*
	from 商品マスタ
	where	表示ＦＬＧ != 1
	and (商品区分 in (1,2,3) or ｸﾞﾙｰﾌﾟ区分=8)
) r
where rnk=1
order by
	商品ＣＤ

        */

        $query = 商品マスタ::query()
            ->select('*', DB::raw('RANK() over(partition by 商品区分, ｸﾞﾙｰﾌﾟ区分 order by 商品区分, 商品ＣＤ) as rnk'))
            ->where('表示ＦＬＧ', '!=', 1)
            ->where(
                function ($q) {
                    return $q->whereIn('商品区分', [1, 2, 3])->orWhere('ｸﾞﾙｰﾌﾟ区分', 8);
                }
            )
            ->orderBy('商品ＣＤ');

        $ProductList = collect($query->get())
            ->filter(function ($product) {
                return $product->rnk == 1;
            })
            ->concat(
                $isOthersGrouping
                    ? [(object) ['商品ＣＤ' => '9999', '商品名' => 'その他']]
                    : []
            )
            ->values();

        return response()->json($ProductList);
    }

    /**
     * Push
     */
    public function Push($request)
    {
        $params = (object) $request->all();
        $isAll = $request->has('isAll') ? $params->isAll : false;
        $targets = $request->has('targets') ? $params->targets : [];

        try {
            if ($isAll) {
                broadcast(new PublicEvent());
            } else {
                broadcast(new PrivateEvent());
            }
        } catch (Exception $e) { }
    }
}
