<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use App\Models\祝日マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use \GuzzleHttp\Client;

class DAI04160Controller extends Controller
{
    /**
     * GetHolidaysFromGov
     */
    public function GetHolidaysFromGov($request) {
        //内閣府-国民の祝日について(https://www8.cao.go.jp/chosei/shukujitsu/gaiyou.html)から祝日一覧をDownload
        $client = new Client();
        $response = $client->request("GET", "https://www8.cao.go.jp/chosei/shukujitsu/syukujitsu.csv");
        $csv = $response->getBody()->getContents();
        $csv = mb_convert_encoding($csv, "UTF-8", "SJIS");
        $array = array_map("str_getcsv", explode(PHP_EOL, $csv));

        $now = Carbon::now();
        $list = collect($array)->filter(function ($v, $i) use ($now) {
            if ($i == 0) return false;
            if (count($v) < 2) return false;

            $d = Carbon::createFromFormat("Y/m/d", $v[0]);
            return $d->gte($now);
        })
        ->map(function($v) use ($now) {
            $d = Carbon::createFromFormat("Y/m/d", $v[0]);
            return [
                "対象日付" => $d->format("Y-m-d"),
                "名称" => $v[1],
                "対象部署ＣＤ" => "",
                "修正担当者ＣＤ" => 999,
                "修正日" => $now->format("Y-m-d H:i:s"),
            ];
        })
        ->values();

        foreach ($list as $rec) {
            $r = 祝日マスタ::query()
                ->where('対象日付', $rec['対象日付'])
                ->get();

            if (count($r) != 0) {
                continue;
            }

            祝日マスタ::insert($rec);
        }
        //TODO: 未連携、一括更新のパターン
        $params = $request->all();
        $Message = $params['Message'];
        $ds = new DataSendWrapper();
        $ds->UpdateHolidayMaster($list->toArray(),$Message);

        return response()->json([
            "result" => true,
        ]);
    }
}
