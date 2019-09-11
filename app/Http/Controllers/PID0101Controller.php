<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;

class PID0101Controller extends Controller
{

    /**
     * GetViewModel
     */
    public function GenerateViewModel()
    {
        //TODO: dummy ViewModel
        $vm = json_decode('
            {
            }
        ');

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
        //TODO: dummy DataList
        $faker = \Faker\Factory::create('ja_JP');
        $DataList = Arr::collapse(
            collect(range(1, 3))
                ->map(function($k) use($faker) {
                    $vms = collect(range(1, $faker->numberBetween(1, 3)))
                        ->map(function($j) use ($k, $faker) {
                            $vm = (object) [];
                            $vm->UID = $faker->uuid;
                            $vm->MajorNo = sprintf('%02d', $k);
                            $vm->MinorNo = sprintf('%02d', $k) . sprintf('%02d', $j);
                            $vm->Volume = $faker->numberBetween(1, 100);
                            $vm->Unit = $faker->numberBetween(1, 4);
                            $vm->UPrice1000 = $faker->randomFloat(2, 100, 1000);
                            $vm->Memo = $faker->realText;

                            return $vm;
                        })
                        ->values();

                    return $vms;
                })
                ->values()
        );

        // $DataList = [];

        return response()->json($DataList);
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
            'AddList.*.StartYMD' => 'required',
            'AddList.*.InfoTitle' => 'required',
            'AddList.*.InfoMemo' => 'required',
            'UpdateList.*.StartYMD' => 'required',
            'UpdateList.*.InfoTitle' => 'required',
            'UpdateList.*.InfoMemo' => 'required',
        ]);

        //TODO: dummy DataList
        $kow = now();
        $DataList = collect(range(1, 100))
            ->map(function($k) use ($kow) {
                $vm = $this->GenerateViewModel();
                $vm->InfoTitle = $vm->InfoTitle . sprintf('%03d', $k);
                $vm->InfoMemo = $vm->InfoMemo . sprintf('%03d', $k);
                $vm->StartDate = (clone $kow)->addDays($k)->format('Y/m/d');

                return $vm;
            })
            ->values();

        session(["DataList"=>$DataList]);

        return response()->json($DataList);
    }
}
