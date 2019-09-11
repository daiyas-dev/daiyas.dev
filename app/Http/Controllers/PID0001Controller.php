<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PID0001Controller extends Controller
{
    /**
     * GetViewModel
     */
    public function GenerateViewModel()
    {
        //TODO: dummy ViewModel
        $vm = json_decode('
            {
                "InfoTitle": "お知らせ件名",
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
     * SearchInfo
     */
    public function SearchInfo($request)
    {
        //TODO: DB代わりにsession
        $InfoList = session("InfoList");
        if ($InfoList != null) {
            return response()->json($InfoList);
        }

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

    /**
     * SearchTask
     */
    public function SearchTask($request)
    {
        //TODO: dummy TaskList
        $now = now();
        $TaskList = collect(range(1, 100))
            ->map(function($n) use ($now) {
                $vm = (object)array('DeadLine' => null, 'TaskTitle' => 'タスク件名', 'TaskSummary' => 'タスク概要');
                $vm->DeadLine = (clone $now)->addDays($n + 10)->format('Y/m/d');
                $vm->TaskTitle = $vm->TaskTitle . sprintf('%03d', $n);
                $vm->TaskSummary = $vm->TaskSummary . sprintf('%03d', $n);

                return $vm;
            })
            ->values();

        return response()->json($TaskList);
    }
}
