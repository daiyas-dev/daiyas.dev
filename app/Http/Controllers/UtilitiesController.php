<?php

namespace App\Http\Controllers;

use App\Events\PrivateEvent;
use App\Events\PublicEvent;
use Illuminate\Http\Request;
use DB;

class UtilitiesController extends Controller
{

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
        $UserList = collect(DB::table('users')->get())
            ->map(function($user) {
                $vm = (object) $user;

                $vm->Cd = $user->id;
                $vm->CdNm = $user->name;
                $vm->GroupId = 'Group' . $user->id;
                $vm->GroupName = 'グループ' . $user->id;

                return $vm;
            })
            ->values();

        return response()->json($UserList);
    }

    /**
     * GetBusyoList
     */
    public function GetBusyoList($request)
    {
        //TODO: dummy BusyoList
        $faker = \Faker\Factory::create('ja_JP');
        $BusyoList = collect(range(1, 10))
                ->map(function($k) use($faker) {
                    $vm = (object) [];
                    $vm->Code = sprintf('%03d', $k);
                    $vm->Name = '部署' . sprintf('%03d', $k);

                    return $vm;
                })
                ->prepend((object) ['Code' => '101', 'Name' => '本社配送'])
                ->values();

        return response()->json($BusyoList);
    }

    /**
     * GetCourseList
     */
    public function GetCourseList($request)
    {
        $kbn = $request->kbn;

        $CourseList = collect(DB::table('courses')->where('kbn', 'like', $kbn??'%')->get())
            ->map(function ($course) {
                $vm = (object) $course;

                $vm->Cd = $course->cd;
                $vm->CdNm = $course->name;
                $vm->Kbn = $course->kbn;
                $vm->user_id = $course->user_id;

                return $vm;
            })
            ->values();

        return response()->json($CourseList);
    }

    /**
     * GetProductList
     */
    public function GetProductList($request)
    {
        $isOthersGrouping = $request->isOthersGrouping;

        $ProductList = collect(DB::table('products')->get())
            ->filter(function ($p) use ($isOthersGrouping) {
                return !$isOthersGrouping || !$p->isOthers;
            })
            ->concat($isOthersGrouping
                ? [(object) ['id' => 9999, 'productCd' => '9999', 'productNm' => 'その他']]
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
