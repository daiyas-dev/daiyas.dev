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
use App\Models\金融機関名称;
use App\Models\金融機関支店名称;
use App\Models\消費税率マスタ;
use Illuminate\Http\Request;
use DB;

class UtilitiesController extends Controller
{

    /**
     * GetColumns
     */
    public function GetColumns($request)
    {
        $TableName = $request->TableName;

        $sql = "
SELECT
	COLUMN_NAME,
	ORDINAL_POSITION,
	(CASE
		WHEN DATA_TYPE = 'varchar' THEN
			'string'
		WHEN DATA_TYPE = 'nvarchar' THEN
			'string'
		WHEN DATA_TYPE = 'int' THEN
			'integer'
		WHEN DATA_TYPE = 'numeric' AND NUMERIC_SCALE = 0 THEN
			'integer'
		WHEN DATA_TYPE = 'numeric' AND NUMERIC_SCALE > 0 THEN
			'float'
		WHEN DATA_TYPE = 'datetime' THEN
			'date'
		ELSE
			'string'
	END) AS DATA_TYPE,
	ISNULL(CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION) AS COLUMN_LENGTH
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = '$TableName'
        ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
    }

    /**
     *
     */
    public function GetCodeList($request)
    {
        $cd = $request->cd;
        $sub1 = $request->sub1;
        $sub2 = $request->sub2;
        $cds = $request->cds;

        $query = 各種テーブル::query()
            ->when(
                $cd,
                function ($q) use ($cd) {
                    return $q->where('各種CD', $cd);
                }
            )
            ->when(
                $sub1,
                function ($q) use ($sub1) {
                    return $q->where('サブ各種CD1', $sub1);
                }
            )
            ->when(
                $sub2,
                function ($q) use ($sub2) {
                    return $q->where('サブ各種CD2', $sub2);
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
                $vm->Sub1 = $info->サブ各種CD1;

                return $vm;
            })
            ->values();

        return response()->json($CodeList);
    }

    /**
     * GetBankList
     */
    public function GetBankList($request)
    {
        $BankCd = $request->BankCd;
        $KeyWord = $request->KeyWord;

        $WhereBank = $BankCd ? " AND BK.銀行CD=$BankCd" : "";
        $WhereKeyWord = $KeyWord
            ? " AND (
                    BK.銀行CD LIKE '$KeyWord%' OR
                    BK.銀行名 LIKE '%$KeyWord%' OR
                    BK.銀行名カナ LIKE '%$KeyWord%'
                )"
            : "";

        if (is_numeric($BankCd) && $WhereBank) {
            //銀行CDでの検索
            $ByCdSql = "
SELECT
    BK.銀行CD AS Cd,
    BK.銀行名 AS CdNm,
    BK.*
FROM 金融機関名称 BK
WHERE 0=0
$WhereBank
        ";

            $Result = DB::select($ByCdSql);

            if (count($Result) == 1) {
                return response()->json($Result);
            }
        }

        $sql = "
SELECT
    BK.銀行CD AS Cd,
    BK.銀行名 AS CdNm,
    BK.*
FROM 金融機関名称 BK
WHERE 0=0
$WhereKeyWord
        ";

        $Result = DB::select($sql);

        return response()->json($Result);
    }

    /**
     * GetBankBranchList
     */
    public function GetBankBranchList($request)
    {
        $BankCd = $request->BankCd;
        $BranchCd = $request->BranchCd;
        $KeyWord = $request->KeyWord;

        if (!is_numeric($BankCd)) {
            return [];
        }

        $WhereBank = $BankCd ? " AND BK.銀行CD=$BankCd" : "";
        $WhereBranch = $BranchCd ? " AND BB.支店CD=$BranchCd" : "";
        $WhereKeyWord = $KeyWord
            ? " AND (
                    BB.支店CD LIKE '$KeyWord%' OR
                    BB.支店名 LIKE '%$KeyWord%' OR
                    BB.支店名カナ LIKE '%$KeyWord%'
                )"
            : "";

        if (is_int($BranchCd) && $WhereBranch) {
            //銀行CD,支店CDでの検索
            $ByCdSql = "
SELECT
    BB.支店CD AS Cd,
    BB.支店名 AS CdNm,
	BK.銀行CD,
	BK.銀行名,
	BB.*
FROM 金融機関名称 BK
LEFT JOIN 金融機関支店名称 BB
    ON BK.銀行CD=BB.銀行CD
WHERE 0=0
$WhereBank
$WhereBranch
        ";

            $Result = DB::select($ByCdSql);

            if (count($Result) == 1) {
                return response()->json($Result);
            }
        }

        $sql = "
SELECT
    BB.支店CD AS Cd,
    BB.支店名 AS CdNm,
	BK.銀行CD,
	BK.銀行名,
	BB.*
FROM 金融機関名称 BK
LEFT JOIN 金融機関支店名称 BB
    ON BK.銀行CD=BB.銀行CD
WHERE 0=0
$WhereBank
$WhereKeyWord
        ";

        $Result = DB::select($sql);

        return response()->json($Result);
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

        $TantoList = collect($query->get())
            ->map(function ($user) {
                $vm = (object) $user;

                $vm->Cd = $user->担当者ＣＤ;
                $vm->CdNm = $user->担当者名;

                //password
                $vm->パスワード = '';

                return $vm;
            })
            ->values();

        return response()->json($TantoList);
    }

    /**
     * GetTantoListForMaint
     */
    public function GetTantoListForMaint($request)
    {
        $BushoCd = $request->BushoCd;

        $query = 担当者マスタ::query()
            ->when(
                $BushoCd,
                function ($q) use ($BushoCd) {
                    return $q->where('所属部署ＣＤ', $BushoCd);
                }
            );

        $TantoList = $query->get();

        return response()->json($TantoList);
    }

    /**
     * GetProductListForMaint
     */
    public function GetProductListForMaint($request)
    {
        $ProductCd = $request->ProductCd;

        $query = 商品マスタ::query()
            ->when(
                $ProductCd,
                function ($q) use ($ProductCd) {
                    return $q->where('商品ＣＤ', $ProductCd);
                }
            );

        // $ProductList = $query->get();
        $ProductList = collect($query->get())
            ->map(function($product){
                $vm = (object) $product;

                $vm->Cd = $product->商品ＣＤ;
                $vm->CdNm = $product->商品名;

                return $vm;
            })
            ->values();

        return response()->json($ProductList);
    }

    /**
     * GetMainSubListForSelect
     */
    public function GetMainSubListForSelect($request)
    {
        $BentoKbn = $request->bentoKbn;

        //副食区分、主食区分の選択肢取得
        $sql = "
            SELECT
                MP.商品ＣＤ AS Cd,
                MP.商品名 AS CdNm
            FROM 商品マスタ MP
            WHERE 弁当区分 = $BentoKbn
            ORDER BY 商品ＣＤ
        ";
        $ProductList = DB::select($sql);

        return response()->json($ProductList);
    }

    /**
     * GetGroupListForSelect
     */
    public function GetGroupListForSelect($request)
    {
        $BentoKbn = $request->bentoKbn;

        $sql = "
            SELECT
                MP.商品ＣＤ AS Cd,
                MP.商品名 AS CdNm
            FROM 商品マスタ MP
            WHERE 弁当区分 = $BentoKbn
            ORDER BY 商品ＣＤ
        ";
        $ProductList = DB::select($sql);

        return response()->json($ProductList);
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
     * GetCourseTableMngForMaint
     */
    public function GetCourseTableMngForMaint($request)
    {
        $BushoCd = $request->BushoCd;

        if (!$BushoCd) return [];

        $CourseCd = $request->CourseCd;
        $WhereCourseCd = $CourseCd ? " AND CM.コースＣＤ=$CourseCd " : "";

        $sql = "
SELECT
	CM.部署ＣＤ,
	BM.部署名,
	CM.コース区分,
	CK.各種名称 AS コース区分名,
	CM.コースＣＤ,
	CM.コース名,
    CM.担当者ＣＤ,
	TM.担当者名,
	CTM.管理ＣＤ,
	CTM.一時フラグ,
	IIF(CTM.一時フラグ=0, '基本', IIF(CTM.一時フラグ=1, '一時', '未登録')) AS 種別,
	CTM.適用開始日,
    CTM.適用終了日,
    CTM.修正担当者ＣＤ,
	CTM.備考,
	STM.担当者名 AS 修正担当者名,
    CTM.修正日
FROM コースマスタ CM
LEFT JOIN 部署マスタ BM
	ON BM.部署ＣＤ=CM.部署ＣＤ
LEFT JOIN 各種テーブル CK
	ON CK.行NO=CM.コース区分 AND CK.各種CD=19
LEFT JOIN 担当者マスタ TM
	ON TM.担当者ＣＤ=CM.担当者ＣＤ
LEFT JOIN コーステーブル管理 CTM
    ON CTM.部署ＣＤ=CM.部署ＣＤ AND CTM.コースＣＤ=CM.コースＣＤ
LEFT JOIN 担当者マスタ STM
	ON STM.担当者ＣＤ=CTM.修正担当者ＣＤ
WHERE
    CM.部署ＣＤ=$BushoCd
$WhereCourseCd
ORDER BY
	CM.部署ＣＤ,
	CM.コースＣＤ,
	CM.コース区分,
	CTM.一時フラグ,
	CTM.適用開始日,
    CTM.適用終了日
        ";

        $result = collect(DB::select($sql))
            ->map(function ($mng) {
                $vm = (object) $mng;

                //一覧用項目追加
                $vm->Cd = $mng->管理ＣＤ;
                $vm->CdNm = $mng->種別 . (!!$mng->備考 ? ("(" . $mng->備考 . ")") : "");

                return $vm;
            })
            ->values();

        if ($request->WithNew) {
            $result = collect($result)
                ->prepend([
                    'Cd' => '新規一時',
                    'CdNm' => '一時',
                    '管理ＣＤ' => '新規一時',
                    '一時フラグ' => 1,
                    '種別' => '一時',
                    '備考' => '新規'
                ]);

            if (!collect($result)->contains('一時フラグ', 0)) {
                $result = collect($result)
                    ->prepend([
                        'Cd' => '新規基本',
                        'CdNm' => '基本',
                        '管理ＣＤ' => '新規基本',
                        '一時フラグ' => 0,
                        '種別' => '基本',
                        '備考' => '新規'
                    ]);
            }
        }

        return response()->json($result);
    }

    /**
     * GetCourseTableForMaint
     */
    public function GetCourseTableForMaint($request)
    {
        $BushoCd = $request->BushoCd;

        if (!$BushoCd) return [];

        $sql = "
WITH 集約コーステーブル AS
(
	SELECT DISTINCT
		部署ＣＤ,
		コースＣＤ,
		基本コースフラグ,
		適用開始日,
		適用終了日
	FROM
		コーステーブル
)
SELECT
	CM.*,
	BM.部署名,
	CK.各種名称 AS コース区分名,
	TM.担当者名,
	STM.担当者名 AS 修正担当者名,
	CT.基本コースフラグ,
	CT.適用開始日,
	CT.適用終了日
FROM コースマスタ CM
LEFT JOIN 部署マスタ BM
	ON BM.部署ＣＤ=CM.部署ＣＤ
LEFT JOIN 各種テーブル CK
	ON CK.行NO=CM.コース区分 AND CK.各種CD=19
LEFT JOIN 担当者マスタ TM
	ON TM.担当者ＣＤ=CM.担当者ＣＤ
LEFT JOIN 担当者マスタ STM
	ON STM.担当者ＣＤ=CM.修正担当者ＣＤ
LEFT JOIN 集約コーステーブル CT
    ON CT.部署ＣＤ=CM.部署ＣＤ AND CT.コースＣＤ=CM.コースＣＤ
WHERE
    CM.部署ＣＤ=$BushoCd
ORDER BY
	CM.部署ＣＤ,
	CM.コースＣＤ,
	CM.コース区分
        ";

        $result = DB::select($sql);

        return response()->json($result);
    }

    /**
     * GetCodeListForMaint
     */
    public function GetCodeListForMaint($request)
    {
        $sql = "
            SELECT * FROM 各種テーブル
        ";
        $CodeList = DB::select($sql);

        return response()->json($CodeList);
    }

    /**
     * GetTaxListForMaint
     */
    public function GetTaxListForMaint($request)
    {
        $sql = "
            SELECT * FROM 消費税率マスタ
        ";
        $TaxList = DB::select($sql);

        return response()->json($TaxList);
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
     * GetCustomerListForSelect
     */
    public function GetCustomerListForSelect($request)
    {
        $BushoCd = $request->bushoCd ?? $request->BushoCd;
        $CustomerCd = $request->CustomerCd;
        $KeyWord = $request->KeyWord;

        $WhereBusho = $BushoCd ? " AND TM.部署ＣＤ=$BushoCd" : "";
        $WhereCustomer = $CustomerCd ? " AND TM.得意先ＣＤ=$CustomerCd" : "";
        $WhereKeyWord = $KeyWord
            ? " AND (
                    TM.得意先ＣＤ LIKE '$KeyWord%' OR
                    TM.得意先名 LIKE '%$KeyWord%' OR
                    TM.得意先名略称 LIKE '%$KeyWord%' OR
                    TM.得意先名カナ LIKE '%$KeyWord%' OR
                    TM.電話番号１ LIKE '$KeyWord%' OR
                    TM.備考１ LIKE '%$KeyWord%' OR
                    TM.備考２ LIKE '%$KeyWord%' OR
                    TM.備考３ LIKE '%$KeyWord%'
                )"
            : "";

        if ($CustomerCd && is_int($CustomerCd)) {
            //得意先ＣＤでの検索
            $ByCustomerSql = "
SELECT
    TM.得意先ＣＤ AS Cd,
    TM.得意先名 AS CdNm,
    TM.得意先名カナ,
    TM.得意先名略称,
    TM.電話番号１,
    TM.備考１,
    TM.備考２,
    TM.備考３,
    BM.部署名
FROM 得意先マスタ TM
LEFT JOIN 部署マスタ BM
    ON TM.部署CD = BM.部署CD
WHERE 0=0
$WhereBusho
$WhereCustomer
        ";

            $Result = DB::select($ByCustomerSql);

            if (count($Result) == 1) {
                return response()->json($Result);
            }
        }

        $CountSql = "
SELECT
    COUNT(TM.得意先ＣＤ) AS CNT
FROM 得意先マスタ TM
WHERE 0=0
$WhereBusho
$WhereKeyWord
        ";

        $Result = DB::select($CountSql);
        $Count = $Result[0]->CNT;
        $CountMax = 1000;
        $SelectTop = $Count > $CountMax ? "TOP 1000" : "";

        $sql = "
SELECT $SelectTop
    TM.得意先ＣＤ AS Cd,
    TM.得意先名 AS CdNm,
    TM.得意先名カナ,
    TM.得意先名略称,
    TM.電話番号１,
    TM.備考１,
    TM.備考２,
    TM.備考３,
    BM.部署名
FROM 得意先マスタ TM
LEFT JOIN 部署マスタ BM
    ON TM.部署CD = BM.部署CD
WHERE 0=0
$WhereBusho
$WhereKeyWord
        ";

        $DataList = DB::select($sql);

        return response()->json(['Data' => $DataList, 'CountConstraint' => !!$SelectTop ? $CountMax : null]);
    }

    /**
     * GetBushoListForMaint
     */
    public function GetBushoListForMaint($request)
    {
        $sql = "
            SELECT
                MB.*,
                BK.銀行名 AS 金融機関名称１,
                BB.支店名 AS 金融機関支店名称１,
                KK.各種名称 AS 口座種別１,
                BK2.銀行名 AS 金融機関名称２,
                BB2.支店名 AS 金融機関支店名称２,
                KK2.各種名称 AS 口座種別２
            FROM 部署マスタ MB
            LEFT JOIN 金融機関名称 BK
            　ON　MB.金融機関CD1=BK.銀行CD
            LEFT JOIN 金融機関支店名称 BB
            　ON　MB.金融機関CD1=BB.銀行CD AND MB.金融機関支店CD1=BB.支店CD
            LEFT JOIN 各種テーブル KK
            　ON　'7'=KK.各種CD AND MB.口座種別1=KK.行NO
            LEFT JOIN 金融機関名称 BK2
            　ON　MB.金融機関CD2=BK2.銀行CD
            LEFT JOIN 金融機関支店名称 BB2
            　ON　MB.金融機関CD2=BB2.銀行CD AND MB.金融機関支店CD2=BB2.支店CD
            LEFT JOIN 各種テーブル KK2
            　ON　'7'=KK2.各種CD AND MB.口座種別2=KK2.行NO
        ";
        $BusyoList = DB::select($sql);

        return response()->json($BusyoList);

        }

    /**
     * GetBankListForMaint
     */
    public function GetBankListForMaint($request)
    {
        $cds = $request->bankCd;

        $query = 金融機関名称::query()
            ->when(
                $cds,
                function ($q) use ($cds) {
                    return $q->whereIn('銀行CD', $cds);
                });

        $BankCdList = collect($query->get())
            ->map(function ($BankCd) {
                $vm = (object) $BankCd;

                //一覧用項目追加
                $vm->BankCd = $BankCd->銀行CD;
                $vm->BankNm = $BankCd->銀行名;

                return $vm;
            })
            ->values();

        return response()->json($BankCdList);
    }

    /**
     * GetBankBranchListForMaint
     */
    public function GetBankBranchListForMaint($request)
    {
        $cds = $request->BankCd;

        $query = 金融機関支店名称::query()
            ->when(
                $cds,
                function ($q) use ($cds) {
                    return $q->whereIn('銀行CD', $cds);
                });

                $BranchList = $query->get();

                return response()->json($BranchList);
    }

    /**
     * GetCustomerList
     */
    public function GetCustomerList($request)
    {
        $BushoCd = $request->bushoCd ?? $request->BushoCd;

        $WhereBusho = $BushoCd ? " AND TM.部署ＣＤ=$BushoCd" : "";

        $sql = "
SELECT
    TM.*,
    BM.部署名
FROM 得意先マスタ TM
LEFT JOIN 部署マスタ BM
    ON TM.部署CD = BM.部署CD
WHERE 0=0
$WhereBusho
        ";

        $DataList = DB::select($sql);

        return response()->json(['Data'=>$DataList, 'CountConstraint'=> false]);
    }

    /**
     * GetCustomerListFromCourse
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
     * GetGroupCustomerList
     */
    public function GetGroupCustomerList($request)
    {
        $CustomerCd = $request->CustomerCd;

        if (!$CustomerCd) return [];

        $sql = "
SELECT
    得意先ＣＤ AS Cd,
    得意先名 AS CdNm,
    *
FROM 得意先マスタ
WHERE 得意先ＣＤ IN (
SELECT
	DISTINCT(Tel_CustNo) AS 得意先ＣＤ
FROM C_TelToCust
WHERE Tel_CustNo != $CustomerCd
AND Tel_TelNo IN (
	SELECT Tel_TelNo
	FROM C_TelToCust
	WHERE Tel_CustNo = $CustomerCd
	AND Tel_DelFlg = 0
)
AND Tel_DelFlg = 0
)
        ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
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
        $GroupCustomerCd = $request->groupCustomerCd;

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

        $WhereGroupCustomer = $GroupCustomerCd ? "
             AND M1.得意先ＣＤ IN (
                SELECT
                    DISTINCT(Tel_CustNo)
                FROM C_TelToCust
                WHERE Tel_CustNo != $GroupCustomerCd
                AND Tel_TelNo IN (
                    SELECT Tel_TelNo
                    FROM C_TelToCust
                    WHERE Tel_CustNo = $GroupCustomerCd
                    AND Tel_DelFlg = 0
                )
                AND Tel_DelFlg = 0
            )" : "";

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
        $WhereGroupCustomer
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
     * GetShidashiOrderNoList
     */
    public function GetShidashiOrderNoList($request)
    {
        $DeliveryDate = $request->targetDate;

        $WhereDeliveryDate = $DeliveryDate ? " AND 配達日付 = '$DeliveryDate'" : "";

        $sql = "
WITH 仕出顧客 AS (
SELECT
    CHUMON.部署ＣＤ
    ,MB.部署名
    ,CHUMON.受注Ｎｏ
    ,CHUMON.配達日付
    ,CHUMON.配達時間,
    CHUMON.得意先ＣＤ
    ,TOK.得意先名
    ,TOK.電話番号１
    ,TOK.ＦＡＸ１
    ,CHUMON.注文日付
    ,ISNULL(TOK.住所１,'')  +  ISNULL(TOK.住所２,'') AS 住所
    ,ISNULL(CHUMON.配達先１,'')  + ISNULL(CHUMON.配達先２,'') AS 配達先
    ,CHUMON.エリアＣＤ
    ,COUM.コース名 AS エリア名称
    ,CHUMON.地域区分
    ,KAKUSHU_TIKU.各種名称 AS 地区名称
    ,CHUMON.配達区分
    ,KAKUSHU_HAITATSU.各種名称 AS 配達名称
    ,CHUMON.税区分
    ,KAKUSHU_ZEI.各種名称 AS 税名称
	,RANK() OVER(PARTITION BY CHUMON.得意先ＣＤ ORDER BY CHUMON.注文日付 DESC, CHUMON.受注Ｎｏ DESC) AS RNK
FROM
    仕出し注文データ CHUMON
    LEFT OUTER JOIN 得意先マスタ TOK ON
    CHUMON.得意先ＣＤ = TOK.得意先ＣＤ
    LEFT OUTER JOIN 各種テーブル KAKUSHU_TIKU ON
    KAKUSHU_TIKU.各種CD = 32
    AND KAKUSHU_TIKU.行NO = CHUMON.地域区分
    LEFT OUTER JOIN 各種テーブル KAKUSHU_HAITATSU ON
    KAKUSHU_HAITATSU.各種CD = 31
    AND KAKUSHU_HAITATSU.行NO = CHUMON.配達区分
    LEFT OUTER JOIN 各種テーブル KAKUSHU_ZEI ON
    KAKUSHU_ZEI.各種CD = 20
    AND KAKUSHU_ZEI.行NO = CHUMON.税区分
    LEFT OUTER JOIN コースマスタ COUM ON
    COUM.部署ＣＤ = CHUMON.部署ＣＤ
    AND COUM.コースＣＤ = CHUMON.エリアＣＤ
    LEFT OUTER JOIN 部署マスタ MB
    ON MB.部署CD = CHUMON.部署ＣＤ
)
SELECT
    DISTINCT
    受注Ｎｏ AS Cd,
    得意先名 AS CdNm,
	*
FROM
	仕出顧客
WHERE
    RNK = 1
    $WhereDeliveryDate
ORDER BY
    得意先ＣＤ
        ";

        try {
            $DataList = DB::select($sql);
            return response()->json($DataList);
        } catch (\Throwable $th) {
            return response()->json($th);
        }
    }

    /**
     * GetShidashiCustomerList
     */
    public function GetShidashiCustomerList($request)
    {
        $CustomerCd = $request->CustomerCd;

        $sql = "
WITH 顧客 AS (
SELECT
    TOK.部署ＣＤ
    ,MB.部署名
    ,TOK.得意先ＣＤ
    ,TOK.得意先名
    ,TOK.得意先名カナ
    ,TOK.電話番号１
    ,TOK.電話番号２
    ,TOK.ＦＡＸ１
    ,ISNULL(TOK.住所１,'')  +  ISNULL(TOK.住所２,'') AS 住所
    ,ISNULL(CHUMON.配達先１,'')  + ISNULL(CHUMON.配達先２,'') AS 配達先
    ,CHUMON.地域区分
    ,KAKUSHU_TIKU.各種名称 AS 地区名称
	,RANK() OVER(PARTITION BY CHUMON.得意先ＣＤ ORDER BY CHUMON.注文日付 DESC, CHUMON.受注Ｎｏ DESC) AS RNK
FROM
	得意先マスタ TOK
    LEFT OUTER JOIN 仕出し注文データ CHUMON ON
    CHUMON.得意先ＣＤ = TOK.得意先ＣＤ
    LEFT OUTER JOIN 各種テーブル KAKUSHU_TIKU ON
    KAKUSHU_TIKU.各種CD = 32
    AND KAKUSHU_TIKU.行NO = CHUMON.地域区分
    LEFT OUTER JOIN 部署マスタ MB
    ON MB.部署CD = TOK.部署ＣＤ
)
SELECT
	DISTINCT
    得意先ＣＤ AS Cd,
    得意先名 AS CdNm,
	*
FROM
	顧客
WHERE
	RNK = 1
ORDER BY
    得意先ＣＤ
        ";

        try {
            $DataList = DB::select($sql);
            return response()->json($DataList);
        } catch (\Throwable $th) {
            return response()->json($th);
        }
    }

    /**
     * GetShidashiCustomer
     */
    public function GetShidashiCustomer($request)
    {
        $BushoCd = $request->bushoCd;
        $OrderNo = $request->orderNo;
        $CustomerCd = $request->customerCd;

        $WhereBusho = $BushoCd ? " AND 部署ＣＤ=$BushoCd" : "";
        $WhereOrderNo = $OrderNo ? " AND 受注Ｎｏ=$OrderNo" : " AND RNK=1";
        $WhereCustomerCd = $CustomerCd ? " AND 得意先ＣＤ=$CustomerCd" : "";

        $sql = "
WITH 顧客 AS (
SELECT
    TOK.部署ＣＤ
    ,MB.部署名
    ,CHUMON.受注Ｎｏ
    ,CHUMON.配達日付
    ,CHUMON.配達時間,
    TOK.得意先ＣＤ
    ,TOK.得意先名
    ,TOK.得意先名カナ
    ,TOK.電話番号１
    ,TOK.電話番号２
    ,TOK.ＦＡＸ１
    ,CHUMON.注文日付
    ,ISNULL(TOK.住所１,'')  +  ISNULL(TOK.住所２,'') AS 住所
    ,ISNULL(CHUMON.配達先１,'')  + ISNULL(CHUMON.配達先２,'') AS 配達先
    ,CHUMON.エリアＣＤ
    ,COUM.コース名 AS エリア名称
    ,CHUMON.地域区分
    ,KAKUSHU_TIKU.各種名称 AS 地区名称
    ,CHUMON.配達区分
    ,KAKUSHU_HAITATSU.各種名称 AS 配達名称
    ,CHUMON.税区分
    ,KAKUSHU_ZEI.各種名称 AS 税名称
	,RANK() OVER(PARTITION BY CHUMON.得意先ＣＤ ORDER BY CHUMON.注文日付 DESC, CHUMON.受注Ｎｏ DESC) AS RNK
FROM
	得意先マスタ TOK
    LEFT OUTER JOIN 仕出し注文データ CHUMON ON
    CHUMON.得意先ＣＤ = TOK.得意先ＣＤ
    LEFT OUTER JOIN 各種テーブル KAKUSHU_TIKU ON
    KAKUSHU_TIKU.各種CD = 32
    AND KAKUSHU_TIKU.行NO = CHUMON.地域区分
    LEFT OUTER JOIN 各種テーブル KAKUSHU_HAITATSU ON
    KAKUSHU_HAITATSU.各種CD = 31
    AND KAKUSHU_HAITATSU.行NO = CHUMON.配達区分
    LEFT OUTER JOIN 各種テーブル KAKUSHU_ZEI ON
    KAKUSHU_ZEI.各種CD = 20
    AND KAKUSHU_ZEI.行NO = CHUMON.税区分
    LEFT OUTER JOIN コースマスタ COUM ON
    COUM.部署ＣＤ = CHUMON.部署ＣＤ
    AND COUM.コースＣＤ = CHUMON.エリアＣＤ
    LEFT OUTER JOIN 部署マスタ MB
    ON MB.部署CD = TOK.部署ＣＤ
)
SELECT
	DISTINCT
	*
FROM
	顧客
WHERE
    0=0
    $WhereBusho
    $WhereOrderNo
    $WhereCustomerCd
ORDER BY
    得意先ＣＤ
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
