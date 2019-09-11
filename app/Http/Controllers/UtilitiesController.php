<?php

namespace App\Http\Controllers;

use App\Events\PrivateEvent;
use App\Events\PublicEvent;
use Illuminate\Http\Request;
use DB;

class UtilitiesController extends Controller
{
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
