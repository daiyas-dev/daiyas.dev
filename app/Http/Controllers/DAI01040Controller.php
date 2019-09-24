<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;

class DAI01040Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $ProductList = collect(DB::table('products')->get())
            ->filter(function ($p) {
                return !$p->isOthers;
            })
            ->concat([(object) ['id' => 9999, 'productCd' => '9999', 'productNm' => 'その他']])
            ->values();

        //TODO: dummy DataList
        $faker = \Faker\Factory::create('ja_JP');
        $DataList = Arr::collapse(
            collect(range(1, $faker->numberBetween(5, 7)))
                ->map(function($k) use($faker, $ProductList) {
                    $vms = collect(range(1, 2))
                        ->map(function($j) use ($k, $faker, $ProductList) {
                            $vm = (object) [];
                            $vm->CustomerIndex = $k;
                            $vm->CustomerCd = sprintf('%03d', $k);
                            $vm->CustomerInfo = sprintf('%03d', $k) . '得意先' . $k . ' 現金';
                            $vm->Payment = $j == 1 ? '現金': '売掛';

                            //TODO: その他まとめ有りの商品リスト
                            $vm->Orders = collect($ProductList)
                                ->map(function ($p) use ($k, $j, $faker) {
                                    $Order = (object)[];

                                    $Order->ProductCd = $p->productCd;
                                    $quantity = $faker->numberBetween(0, 1);
                                    $Order->Quantity = $quantity == 0 ? 0 : $faker->numberBetween(1, 3);
                                    $Order->Price = $Order->Quantity * 420;

                                    return $Order;
                                })
                                ->keyBy('ProductCd');

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
