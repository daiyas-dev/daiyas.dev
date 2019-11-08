<?php

namespace App\Http\Controllers;

use App\Events\PrivateEvent;
use App\Events\PublicEvent;
use App\Models\コーステーブル;
use App\Models\コースマスタ;
use App\Models\備考マスタ;
use App\Models\各種テーブル;
use App\Models\商品マスタ;
use App\Models\得意先マスタ;
use App\Models\担当者マスタ;
use App\Models\祝日マスタ;
use App\Models\部署マスタ;
use Illuminate\Http\Request;
use DB;

class UtilitiesController extends Controller
{
    /**
     * GetCodeList
     */
    public function GetCodeList($request)
    {
        $cd = $request->cd;
        $cds = $request->cds;

        $query = 各種テーブル::query()
            ->when(
                $cd,
                function ($q) use ($cd) {
                    return $q->where('各種CD', $cd);
                }
            )
            ->when(
                $cds,
                function ($q) use ($cds) {
                    return $q->whereIn('各種CD', $cds);
                }
            )
            ->orderBy('各種CD', 'asc')
            ->orderBy('行NO', 'asc')
            ;

        $CodeList = collect($query->get())
            ->map(function ($info) {
                $vm = (object) $info;

                //一覧用項目追加
                $vm->Cd = $info->行NO;
                $vm->CdNm = $info->各種名称;
                $vm->CdAbbr = $info->各種略称;

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
        $BushoCd = $request->bushoCd;
        $TantoCd = $request->tantoCd;

        $query = 担当者マスタ::with(['部署'])
            ->when(
                $BushoCd,
                function ($q) use ($BushoCd) {
                    return $q->where('所属部署ＣＤ', $BushoCd);
                }
            )
            ->where('ユーザーＩＤ', '>', 0);

        $UserList = collect($query->get())
            ->map(function ($user) {
                $vm = (object) $user;

                $vm->Cd = trim($user->ユーザーＩＤ);
                $vm->CdNm = $user->担当者名;

                //password
                $vm->パスワード = '';

                return $vm;
            })
            ->values();

        return response()->json($UserList);
    }

    /**
     * GetTantoList
     */
    public function GetTantoList($request)
    {
        $BushoCd = $request->bushoCd;
        $TantoCd = $request->tantoCd;

        $query = 担当者マスタ::with(['部署'])
            ->when(
                $BushoCd,
                function ($q) use ($BushoCd) {
                    return $q->where('所属部署ＣＤ', $BushoCd);
                }
            )
            ->when(
                $TantoCd,
                function ($q) use ($TantoCd) {
                    return $q->where('担当者ＣＤ', $TantoCd);
                }
            )
            ->where('ユーザーＩＤ', '>', 0);

        $UserList = collect($query->get())
            ->map(function ($user) {
                $vm = (object) $user;

                $vm->Cd = $user->担当者ＣＤ;
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
        $cds = $request->cds;
        $group = $request->group;

        $query = 部署マスタ::query()
            ->when(
                $cds,
                function ($q) use ($cds) {
                    return $q->whereIn('部署CD', $cds);
                }
            )
            ->when(
                $group,
                function($q) use($group) {
                    return $q->where('部署グループ', $group);
                });

        $BushoCdList = collect($query->get())
            ->map(function ($BushoCd) {
                $vm = (object) $BushoCd;

                //一覧用項目追加
                $vm->Cd = $BushoCd->部署CD;
                $vm->CdNm = $BushoCd->部署名;
                $vm->Group = $BushoCd->部署グループ;

                return $vm;
            })
            ->values();

        return response()->json($BushoCdList);
    }

    /**
     * GetBikouList
     */
    public function GetBikouList($request)
    {
        $Kbn = $request->kbn;

        $query = 備考マスタ::query()
            ->when(
                $Kbn,
                function ($q) use ($Kbn) {
                    return $q->whereIn('区分', $Kbn);
                }
            );

        $BikouList = collect($query->get())
            ->map(function ($Bikou) {
                $vm = (object) $Bikou;

                //一覧用項目追加
                $vm->Cd = $Bikou->備考ＣＤ;
                $vm->CdNm = $Bikou->備考;
                $vm->Group = $Bikou->区分;

                return $vm;
            })
            ->values();

        return response()->json($BikouList);
    }

    /**
     * GetHolidayList
     */
    public function GetHolidayList($request)
    {
        $from = $request->from;
        $to = $request->to;

        $query = 祝日マスタ::query()
            ->when(
                $from,
                function ($q) use ($from) {
                    return $q->where('対象日付', '>=', $from);
                }
            )
            ->when(
                $to,
                function ($q) use ($to) {
                    return $q->where('対象日付', '<=', $to);
                }
            );

        $HolidayList = collect($query->get())
            ->map(function ($Bikou) {
                $vm = (object) $Bikou;

                //一覧用項目追加
                $vm->Cd = $Bikou->対象日付;
                $vm->CdNm = $Bikou->名称;

                return $vm;
            })
            ->values();

        return response()->json($HolidayList);
    }

    /**
     * GetCourseList
     */
    public function GetCourseList($request)
    {
        $BushoCd = $request->bushoCd;
        $CourseCd = $request->courseCd;
        $CourseKbn = $request->courseKbn;

        $query = コースマスタ::with(['担当者'])
            ->when(
                $BushoCd,
                function ($q) use ($BushoCd) {
                    return $q->where('部署ＣＤ', $BushoCd);
                }
            )
            ->when(
                $CourseCd,
                function ($q) use ($CourseCd) {
                    return $q->where('コースＣＤ', $CourseCd);
                }
            )
            ->when(
                $CourseKbn,
                function ($q) use ($CourseKbn) {
                    return $q->where('コース区分', $CourseKbn);
                }
            );

        $CourseList = collect($query->get())
            ->map(function ($course) {
                $vm = (object) $course;

                $vm->Cd = $course->コースＣＤ;
                $vm->CdNm = $course->コース名;

                $vm->担当者名 = $course->担当者->担当者名;

                return $vm;
            })
            ->values();

        return response()->json($CourseList);
    }

    /**
     * GetCustomerList
     */
    public function GetCustomerListFromCourse($request)
    {
        $BushoCd = $request->bushoCd;
        $CourseCd = $request->courseCd;

        $query = コーステーブル::with(['得意先'])
            ->when(
                $BushoCd,
                function ($q) use ($BushoCd) {
                    return $q->where('部署ＣＤ', $BushoCd);
                }
            )
            ->when(
                $CourseCd,
                function ($q) use ($CourseCd) {
                    return $q->where('コースＣＤ', $CourseCd);
                }
            );

        $CustomerList = collect($query->get())
            ->map(function ($course) {
                $vm = (object) $course;

                $vm->Cd = $course->得意先ＣＤ;
                $vm->CdNm = $course->得意先->得意先名;
                $vm->得意先名 = $course->得意先->得意先名;
                $vm->得意先名カナ = $course->得意先->得意先名カナ;
                $vm->得意先名略称 = $course->得意先->得意先名略称;
                $vm->電話番号１ = $course->得意先->電話番号１;
                $vm->備考１ = $course->得意先->備考１;
                $vm->備考２ = $course->得意先->備考２;
                $vm->備考３ = $course->得意先->備考３;

                unset($vm->得意先);

                return $vm;
            })
            ->values();

        return response()->json($CustomerList);
    }

    /**
     * GetProductList
     */
    public function GetProductList($request)
    {
        $group = $request->group;

        $query = 商品マスタ::query()
            ->where('表示ＦＬＧ', 0)
            ->orderBy('商品ＣＤ');

        $ProductList = collect($query->get())
            ->map(function ($product) {
                $vm = (object) $product;

                $vm->Cd = $product->商品ＣＤ;
                $vm->CdNm = $product->商品名;

                return $vm;
            })
            ->values();

        return response()->json($ProductList);
    }

    /**
     * GetAvailableProductList
     */
    public function GetAvailableProductList($request)
    {
        $group = $request->group;
        $isOthersGrouping = $request->isOthersGrouping;

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
     * GetCustomerAndCourseList
     */
    public function GetCustomerAndCourseList($request)
    {
        $BushoCd = $request->bushoCd;
        $CourseKbn = $request->courseKbn;
        $TargetDate = $request->targetDate;
        $KanaNm = $request->kanaNm;
        $TelNo = $request->telNo;
        $IsOyaOnly = $request->isOyaOnly ?? true;

        $WhereBushoCd = $BushoCd ? " AND M1.部署ＣＤ = $BushoCd" : "";
        $WhereKanaNm = $KanaNm ? " AND M1.得意先名カナ LIKE '%$KanaNm%'" : "";
        $WhereTelNo = $TelNo ? " AND M1.得意先ＣＤ IN (SELECT Tel_CustNo FROM C_TelToCust WHERE Tel_TelNo LIKE '$TelNo%')" : "";
        $WhereOyaOnly = $IsOyaOnly ? " AND (M1.受注得意先ＣＤ = 0 OR M1.受注得意先ＣＤ = M1.得意先ＣＤ)" : "";

        $WhereCourseKbn =  $CourseKbn
                                ? " AND MC.コース区分 = $CourseKbn"
                                : $TargetDate
                                    ? "AND MC.コース区分 =
                                            CASE
                                                WHEN (SELECT 対象日付 FROM 祝日マスタ WHERE 対象日付 = '$TargetDate') IS NOT NULL THEN 4
                                                ELSE
                                                    CASE DATEPART(WEEKDAY, '$TargetDate')
                                                        WHEN 1 THEN 3
                                                        WHEN 7 THEN 2
                                                        ELSE 1
                                                    END
                                            END"
                                    : "";

        $OrderBy = $KanaNm ? " ORDER BY 得意先名カナ" : " ORDER BY 得意先ＣＤ ";

        $sql = "
WITH 得意先_コース一覧 AS
(
	SELECT
		M1.部署CD,
		MB.部署名,
		M1.得意先CD,
		M1.得意先名,
		M1.得意先名略称,
		M1.得意先名カナ,
		M1.売掛現金区分,
		M1.電話番号１,
		M1.備考１,
		M1.備考２,
		M1.備考３,
		MC.担当者ＣＤ,
		MT.担当者名,
		MC.コース区分,
		MC.コースCD,
		MC.コース名,
		RANK() OVER(PARTITION BY M1.部署CD, M1.得意先CD ORDER BY MC.コース区分) AS RNK
	FROM
		得意先マスタ M1
		LEFT OUTER JOIN 部署マスタ MB
			ON MB.部署CD = M1.部署CD
		LEFT OUTER JOIN コーステーブル TC
			ON M1.部署CD = TC.部署CD AND M1.得意先CD = TC.得意先CD
		LEFT OUTER JOIN コースマスタ MC
			ON TC.部署CD = MC.部署CD AND TC.コースCD = MC.コースCD
		LEFT OUTER JOIN 担当者マスタ MT
			ON MT.担当者ＣＤ = MC.担当者ＣＤ
    WHERE
        0=0
		$WhereBushoCd
        $WhereKanaNm
        $WhereTelNo
        $WhereOyaOnly
        $WhereCourseKbn
)
SELECT
	得意先CD AS Cd,
	得意先名 AS CdNm,
	部署CD,
	部署名,
	得意先CD,
	得意先名,
    得意先名略称,
    得意先名カナ,
    売掛現金区分,
    電話番号１,
    備考１,
    備考２,
    備考３,
	担当者ＣＤ,
	担当者名,
	コース区分,
	コースCD,
	コース名
FROM
	得意先_コース一覧
WHERE
	RNK = 1
$OrderBy
        ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
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
