<?php

namespace App\Http\Controllers;

use App\Models\コーステーブル;
use App\Models\各種テーブル;
use App\Models\商品マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Illuminate\Support\Facades\DB as IlluminateDB;

class DAI01040Controller extends Controller
{
    /**
     * Search
     */
    public function Search($vm)
    {
        $BushoCd = $vm->BushoCd;
        $CourseCd = $vm->CourseCd;
        $OrderDate = $vm->TargetDate;
        $OrderUser = $vm->TantoCd;

        if (!($BushoCd && $CourseCd && $OrderDate && $OrderUser)) {
            //TODO: debug
            // $BushoCd = 101;
            // $CourseCd = 110;
            // $OrderDate = '2019/07/24';
            // $OrderUser = 203;

            //TODO: required error?
            return [];
        }

        $sql = "
with
支払方法 as (
	select
		各種CD, 行NO, 各種名称 as 支払方法
	from 各種テーブル
	where 各種CD=1
),
得意先 as (
	select
		c.*,
		tt.得意先名 ,
		tt.得意先名略称,
		tt.売掛現金区分,
		x.支払方法
	from コーステーブル c
	left outer join 得意先マスタ tt
		on c.得意先ＣＤ=tt.得意先ＣＤ
	left outer join 支払方法 x
		on x.行NO=tt.売掛現金区分
	where
		c.部署CD=$BushoCd
	and c.コースＣＤ=$CourseCd
),
対象商品 as  (
	select
		商品ＣＤ, 商品名
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
)
select
	得意先ＣＤ,
	得意先名略称,
	売掛現金区分,
	得意先支払方法,
	支払方法,
	商品ＣＤ,
	商品名,
	SUM(個数) as 個数,
	SUM(金額) as 金額
from
(
	select
		tk.ＳＥＱ,
		tk.得意先ＣＤ,
		tk.得意先名略称,
		tk.売掛現金区分,
		tk.支払方法 as 得意先支払方法,
		x.支払方法,
		ISNULL(p.商品ＣＤ, 9999) as 商品ＣＤ,
		ISNULL(p.商品名, 'その他') as 商品名,
		IIF(x.支払方法='現金', c.現金個数, c.掛売個数) as 個数,
		IIF(x.支払方法='現金', c.現金金額, c.掛売金額) as 金額
	from 得意先 tk
	cross join 支払方法 x
	left outer join 注文データ c
		on	c.得意先ＣＤ=tk.得意先ＣＤ
		and	c.注文日付='$OrderDate'
		and	c.部署ＣＤ=$BushoCd
		and c.修正担当者ＣＤ=$OrderUser
	left outer join 対象商品 p
		on p.商品ＣＤ = c.商品ＣＤ
) ret
group by
	ＳＥＱ,
	得意先ＣＤ,
	得意先名略称,
	売掛現金区分,
	得意先支払方法,
	支払方法,
	商品ＣＤ,
	商品名
order by
	ＳＥＱ,
	得意先ＣＤ,
	支払方法,
	商品ＣＤ
		        ";

        $DataList = DB::select($sql);

/* Eloquent Join/Subquery
        //得意先検索
        $CustomerQuery = コーステーブル::query()
            ->leftJoin('得意先マスタ', 'コーステーブル.得意先ＣＤ', '=', '得意先マスタ.得意先ＣＤ')
            ->select('コーステーブル.*', '得意先マスタ.得意先名略称')
		    ->where('コーステーブル.部署CD', $BushoCd)
            ->where('コーステーブル.コースＣＤ', $CourseCd);

        //商品検索
        $ProductSubQuery = 商品マスタ::query()
            ->select('*', DB::raw('RANK() over(partition by 商品区分, ｸﾞﾙｰﾌﾟ区分 order by 商品区分, 商品ＣＤ) as rnk'))
            ->where('表示ＦＬＧ', '!=', 1)
            ->where(
                function ($q) {
                    return $q->whereIn('商品区分', [1, 2, 3])->orWhere('ｸﾞﾙｰﾌﾟ区分', 8);
                }
            )
            ->orderBy('商品ＣＤ');

        $ProductQuery = DB::table(DB::raw("({$ProductSubQuery->toSql()}) as r"))
            ->where('r.rnk', 1);

        //支払方法
        $PaymentsQuery = 各種テーブル::query()
            ->selectRaw('各種CD, 行NO, 各種名称 as 支払方法')
            ->where('各種CD', 1);

        //対象注文データ
        $OrderQuery = DB::table(DB::raw("({$CustomerQuery->toSql()}) as tk"))
            ->mergeBindings($CustomerQuery)
            ->crossJoin(DB::raw("({$ProductQuery->toSql()}) as p"))
            ->mergeBindings($ProductSubQuery)
            ->mergeBindings($ProductQuery)
            ->crossJoin(DB::raw("({$PaymentsQuery->toSql()}) as x"))
            ->mergeBindings($PaymentsQuery)
            ->leftJoinSub('注文データ', 'c', function($join) use($BushoCd, $OrderDate, $OrderUser) {
                $join->on('tk.得意先ＣＤ', '=', 'c.得意先ＣＤ')
                     ->where('c.部署ＣＤ', $BushoCd)
                     ->where('c.注文日付', $OrderDate)
                     ->where('c.修正担当者ＣＤ', $OrderUser);
            })
            ->select(
                'tk.ＳＥＱ',
                'tk.得意先ＣＤ',
                'tk.得意先名略称',
                'x.支払方法',
                DB::raw('ISNULL(p.商品ＣＤ, 9999) as 商品ＣＤ'),
                DB::raw("ISNULL(p.商品名, 'その他') as 商品名"),
                DB::raw("IIF(x.支払方法='現金', c.現金個数, c.掛売個数) as 個数"),
                DB::raw("IIF(x.支払方法='現金', c.現金金額, c.掛売金額) as 金額")
            )
            ;
        $sql = $OrderQuery->toSql();
        $bindings = $OrderQuery->getBindings();

        //注文検索実行
        $DataList = DB::table(DB::raw("({$OrderQuery->toSql()}) as ret"))
            ->select(
                '得意先ＣＤ',
                '得意先名略称',
                '支払方法',
                '商品ＣＤ',
                '商品名',
                DB::raw('SUM(個数) as 個数'),
                DB::raw('SUM(金額) as 金額')
            )
            ->groupBy('ＳＥＱ', '得意先ＣＤ', '得意先名略称', '支払方法', '商品ＣＤ', '商品名')
            ->orderBy('ＳＥＱ', 'asc')
            ->orderBy('得意先ＣＤ', 'asc')
            ->orderBy('支払方法', 'asc')
            ->orderBy('商品ＣＤ', 'asc')
            ->get();
*/

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
