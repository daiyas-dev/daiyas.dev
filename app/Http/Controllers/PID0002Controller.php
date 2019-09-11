<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class PID0002Controller extends Controller
{
    /**
     * GetViewModel
     */
    public function GenerateViewModel()
    {
        //TODO: dummy ViewModel
        $vm = json_decode('
            {
                "InfoNo": 0,
                "UserId": "User",
                "UserName": "ユーザ名",
                "InfoTitle": "件名",
                "InfoMemo": "お知らせ内容",
                "StartDate": null,
                "EndDate": null
            }
        ');
        $now = now();
        $vm->StartDate = $now->format('Y/m/d');
        $vm->EndDate = $now->addDays(10)->format('Y/m/d');

        return $vm;
    }

    /**
     * GetViewModel
     */
    public function GetViewModel()
    {
        return response()->json($this->GenerateViewModel());
    }

    /**
     * Search
     */
    public function Search($vm)
    {
        // //TODO: DB代わりにsession
        // $InfoList = session("InfoList");
        // if ($InfoList != null) {
        //     return response()->json($InfoList);
        // }

        // //TODO: dummy InfoList
        // $now = now();
        // $InfoList = collect(range(1, 5))
        //     ->map(function($n) use ($now) {
        //         $vm = $this->GenerateViewModel();
        //         $vm->InfoNo = $n;
        //         $vm->UserId = $vm->UserId . sprintf('%03d', $n);
        //         $vm->UserName = $vm->UserName . sprintf('%03d', $n);
        //         $vm->InfoTitle = $vm->InfoTitle . sprintf('%03d', $n);
        //         $vm->InfoMemo = $vm->InfoMemo . sprintf('%03d', $n);
        //         $vm->StartDate = (clone $now)->addDays($n)->format('Y/m/d');

        //         return $vm;
        //     })
        //     ->values();

        // session(["InfoList"=>$InfoList]);

        $InfoList = DB::table('informations')
                        ->leftJoin('users', 'users.id', '=', 'informations.user_id')
                        ->get();

        return response()->json($InfoList);
    }

    /**
     * Save
     */
    public function Save($request)
    {
        $lists = $request->all();
        $AddList = $lists['AddList'];
        $UpdateList = $lists['UpdateList'];
        $DeleteList = $lists['DeleteList'];

        //TODO: validation
        validator()->validate($lists, [
            'AddList.*.info_no' => 'required',
            'AddList.*.start_date' => 'required',
            'AddList.*.name' => 'required',
            'AddList.*.user_id' => 'required',
            'AddList.*.info_title' => 'required',
            'AddList.*.info_memo' => 'required',
            'UpdateList.*.info_no' => 'required',
            'UpdateList.*.start_date' => 'required',
            'UpdateList.*.name' => 'required',
            'UpdateList.*.user_id' => 'required',
            'UpdateList.*.info_title' => 'required',
            'UpdateList.*.info_memo' => 'required',
        ]);

        //TODO: dummy InfoList
        $now = now();
        $InfoList = collect(range(1, 100))
            ->map(function($n) use ($now) {
                $vm = $this->GenerateViewModel();
                $vm->InfoTitle = $vm->InfoTitle . sprintf('%03d', $n);
                $vm->InfoMemo = $vm->InfoMemo . sprintf('%03d', $n);
                $vm->StartDate = (clone $now)->addDays($n)->format('Y/m/d');

                return $vm;
            })
            ->values();

        session(["InfoList"=>$InfoList]);

        return response()->json($InfoList);
    }
}
