<?php

namespace App\Http\Controllers;

use App\Events\PrivateEvent;
use App\Events\PublicEvent;
use App\Models\CTelToCust;
use App\Models\Web受注データ;
use App\Models\Web受注データ利用者別詳細;
use App\Models\Web受注データ利用者情報;
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
use App\Models\得意先履歴テーブル;
use App\Models\得意先単価マスタ;
use App\Models\請求データ;
use App\Models\非顧客電話番号マスタ;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDO;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

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
                $vm->Sub2 = $info->サブ各種CD2;

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
           ->where('ユーザーＩＤ', '!=', '0');

        $UserList = collect($query->get())
            ->filter(function ($user) {
                return !!$user->ユーザーＩＤ;
            })
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
           ->where('ユーザーＩＤ', '!=', '0');

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
     * GetTantoListForSelect
     */
    public function GetTantoListForSelect($request)
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
            );

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
        $TantoCd = $request->TantoCd;

        $query = 担当者マスタ::query()
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
     * GetMainSubList
     */
    public function GetMainSubList($request)
    {
        $BushoCd = $request->BushoCd;

        $sql = "
SELECT DISTINCT
	CASE
		WHEN SHOHIN.主食ＣＤ = 0 AND SHOHIN.副食ＣＤ = 0 THEN SHOHIN.商品ＣＤ
		WHEN S2.商品ＣＤ IS NOT NULL THEN S2.商品ＣＤ
		WHEN S1.商品ＣＤ IS NOT NULL THEN S1.商品ＣＤ
	END AS Cd,
	CASE
		WHEN SHOHIN.主食ＣＤ = 0 AND SHOHIN.副食ＣＤ = 0 THEN SHOHIN.商品名
		WHEN S2.商品ＣＤ IS NOT NULL THEN S2.商品名
		WHEN S1.商品ＣＤ IS NOT NULL THEN S1.商品名
	END AS CdNm,
	CASE
		WHEN SHOHIN.主食ＣＤ = 0 AND SHOHIN.副食ＣＤ = 0 THEN SHOHIN.商品ＣＤ
		WHEN S2.商品ＣＤ IS NOT NULL THEN S2.商品ＣＤ
		WHEN S1.商品ＣＤ IS NOT NULL THEN S1.商品ＣＤ
	END AS 商品ＣＤ,
	CASE
		WHEN SHOHIN.主食ＣＤ = 0 AND SHOHIN.副食ＣＤ = 0 THEN SHOHIN.商品名
		WHEN S2.商品ＣＤ IS NOT NULL THEN S2.商品名
		WHEN S1.商品ＣＤ IS NOT NULL THEN S1.商品名
	END AS 商品名,
	CASE
		WHEN SHOHIN.主食ＣＤ = 0 AND SHOHIN.副食ＣＤ = 0 THEN SHOHIN.商品区分
		WHEN S2.商品ＣＤ IS NOT NULL THEN S2.商品区分
		WHEN S1.商品ＣＤ IS NOT NULL THEN S1.商品区分
	END AS 商品区分
FROM
  商品マスタ SHOHIN
  LEFT OUTER JOIN 各種テーブル BUSHOGRP
	ON BUSHOGRP.各種CD = 26 AND SHOHIN.部署グループ = BUSHOGRP.サブ各種CD2
  LEFT OUTER JOIN 商品マスタ S1
	ON S1.商品CD = SHOHIN.主食ＣＤ
  LEFT OUTER JOIN 商品マスタ S2
	ON S2.商品CD = SHOHIN.副食ＣＤ
WHERE
  SHOHIN.表示ＦＬＧ = 0
  AND BUSHOGRP.サブ各種CD1 = $BushoCd
ORDER BY
	商品区分,
	商品ＣＤ
        ";

        $DataList = DB::select($sql);

        return response()->json($DataList);
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

        $sql = "
            SELECT
                MB.*,
                BK.銀行名 AS 金融機関1名称,
                BB.支店名 AS 金融機関支店1名称,
                KK.各種名称 AS 口座種別1名称,
                BK2.銀行名 AS 金融機関2名称,
                BB2.支店名 AS 金融機関支店2名称,
                KK2.各種名称 AS 口座種別2名称
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

        $BushoCdList = collect(DB::select($sql))
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
        $WhereCourseCd = isset($CourseCd) ? " AND CM.コースＣＤ=$CourseCd " : "";

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
            if (collect($result)->contains('一時フラグ', '0')) {
                $result = collect($result)
                    ->prepend([
                        'Cd' => '新規(一時)',
                        'CdNm' => '',
                        '管理ＣＤ' => '新規(一時)',
                        '一時フラグ' => 1,
                        '種別' => '一時',
                        '備考' => '新規'
                    ]);
            } else {
                $result = collect($result)
                    ->prepend([
                        'Cd' => '新規(基本)',
                        'CdNm' => '',
                        '管理ＣＤ' => '新規(基本)',
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
            WHERE 各種テーブル.各種CD > 0
            ORDER BY 各種CD
        ";
        $CodeList = DB::select($sql);

        return response()->json($CodeList);
    }

    /**
     * GetTaxListForMaint
     */
    public function GetTaxListForMaint($request)
    {
        $query = 消費税率マスタ::query();

        $TaxList = $query->get();

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
        $BushoCd = $request->BushoCd;

        $query = 祝日マスタ::query();

        if (!!$BushoCd) {
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
            )
            ->when(
                $BushoCd,
                function ($q) use ($BushoCd) {
                    return $q->where('対象部署ＣＤ', 'LIKE', "%$BushoCd%");
                }
            );
        } else {
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
        }


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
        $userBusho = $request->UserBushoCd; //Auth::user()->部署->部署CD;
        $rankBusho = !!$userBusho ? ",IIF(MB.部署CD = $userBusho, 0, 1) AS sort" : "";
        $orderBusho = !!$userBusho ? "ORDER BY sort, MB.部署CD, MC.コースCD" : "";

        $BushoCd = $request->bushoCd ?? $request->BushoCd;
        $WithZero = $request->WithZero;
        $WhereBushoCd = !!$BushoCd ? (!!$WithZero ? " AND (MC.部署CD=$BushoCd OR MC.コースCD=0)" : " AND MC.部署CD=$BushoCd") : "";

        $BushoArray = $request->BushoArray;
        $WhereBushoList = "";
        if ($BushoArray != null && is_array($BushoArray) && 0 < count($BushoArray)) {
            $BushoList = implode(',', $BushoArray);
            $WhereBushoList = " AND MC.部署ＣＤ IN ($BushoList)";
        }

        $TargetDate = $request->targetDate ?? $request->TargetDate;

        $CourseKbn = ($request->courseKbn ?? $request->CourseKbn) ?? $request->CourseKbn ?? (!!$TargetDate ? $this->SearchCourseKbnFromDate($request)->コース区分 : null);
        $WhereCourseKbn = !!$CourseKbn ? " AND MC.コース区分=$CourseKbn" : "";

        $KeyWord = $request->KeyWord;
        $CourseCd = $request->courseCd ?? $request->CourseCd;
        $WhereCourseCd = !isset($KeyWord) && !!$CourseCd ? " AND MC.コースＣＤ=$CourseCd" : "";

        $sql = "
            SELECT
                MC.*,
                MC.コースCD AS Cd,
                MC.コース名 AS CdNm,
				MB.部署名,
                MT.担当者名
                $rankBusho
            FROM
                コースマスタ MC
                LEFT OUTER JOIN 部署マスタ MB
                    ON MB.部署CD = MC.部署ＣＤ
                LEFT OUTER JOIN 担当者マスタ MT
                    ON MT.担当者ＣＤ = MC.担当者ＣＤ
            WHERE
                0=0
                $WhereBushoCd
                $WhereBushoList
                $WhereCourseKbn
                $WhereCourseCd
            $orderBusho
        ";

        // TODO: 高速化対応
        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        // $DataList = DB::select(DB::raw($sql));

        return response()->json($DataList);
    }

    /**
     * GetCourseListByCustomer
     */
    public function GetCourseListByCustomer($request)
    {
        $BushoCd = $request->bushoCd ?? $request->BushoCd;
        $CustomerCd = $request->customerCd ?? $request->CustomerCd;
        $TargetDate = $request->targetDate ?? $request->TargetDate;
        $CourseKbn = $request->CourseKbn ?? (!!$TargetDate ? $this->SearchCourseKbnFromDate($request)->コース区分 : null);
        $WhereCourseKbn = !!$CourseKbn ? " AND MC.コース区分=$CourseKbn" : "";

        $sql = "
SELECT
    TC.得意先ＣＤ,
    MC.コースCD,
    MC.コース名,
    MT.担当者ＣＤ,
    MT.担当者名,
    MC.コースCD AS Cd,
    MC.コース名 AS CdNm
FROM
    コースマスタ MC
    LEFT OUTER JOIN コーステーブル TC
        ON MC.部署CD = TC.部署CD AND MC.コースCD = TC.コースCD
    LEFT OUTER JOIN 担当者マスタ MT
        ON MT.担当者ＣＤ = MC.担当者ＣＤ
WHERE
    MC.部署CD=$BushoCd
AND TC.得意先ＣＤ=$CustomerCd
$WhereCourseKbn
        ";

        //TODO: 高速化対応
        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pdo = null;

        if (count($DataList) == 0 && isset($Keyword)) {
            return $this->GetCustomerAndCourseList($request, true);
        }

        return response()->json($DataList);
    }

    /**
     * GetCustomerListForSelect
     */
    public function GetCustomerListForSelect($request)
    {
        $BushoCd = $request->bushoCd ?? $request->BushoCd;
        $KeyWord = $request->KeyWord;
        $TelNo = !!$KeyWord ? str_replace('-', '', $KeyWord) : '';
        $BushoArray = $request->BushoArray;
        $WhereBushoList = "";
        if ($BushoArray != null && is_array($BushoArray) && 0 < count($BushoArray)) {
            $BushoList = implode(',', $BushoArray);
            $WhereBushoList = " AND TM.部署ＣＤ IN ($BushoList)";
        }

        $WhereBusho = $BushoCd ? " AND TM.部署ＣＤ=$BushoCd" : "";
        $WhereKeyWord = $KeyWord
            ? " AND (
                    TM.得意先名 LIKE '%$KeyWord%' OR
                    TM.備考１ LIKE '$KeyWord%' OR
                    TM.備考２ LIKE '$KeyWord%' OR
                    TM.備考３ LIKE '$KeyWord%' OR
                    TM.電話番号１ LIKE '$KeyWord%' OR
                    TM.電話番号１ LIKE '$TelNo%'
                )"
            : "";

        $CourseCd = $request->CourseCd;
        $SelectCourseCd = !!$BushoCd && !!$CourseCd ? ", $CourseCd AS コースＣＤ" : "";
        $WhereBushoCourse = $WhereBusho ? " AND 部署ＣＤ=$BushoCd" : "";
        $WhereBushoListCourse = $WhereBushoList ? " AND 部署ＣＤ IN ($BushoList)" : "";
        $WhereCourseCd = !!$CourseCd && (!!$WhereBushoCourse || $WhereBushoListCourse)
            ? " AND TM.得意先ＣＤ IN (
                    SELECT 得意先ＣＤ
                    FROM コーステーブル
                    WHERE
                        コースＣＤ=$CourseCd
                    $WhereBushoCourse
                    $WhereBushoListCourse
                )"
            : "";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);

        if (is_numeric($KeyWord) && ctype_digit($KeyWord)) {
            $WhereCustomer = " AND TM.得意先ＣＤ=$KeyWord";

            //得意先ＣＤでの検索
            $ByCustomerSql = "
                SELECT
                    TM.得意先ＣＤ AS Cd,
                    TM.得意先名 AS CdNm,
                    TM.部署CD,
                    TM.得意先名カナ,
                    TM.得意先名略称,
                    TM.住所１,
                    TM.電話番号１,
                    TM.ＦＡＸ１,
                    TM.備考１,
                    TM.備考２,
                    TM.備考３,
                    TM.売掛現金区分,
                    TM.支払方法１,
                    TM.締日１,
                    BM.部署名
                FROM 得意先マスタ TM
                LEFT JOIN 部署マスタ BM
                    ON TM.部署CD = BM.部署CD
                WHERE 0=0
                $WhereBusho
                $WhereBushoList
                $WhereCustomer
            ";

            $stmt = $pdo->query($ByCustomerSql);
            $Result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($Result) == 1) {
                $pdo = null;
                return response()->json($Result);
            }
        }

        $CountSql = "
            SELECT
                COUNT(TM.得意先ＣＤ) AS CNT
            FROM 得意先マスタ TM
            WHERE 0=0
            $WhereBusho
            $WhereBushoList
            $WhereKeyWord
            $WhereCourseCd
        ";

        $stmt = $pdo->query($CountSql);
        $Result = $stmt->fetch();
        // $Result = DB::select($CountSql);

        $Count = $Result["CNT"];
        $CountMax = $request->CountMax ?? 100;
        $SelectTop = !!$request->NoLimit ? "" : ($Count > $CountMax ? "TOP $CountMax" : "");

        $UserBushoCd = $request->UserBushoCd ?? Auth::user()->部署->部署CD ?? 99999;

        $sql = "
            WITH 部署ソート AS (
                SELECT
                    *
                    ,IIF(
                        部署CD=$UserBushoCd,
                        0,
                        CASE 部署CD
                            WHEN 101 THEN 1
                            WHEN 201 THEN 2
                            WHEN 301 THEN 3
                            WHEN 401 THEN 4
                            WHEN 901 THEN 5
                            WHEN 701 THEN 6
                            WHEN 601 THEN 7
                            WHEN 0 THEN 9999
                            ELSE 部署CD
                        END
                    ) AS ソート
                FROM
                    部署マスタ
            )
            SELECT $SelectTop
                TM.得意先ＣＤ AS Cd,
                TM.得意先名 AS CdNm,
                TM.部署CD,
                TM.得意先名カナ,
                TM.得意先名略称,
                TM.住所１,
                TM.電話番号１,
                TM.ＦＡＸ１,
                TM.備考１,
                TM.備考２,
                TM.備考３,
                TM.売掛現金区分,
                TM.支払方法１,
                TM.締日１,
                BM.部署名
                $SelectCourseCd
            FROM 得意先マスタ TM
            LEFT OUTER JOIN 部署ソート BM
                ON TM.部署CD = BM.部署CD
            WHERE 0=0
            $WhereBusho
            $WhereBushoList
            $WhereKeyWord
            $WhereCourseCd
            ORDER BY
                ISNULL(BM.ソート, 9999),
                TM.得意先ＣＤ
        ";

        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        return response()->json(['Data' => $DataList, 'CountConstraint' => !!$SelectTop ? $CountMax : null, 'ActualCounts' => $Count ]);
    }

    /**
     * GetCustomerAbbListForSelect
     */
    public function GetCustomerAbbListForSelect($request)
    {
        $BushoCd = $request->bushoCd ?? $request->BushoCd;
        $KeyWord = $request->KeyWord;
        $TelNo = !!$KeyWord ? str_replace('-', '', $KeyWord) : '';
        $BushoArray = $request->BushoArray;
        $WhereBushoList = "";
        if ($BushoArray != null && is_array($BushoArray) && 0 < count($BushoArray)) {
            $BushoList = implode(',', $BushoArray);
            $WhereBushoList = " AND TM.部署ＣＤ IN ($BushoList)";
        }

        $WhereBusho = $BushoCd ? " AND TM.部署ＣＤ=$BushoCd" : "";
        $WhereKeyWord = $KeyWord
            ? " AND (
                    TM.得意先名 LIKE '%$KeyWord%' OR
                    TM.備考１ LIKE '$KeyWord%' OR
                    TM.備考２ LIKE '$KeyWord%' OR
                    TM.備考３ LIKE '$KeyWord%' OR
                    TM.電話番号１ LIKE '$KeyWord%' OR
                    TM.電話番号１ LIKE '$TelNo%'
                )"
            : "";

        $CourseCd = $request->CourseCd;
        $SelectCourseCd = !!$BushoCd && !!$CourseCd ? ", $CourseCd AS コースＣＤ" : "";
        $WhereBushoCourse = $WhereBusho ? " AND 部署ＣＤ=$BushoCd" : "";
        $WhereBushoListCourse = $WhereBushoList ? " AND 部署ＣＤ IN ($BushoList)" : "";
        $WhereCourseCd = !!$CourseCd && (!!$WhereBushoCourse || $WhereBushoListCourse)
            ? " AND TM.得意先ＣＤ IN (
                    SELECT 得意先ＣＤ
                    FROM コーステーブル
                    WHERE
                        コースＣＤ=$CourseCd
                    $WhereBushoCourse
                    $WhereBushoListCourse
                )"
            : "";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);

        if (is_numeric($KeyWord) && ctype_digit($KeyWord)) {
            $WhereCustomer = " AND TM.得意先ＣＤ=$KeyWord";

            //得意先ＣＤでの検索
            $ByCustomerSql = "
                SELECT
                    TM.得意先ＣＤ AS Cd,
                    TM.得意先名,
                    TM.部署CD,
                    TM.得意先名カナ,
                    TM.得意先名略称 AS CdNm,
                    TM.住所１,
                    TM.電話番号１,
                    TM.ＦＡＸ１,
                    TM.備考１,
                    TM.備考２,
                    TM.備考３,
                    TM.売掛現金区分,
                    TM.締日１,
                    BM.部署名
                FROM 得意先マスタ TM
                LEFT JOIN 部署マスタ BM
                    ON TM.部署CD = BM.部署CD
                WHERE 0=0
                $WhereBusho
                $WhereBushoList
                $WhereCustomer
            ";

            $stmt = $pdo->query($ByCustomerSql);
            $Result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($Result) == 1) {
                $pdo = null;
                return response()->json($Result);
            }
        }

        $CountSql = "
            SELECT
                COUNT(TM.得意先ＣＤ) AS CNT
            FROM 得意先マスタ TM
            WHERE 0=0
            $WhereBusho
            $WhereBushoList
            $WhereKeyWord
            $WhereCourseCd
        ";

        $stmt = $pdo->query($CountSql);
        $Result = $stmt->fetch();
        // $Result = DB::select($CountSql);

        $Count = $Result["CNT"];
        $CountMax = $request->CountMax ?? 100;
        $SelectTop = !!$request->NoLimit ? "" : ($Count > $CountMax ? "TOP $CountMax" : "");

        $UserBushoCd = $request->UserBushoCd ?? Auth::user()->部署->部署CD ?? 99999;

        $sql = "
            WITH 部署ソート AS (
                SELECT
                    *
                    ,IIF(
                        部署CD=$UserBushoCd,
                        0,
                        CASE 部署CD
                            WHEN 101 THEN 1
                            WHEN 201 THEN 2
                            WHEN 301 THEN 3
                            WHEN 401 THEN 4
                            WHEN 901 THEN 5
                            WHEN 701 THEN 6
                            WHEN 601 THEN 7
                            WHEN 0 THEN 9999
                            ELSE 部署CD
                        END
                    ) AS ソート
                FROM
                    部署マスタ
            )
            SELECT $SelectTop
                TM.得意先ＣＤ AS Cd,
                TM.得意先名,
                TM.部署CD,
                TM.得意先名カナ,
                TM.得意先名略称 AS CdNm,
                TM.住所１,
                TM.電話番号１,
                TM.ＦＡＸ１,
                TM.備考１,
                TM.備考２,
                TM.備考３,
                TM.売掛現金区分,
                TM.締日１,
                BM.部署名
                $SelectCourseCd
            FROM 得意先マスタ TM
            LEFT OUTER JOIN 部署ソート BM
                ON TM.部署CD = BM.部署CD
            WHERE 0=0
            $WhereBusho
            $WhereBushoList
            $WhereKeyWord
            $WhereCourseCd
            ORDER BY
                ISNULL(BM.ソート, 9999),
                TM.得意先ＣＤ
        ";

        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        return response()->json(['Data' => $DataList, 'CountConstraint' => !!$SelectTop ? $CountMax : null, 'ActualCounts' => $Count ]);
    }

    /**
     * GetProductListForSelect
     */
    public function GetProductListForSelect($request)
    {
        $CustomerCd = $request->CustomerCd;
        $CustomerCd = isset($CustomerCd) ? $CustomerCd : "NULL";
        $TargetDate = $request->TargetDate;
        $TargetDate = isset($TargetDate) ? "'$TargetDate'" : "NULL";

        $ExceptNull = $request->ExceptNull;
        $WhereExceptNull = !!$ExceptNull ? "AND ISNULL(TTT.単価, TT.単価) IS NOT NULL" : "";

        $sql = "
            SELECT
                MP.商品ＣＤ AS Cd,
                MP.商品名 AS CdNm,
                MP.商品区分,
                MP.売価単価,
                ISNULL(TTT.単価, TT.単価) AS 得意先単価,
                KK.各種名称
            FROM 商品マスタ MP
            LEFT OUTER JOIN 各種テーブル KK
                ON MP.商品区分 = KK.行NO
                AND KK.各種CD = '14'
            LEFT OUTER JOIN 得意先単価マスタ TT
                ON  TT.商品ＣＤ=MP.商品ＣＤ
                AND TT.得意先ＣＤ=$CustomerCd
            LEFT OUTER JOIN (
                SELECT DISTINCT
                    得意先ＣＤ,
                    商品ＣＤ,
                    FIRST_VALUE(単価) OVER(PARTITION BY 得意先ＣＤ, 商品ＣＤ ORDER BY 適用開始日 DESC) AS 単価
                FROM
                    得意先単価適用マスタ
                WHERE
                    適用開始日 <= $TargetDate
            ) TTT
                ON  TTT.商品ＣＤ=MP.商品ＣＤ
                AND TTT.得意先ＣＤ=$CustomerCd
            WHERE
                0=0
                --MP.売価単価 > 0
            $WhereExceptNull
        ";
        $ProductList = DB::select($sql);

        return response()->json($ProductList);
    }
    /**
     * GetProductListForSelect(得意先単価マスタ新を参照する)
     */
    public function GetProductListForSelectNew($request)
    {
        $CustomerCd = $request->CustomerCd;
        $CustomerCd = isset($CustomerCd) ? $CustomerCd : "NULL";
        $TargetDate = $request->TargetDate;
        $TargetDate = isset($TargetDate) ? "'$TargetDate'" : "NULL";

        $ExceptNull = $request->ExceptNull;
        //$WhereExceptNull = !!$ExceptNull ? "AND ISNULL(TTT.単価, TT.単価) IS NOT NULL" : "";
        $WhereExceptNull = !!$ExceptNull ? "AND MTT.単価 IS NOT NULL" : "";

        /*
        $sql = "
            SELECT
                MP.商品ＣＤ AS Cd,
                MP.商品名 AS CdNm,
                MP.商品区分,
                MP.売価単価,
                ISNULL(TTT.単価, TT.単価) AS 得意先単価,
                KK.各種名称
            FROM 商品マスタ MP
            LEFT OUTER JOIN 各種テーブル KK
                ON MP.商品区分 = KK.行NO
                AND KK.各種CD = '14'
            LEFT OUTER JOIN 得意先単価マスタ TT
                ON  TT.商品ＣＤ=MP.商品ＣＤ
                AND TT.得意先ＣＤ=$CustomerCd
            LEFT OUTER JOIN (
                SELECT DISTINCT
                    得意先ＣＤ,
                    商品ＣＤ,
                    FIRST_VALUE(単価) OVER(PARTITION BY 得意先ＣＤ, 商品ＣＤ ORDER BY 適用開始日 DESC) AS 単価
                FROM
                    得意先単価適用マスタ
                WHERE
                    適用開始日 <= $TargetDate
            ) TTT
                ON  TTT.商品ＣＤ=MP.商品ＣＤ
                AND TTT.得意先ＣＤ=$CustomerCd
            WHERE
                0=0
                --MP.売価単価 > 0
            $WhereExceptNull
        ";
        */

        $sql = "
            SELECT
                MP.商品ＣＤ AS Cd,
                MP.商品名 AS CdNm,
                MP.商品区分,
                MP.売価単価,
                MTT.単価 AS 得意先単価,
                KK.各種名称
            FROM 商品マスタ MP
            LEFT OUTER JOIN 各種テーブル KK
                ON MP.商品区分 = KK.行NO
                AND KK.各種CD = '14'
            LEFT OUTER JOIN
                (
                    SELECT
                        *
                    FROM (
                        SELECT
                            *
                            , RANK() OVER(PARTITION BY 得意先ＣＤ, 商品ＣＤ ORDER BY 適用開始日 DESC) AS RNK
                        FROM
                            得意先単価マスタ新
                        WHERE
                            得意先ＣＤ=$CustomerCd
                        AND 適用開始日 <= $TargetDate
                    ) TT
                    WHERE
                        RNK = 1
			  ) MTT
                ON  MTT.商品ＣＤ=MP.商品ＣＤ
                AND MTT.得意先ＣＤ=$CustomerCd
            WHERE
                0=0
                --MP.売価単価 > 0
            $WhereExceptNull
        ";

        $ProductList = DB::select($sql);

        return response()->json($ProductList);
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
        $query = 金融機関名称::query();

        $BankList = collect($query->get())
            ->map(function ($Bank) {
                $vm = (object) $Bank;
                return $vm;
            })
            ->values();

        return response()->json($BankList);
    }

    /**
     * GetBankBranchListForMaint
     */
    public function GetBankBranchListForMaint($request)
    {
        $BankCd = $request->BankCd;

        if (!is_numeric($BankCd)) {
            return [];
        }

        $query = 金融機関支店名称::query()
            ->when(
                $BankCd,
                function ($q) use ($BankCd) {
                    return $q->where('銀行CD', $BankCd);
                });

        $BankBranchList = collect($query->get())
            ->map(function ($BankBranch) {
                $vm = (object) $BankBranch;
                return $vm;
            })
            ->values();

        return response()->json($BankBranchList);
    }

    /**
     * GetCustomerListForMaint
     */
    public function GetCustomerListForMaint($request)
    {
        $BushoCd = $request->bushoCd ?? $request->BushoCd;
        $CustomerCd = $request->CustomerCd;

        $WhereBusho = $BushoCd ? " AND TOK.部署CD=$BushoCd" : "";
        $WhereCustomer = $CustomerCd ? " AND TOK.得意先ＣＤ=$CustomerCd" : "";

        $sql = "
            WITH CT_DISTINCT AS (
                SELECT
                    *
                FROM (
                    SELECT
                        CT.部署ＣＤ
                        ,CT.得意先ＣＤ
                        ,CT.コースＣＤ
                        ,CM.コース区分
                        ,CM.コース名
                        ,MIN(CM.コース区分) OVER(PARTITION BY CM.部署CD, CT.得意先ＣＤ)  AS 最小コース区分
                    FROM コーステーブル CT
                    LEFT OUTER JOIN コースマスタ CM
                        ON CM.部署CD=CT.部署CD AND CT.コースＣＤ=CM.コースＣＤ
                ) CT_MIN
                WHERE
                    コース区分 = 最小コース区分
            )
            SELECT
                TOK.部署CD
                ,BM.部署名
                ,TOK.得意先CD
                ,TOK.得意先名
                ,TOK.得意先名カナ
                ,TOK.状態区分
                ,TOK.承認日
                ,TOK.承認者ＣＤ
                ,TOK.電話番号１
                ,CT_DISTINCT.コースＣＤ
                ,CT_DISTINCT.コース名
                ,CT_DISTINCT.コース区分
            FROM 得意先マスタ TOK
            LEFT OUTER JOIN 部署マスタ BM
                ON BM.部署CD=TOK.部署CD
            LEFT OUTER JOIN CT_DISTINCT
                ON CT_DISTINCT.部署CD=TOK.部署CD AND CT_DISTINCT.得意先ＣＤ=TOK.得意先ＣＤ
            WHERE 0=0
            $WhereBusho
            $WhereCustomer
        ";

        $DataList = DB::select($sql);

        return response()->json(['Data'=>$DataList, 'CountConstraint'=> false]);
    }

    /**
     * GetCustomerListForMaintDistinct
     */
    public function GetCustomerListForMaintDistinct($request)
    {
        $BushoCd = $request->bushoCd ?? $request->BushoCd;
        $CustomerCd = $request->CustomerCd;

        $WhereBusho = $BushoCd ? " AND TOK.部署CD=$BushoCd" : "";
        $WhereCustomer = $CustomerCd ? " AND TOK.得意先ＣＤ=$CustomerCd" : "";

        $sql = "
            WITH CT_DISTINCT AS (
                SELECT
                    *
                FROM (
                    SELECT
                        CT.部署ＣＤ
                        ,CT.得意先ＣＤ
                        ,CT.コースＣＤ
                        ,CM.コース区分
                        ,CM.コース名
                        ,MIN(CM.コース区分) OVER(PARTITION BY CM.部署CD, CT.得意先ＣＤ)  AS 最小コース区分
                    FROM コーステーブル CT
                    LEFT OUTER JOIN コースマスタ CM
                        ON CM.部署CD=CT.部署CD AND CT.コースＣＤ=CM.コースＣＤ
                ) CT_MIN
                WHERE
                    コース区分 = 最小コース区分
            )
            SELECT DISTINCT
                TOK.部署CD
                ,BM.部署名
                ,TOK.得意先CD
                ,TOK.得意先名
                ,TOK.得意先名カナ
                ,TOK.状態区分
                ,TOK.承認日
                ,TOK.承認者ＣＤ
                ,TOK.電話番号１
                ,CT_DISTINCT.コースＣＤ
                ,CT_DISTINCT.コース名
                ,CT_DISTINCT.コース区分
            FROM 得意先マスタ TOK
            LEFT OUTER JOIN 部署マスタ BM
                ON BM.部署CD=TOK.部署CD
            LEFT OUTER JOIN CT_DISTINCT
                ON CT_DISTINCT.部署CD=TOK.部署CD AND CT_DISTINCT.得意先ＣＤ=TOK.得意先ＣＤ
            WHERE 0=0
            $WhereBusho
            $WhereCustomer
        ";

        $DataList = DB::select($sql);

        return response()->json(['Data'=>$DataList, 'CountConstraint'=> false]);
    }

    /**
     * GetCustomerList
     */
    public function GetCustomerList($request)
    {
        $BushoCd = $request->bushoCd ?? $request->BushoCd;
        $CustomerCd = $request->CustomerCd;

        $WhereBusho = $BushoCd ? " AND TM.部署CD=$BushoCd" : "";
        $WhereCustomer = $CustomerCd ? " AND TM.得意先ＣＤ=$CustomerCd" : "";

        $sql = "
            SELECT
                TM.*,
                BM.部署名
            FROM 得意先マスタ TM
            LEFT JOIN 部署マスタ BM
                ON TM.部署CD = BM.部署CD
            WHERE 0=0
            $WhereBusho
            $WhereCustomer
            ";

        $DataList = DB::select($sql);

        return response()->json(['Data'=>$DataList, 'CountConstraint'=> false]);
    }

    /**
     * GetCustomerHistoryList
     */
    public function GetCustomerHistoryList($request)
    {
        $CustomerCd = $request->CustomerCd;

        $WhereCustomer = $CustomerCd ? " AND 得意先ＣＤ=$CustomerCd" : "";

        $sql = "
            SELECT
                Q_状態区分.状態区分 AS 状態,
                CONVERT(VARCHAR(30), MAIN.承認日, 111) AS 承認日,
                Q_承認者.担当者名                      AS 承認者,
                Q_状態理由区分.状態理由,
                CONVERT(VARCHAR(30), MAIN.失客日, 111) AS 失客日,
                Q_営業担当者.担当者名                  AS 営業担当者,
                CONVERT(VARCHAR(30), MAIN.登録日, 111) AS 処理日,
                Q_登録担当者.担当者名                  AS 登録担当者

            FROM 得意先履歴テーブル MAIN

            LEFT OUTER JOIN (SELECT 行NO, 各種略称 AS 状態区分 FROM 各種テーブル WHERE 各種CD = 12) AS Q_状態区分
            ON MAIN.状態区分 = Q_状態区分.行NO

            LEFT OUTER JOIN (SELECT 行NO, 各種略称 AS 状態理由 FROM 各種テーブル WHERE 各種CD = 36) AS Q_状態理由区分
            ON MAIN.失客理由 = Q_状態理由区分.行NO

            LEFT OUTER JOIN (SELECT 担当者CD, 担当者名 FROM 担当者マスタ) AS Q_承認者
            ON MAIN.承認者ＣＤ = Q_承認者.担当者CD

            LEFT OUTER JOIN (SELECT 担当者CD, 担当者名 FROM 担当者マスタ) AS Q_営業担当者
            ON MAIN.営業担当者ＣＤ = Q_営業担当者.担当者CD

            LEFT OUTER JOIN (SELECT 担当者CD, 担当者名 FROM 担当者マスタ) AS Q_登録担当者
            ON MAIN.変更者ＣＤ = Q_登録担当者.担当者CD

            WHERE 0=0
            $WhereCustomer
            ORDER BY 得意先CD, 得意先履歴ID
        ";

        $DataList = DB::select($sql);

        return response()->json(['Data'=>$DataList, 'CountConstraint'=> false]);
    }

    /**
     * GetBunpaisakiList
     */
    public function GetBunpaisakiList($request)
    {
        $CustomerCd = $request->CustomerCd;
        $BushoCd = $request->BushoCd;

        $WhereCustomer = $CustomerCd ? " AND 受注得意先ＣＤ=$CustomerCd" : "";
        $WhereBushoCd = $BushoCd ? " AND 部署CD=$BushoCd" : "";

        $sql = "
            SELECT
                得意先ＣＤ,
                得意先名
            FROM 得意先マスタ

            WHERE 0=0
            $WhereCustomer
            $WhereBushoCd
            ORDER BY 得意先ＣＤ
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
                $vm->住所１ = $course->得意先->住所１;
                $vm->備考１ = $course->得意先->備考１;
                $vm->備考２ = $course->得意先->備考２;
                $vm->備考３ = $course->得意先->備考３;
                $vm->コース名 = $course->コース->コース名;

                unset($vm->得意先);
                unset($vm->コース);

                return $vm;
            })
            ->values();

        return response()->json($CustomerList);
    }

    /**
     * GetCustomerListWithTemp
     */
    public function GetCustomerListWithTemp($request) {
        $BushoCd = $request->bushoCd ?? $request->BushoCd;
        $CourseCd = $request->courseCd ?? $request->CourseCd;
        $MngCd = $request->mngCd ?? $request->MngCd;

        $sql = "
            SELECT
                C.*
                ,得意先マスタ.得意先名
            FROM (
                SELECT
                    部署ＣＤ,
                    コースＣＤ,
                    ＳＥＱ,
                    0 AS 管理ＣＤ,
                    得意先ＣＤ,
                    修正担当者ＣＤ,
                    修正日
                FROM
                    コーステーブル C
                UNION ALL
                SELECT
                    部署ＣＤ,
                    コースＣＤ,
                    ＳＥＱ,
                    管理ＣＤ,
                    得意先ＣＤ,
                    修正担当者ＣＤ,
                    修正日
                FROM
                    コーステーブル一時 C
            ) C
                LEFT JOIN 得意先マスタ
                    ON  得意先マスタ.部署ＣＤ = C.部署ＣＤ
                    AND 得意先マスタ.得意先ＣＤ = C.得意先ＣＤ
            WHERE
                C.部署ＣＤ = $BushoCd
            AND C.コースＣＤ = $CourseCd
            AND C.管理ＣＤ = $MngCd
            ORDER BY
                C.ＳＥＱ
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;
        // $DataList = DB::select($sql);

        return response()->json($DataList);
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

        if (!!$GroupCustomerCd && (!is_numeric($GroupCustomerCd) || !ctype_digit($GroupCustomerCd))) {
            return [];
        }

        $WhereBushoCd = $BushoCd ? " AND M1.部署ＣＤ = $BushoCd" : "";
        $WhereKanaNm = $KanaNm ? " AND M1.得意先名カナ LIKE '%$KanaNm%'" : "";
        $WhereTelNo = $TelNo ? " AND M1.得意先ＣＤ IN (SELECT Tel_CustNo FROM C_TelToCust WHERE Tel_TelNo LIKE '$TelNo%')" : "";
        $WhereOyaOnly = $IsOyaOnly ? " AND (M1.受注得意先ＣＤ = 0 OR M1.受注得意先ＣＤ = M1.得意先ＣＤ)" : "";

        $WhereCourseKbn =  $CourseKbn
                                ? " AND MC.コース区分 = $CourseKbn"
                                : ($TargetDate
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
                                    : "");

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

        //TODO: 高速化対応
        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        // $DataList = DB::select($sql);

        return response()->json($DataList);
    }

    /**
     * GetCourseKbnFromDate
     */
    public function GetCourseKbnFromDate($request)
    {
        return response()->json($this->SearchCourseKbnFromDate($request));
    }

    /**
     * GetCourseKbnFromDateList
     */
    public function GetCourseKbnFromDateList($request)
    {
        $TargetDateList = $request->TargetDateList ?? $request->TargetDateList;

        $ret = collect($TargetDateList)
            ->map(function($date) {
                $param = (object) ["TargetDate" => $date];
                return $this->SearchCourseKbnFromDate($param);
            });

        return response()->json($ret);
    }

    /**
     * SearchCourseKbnFromDate
     */
    public function SearchCourseKbnFromDate($request) {
        $TargetDate = $request->targetDate ?? $request->TargetDate;

        $sql = "
            SELECT
                D.対象日付,
                D.コース区分,
                各種テーブル.各種名称 AS コース区分名
            FROM (
                SELECT
                    '$TargetDate' AS 対象日付,
                    CASE
                        WHEN (SELECT 対象日付 FROM 祝日マスタ WHERE 対象日付 = '$TargetDate') IS NOT NULL THEN 4
                        ELSE
                            CASE DATEPART(WEEKDAY, '$TargetDate')
                                WHEN 1 THEN 3
                                WHEN 7 THEN 2
                                ELSE 1
                            END
                    END AS コース区分
            ) D
                LEFT OUTER JOIN 各種テーブル
                    ON 各種テーブル.行NO = D.コース区分 AND 各種テーブル.各種CD=19
        ";

        $Result = DB::selectOne($sql);

        return $Result;
    }

    /**
     * GetTankaListForMaint
     */
    public function GetTankaList($request)
    {
        return response()->json($this->SearchTankaList($request));
    }

    public function SearchTankaList($request)
    {
        $BushoArray = $request->BushoArray;
        $WhereBushoList = "";
        if ($BushoArray != null && is_array($BushoArray) && 0 < count($BushoArray)) {
            $BushoList = implode(',', $BushoArray);
            $WhereBushoList = " AND TM.部署ＣＤ IN ($BushoList)";
        }

        $CustomerCd = $request->CustomerCd;
        $WhereCustomerCd = !!$CustomerCd ? "AND TT.得意先ＣＤ=$CustomerCd" : "";

        $ProductCd = $request->ProductCd;
        $WhereProductCd = !!$ProductCd ? "AND TT.商品ＣＤ=$ProductCd" : "";

        $TargetDate = $request->TargetDate;
        $OrderTargetDate = !!$TargetDate ? "IIF(TT.適用開始日 <= '$TargetDate', 0, 1)," : "";

        $sql = "
            SELECT
                TT.*
                ,TM.部署ＣＤ
                ,BM.部署名
				,TM.得意先名
                ,SM.商品名
                ,IIF(
                    FIRST_VALUE(TT.適用開始日) OVER(
                        PARTITION BY TT.得意先ＣＤ, TT.商品ＣＤ
                        ORDER BY $OrderTargetDate TT.適用開始日 DESC
                    ) = TT.適用開始日,
                    1, 0
                ) AS 状況
            FROM
                得意先単価マスタ新 TT
				LEFT OUTER JOIN 得意先マスタ TM
                    ON  TM.得意先ＣＤ=TT.得意先ＣＤ
				LEFT OUTER JOIN 部署マスタ BM
                    ON  BM.部署CD=TM.部署ＣＤ
				LEFT OUTER JOIN 商品マスタ SM
                    ON  SM.商品ＣＤ=TT.商品ＣＤ
            WHERE
                0=0
            $WhereBushoList
            $WhereCustomerCd
            $WhereProductCd
            ORDER BY
                TT.得意先ＣＤ,
                TT.商品ＣＤ,
                TT.適用開始日
        ";

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);
        $stmt = $pdo->query($sql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $pdo = null;

        return $DataList;
    }

    /**
     * GetSimeDateList
     */
    public function GetSimeDateList() {
        $sql = "
            SELECT DISTINCT
                締日１ AS 締日
            FROM
                得意先マスタ
            UNION
            SELECT DISTINCT
                締日２ AS 締日
            FROM
                得意先マスタ
        ";

        $Result = collect(DB::select($sql))
            ->map(function ($val) {
                $obj = (object)[];

                $obj->Cd = $val->締日;
                $obj->CdNm = $val->締日 == 99 ? '末日' : ($val->締日.'日締');

                return $obj;
            })
            ->values();

        return response()->json($Result);
    }

    /**
     * SearchCustomerListPartial
     */
    public function SearchCustomerListPartial($request)
    {
        $BushoCd = $request->bushoCd ?? $request->BushoCd;
        $WhereBusho = $BushoCd ? " AND TOK.部署CD=$BushoCd" : "";

        $TelNo = $request->TelNo;
        $WhereTelNo = $TelNo
            ? "
                AND TOK.得意先ＣＤ IN (
					SELECT
						Tel_CustNo
					FROM C_TelToCust
					WHERE
                    REPLACE(Tel_TelNo, '-', '')='$TelNo'
				 )
            "
            : "";

        $Start = $request->Start;
        $Chunk = $request->Chunk;
        $End = $Start + $Chunk - 1;

        $KeyWords = $request->KeyWords ?? $request->Keywords;
        $WhereKeyWord = "";
        if (!!$KeyWords && !!count($KeyWords)) {
            $Conditions = collect($KeyWords)
                ->map(function ($KeyWord) {
                    $Condition = "
                        (
                            TOK.得意先名 LIKE '%$KeyWord%' OR
                            TOK.得意先名カナ LIKE '%$KeyWord%' OR
                            TOK.得意先名略称 LIKE '%$KeyWord%' OR
                            TOK.住所１ LIKE '%$KeyWord%' OR
                            TOK.お届け先住所１ LIKE '%$KeyWord%' OR
                            TOK.電話番号１ LIKE '$KeyWord%' OR
                            TOK.備考１ LIKE '$KeyWord%' OR
                            TOK.備考２ LIKE '$KeyWord%' OR
                            TOK.備考３ LIKE '$KeyWord%'
                        )
                    ";

                    return $Condition;
                })
                ->join(' OR ');

            $WhereKeyWord = " AND ($Conditions)";
        }

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);

        $SearchSql = "
            WITH CT_DISTINCT AS (
                SELECT
                    *
                FROM (
                    SELECT
                        CT.部署ＣＤ
                        ,CT.得意先ＣＤ
                        ,CT.コースＣＤ
                        ,CM.コース区分
                        ,CM.コース名
                        ,MIN(CM.コース区分) OVER(PARTITION BY CM.部署CD, CT.得意先ＣＤ)  AS 最小コース区分
                    FROM コーステーブル CT
                    LEFT OUTER JOIN コースマスタ CM
                        ON CM.部署CD=CT.部署CD AND CT.コースＣＤ=CM.コースＣＤ
                ) CT_MIN
                WHERE
                    コース区分 = 最小コース区分
            )
            SELECT
            *
            FROM (
                SELECT
                    TOK.部署CD
                    ,BM.部署名
                    ,TOK.得意先ＣＤ
                    ,TOK.得意先名
                    ,CT_DISTINCT.コースＣＤ
                    ,CT_DISTINCT.コース名
                    ,TOK.得意先ＣＤ AS Cd
                    ,TOK.得意先名 AS CdNm
					,ROW_NUMBER() OVER (ORDER BY TOK.得意先ＣＤ) AS ROWNUM
                FROM 得意先マスタ TOK
                    LEFT OUTER JOIN 部署マスタ BM
                        ON BM.部署CD=TOK.部署CD
                    LEFT OUTER JOIN CT_DISTINCT
                        ON CT_DISTINCT.部署CD=TOK.部署CD AND CT_DISTINCT.得意先ＣＤ=TOK.得意先ＣＤ
                WHERE 0=0
                $WhereBusho
                $WhereTelNo
                $WhereKeyWord
            ) AS T
            WHERE
                ROWNUM BETWEEN $Start AND $End
            ORDER BY
                ROWNUM
        ";

        // $DataList = DB::select($SearchSql);
        $stmt = $pdo->query($SearchSql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $CountSql = "
            SELECT
                COUNT(TOK.部署CD) AS CNT
            FROM 得意先マスタ TOK
            WHERE 0=0
            $WhereBusho
            $WhereTelNo
            $WhereKeyWord
        ";
        $stmt = $pdo->query($CountSql);
        $Count = $stmt->fetch()["CNT"];

        $pdo = null;

        return response()->json([
            [
                "End" => $End,
                "Count" => $Count,
                "Result" => $DataList,
            ]
        ]);
    }

    /**
     * CheckTelNo
     */
    public function CheckTelNo($request)
    {
        $TelNo = $request->TelNo;

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);

        $CheckRejectSql = "
            SELECT
                *
            FROM
                非顧客電話番号マスタ
            WHERE
                電話番号 = '$TelNo'
        ";

        $stmt = $pdo->query($CheckRejectSql);
        $Rejects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($Rejects) > 0) {
            return response()->json([
                "Reject" => true,
            ]);
        }

        $CheckRegistSql = "
            SELECT DISTINCT
                REPLACE(CT.Tel_TelNo, '-', '') AS Tel_TelNo
                ,CT.Tel_CustNo
                ,CT.Tel_RepFlg
                ,CT.Tel_DelFlg
                ,CT.Tel_NewDate
                ,CT.Tel_UpdDate
                ,得意先マスタ.部署CD
                ,得意先マスタ.得意先ＣＤ
                ,得意先マスタ.得意先名
            FROM
                C_TelToCust CT
                LEFT OUTER JOIN 得意先マスタ
                    ON 得意先マスタ.得意先ＣＤ = CT.Tel_CustNo
            WHERE
            REPLACE(CT.Tel_TelNo, '-', '') = '$TelNo'
        ";
        $stmt = $pdo->query($CheckRegistSql);
        $Regists = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pdo = null;

        return response()->json([
            "Reject" => false,
            "Unregist" => count($Regists) == 0,
            "Unique" => count($Regists) == 1,
            "CustomerInfo" => count($Regists) == 1 ? $Regists[0] : null,
        ]);
    }

    /**
     * InsertTelNo
     */
    public function InsertTelNo($request)
    {
        $TelNo = $request->TelNo;
        $CustomerCd = $request->CustomerCd;
        $EditUser = $request->EditUser;
        $date = Carbon::now()->format('Y/m/d');

        DB::beginTransaction();

        try {
            $model = [
                'Tel_TelNo' => $TelNo,
                'Tel_CustNo' => $CustomerCd,
                'Tel_RepFlg' => 0,
                'Tel_DelFlg' => 0,
                'Tel_NewDate' => $date,
                'Tel_UpdDate' => $date,
            ];

            CTelToCust::insert($model);

            $customer = 得意先マスタ::query()
                ->where("得意先ＣＤ", $CustomerCd)
                ->first();

            if (!isset($customer->電話番号１)) {
                得意先マスタ::query()
                    ->where("得意先ＣＤ", $CustomerCd)
                    ->update(['電話番号１' => $TelNo, '修正担当者ＣＤ' => $EditUser, '修正日' => Carbon::now()->format('Y-m-d H:i:s')]);
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return;
    }

    /**
     * SetNonCustomer
     */
    public function SetNonCustomer($request)
    {
        $TelNo = $request->TelNo;
        $EditUser = $request->EditUser;
        $date = Carbon::now()->format('Y-m-d H:i:s');

        DB::beginTransaction();

        try {
            $model = [
                "電話番号" => $TelNo,
                "修正担当者ＣＤ" => $EditUser,
                "修正日" => $date,
            ];

            非顧客電話番号マスタ::insert($model);

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return;
    }

    /**
     * SearchWebOrderList
     */
    public function SearchWebOrderList($request)
    {
        $TargetDate = $request->TargetDate;
        $WhereTargetDate = $TargetDate ? "AND X.配送日='$TargetDate'" : "";

        $BushoCd = $request->bushoCd ?? $request->BushoCd;
        $WhereBusho = $BushoCd ? "AND TOK.部署CD=$BushoCd" : "";

        $UnRegisted = $request->UnRegisted;
        $HavingUnRegisted = $UnRegisted == '1' ? "AND SUM(X.確認済) = 0" : "";

        $Timeout = $request->Timeout;
        $HavingTimeout = $Timeout == '1' ? "AND CAST(X.配送日 + ' ' + X.締切時刻 AS datetime) < GETDATE()" : "";

        $Start = $request->Start ?? 1;
        $Chunk = $request->Chunk ?? 1000000;
        $End = $Start + $Chunk - 1;

        $KeyWords = $request->KeyWords ?? $request->Keywords;
        $WhereKeyWord = "";
        if (!!$KeyWords && !!count($KeyWords)) {
            $Conditions = collect($KeyWords)
                ->map(function ($KeyWord) {
                    $Condition = "
                        (
                            TOK.得意先名 LIKE '%$KeyWord%' OR
                            TOK.得意先名カナ LIKE '%$KeyWord%' OR
                            TOK.得意先名略称 LIKE '%$KeyWord%' OR
                            TOK.住所１ LIKE '%$KeyWord%' OR
                            TOK.お届け先住所１ LIKE '%$KeyWord%' OR
                            TOK.電話番号１ LIKE '$KeyWord%' OR
                            TOK.備考１ LIKE '$KeyWord%' OR
                            TOK.備考２ LIKE '$KeyWord%' OR
                            TOK.備考３ LIKE '$KeyWord%'
                        )
                    ";

                    return $Condition;
                })
                ->join(' OR ');

            $WhereKeyWord = " AND ($Conditions)";
        }

        $pdo = DB::getPdo();

        $SearchSql = "
            SELECT
                *
            FROM (
                SELECT
                    X.Web受注ID
                    ,X.配送日
                    ,X.注文日時
					,X.部署CD
					,X.部署名
					,X.Web得意先ＣＤ
					,X.Web得意先名
                    ,STRING_AGG(X.得意先ＣＤ, '/') AS Cd
                    ,STRING_AGG(X.得意先名, '/') AS CdNm
					,STRING_AGG(IIF(X.売掛現金区分 = 0, X.得意先ＣＤ, NULL), '/')  AS 得意先ＣＤ_現金
					,STRING_AGG(IIF(X.売掛現金区分 = 0, X.得意先名, NULL), '/')  AS 得意先名_現金
					,STRING_AGG(IIF(X.売掛現金区分 = 0, X.電話番号１, NULL), '/')  AS 電話番号_現金
					,STRING_AGG(IIF(X.売掛現金区分 = 1, X.得意先ＣＤ, NULL), '/')  AS 得意先ＣＤ_掛売
					,STRING_AGG(IIF(X.売掛現金区分 = 1, X.得意先名, NULL), '/')  AS 得意先名_掛売
					,STRING_AGG(IIF(X.売掛現金区分 = 1, X.電話番号１, NULL), '/')  AS 電話番号_掛売
                    ,IIF(SUM(X.確認済) > 0, 1, 0) AS 確認
					,X.締切時刻
            		,ROW_NUMBER() OVER (ORDER BY X.Web受注ID) AS ROWNUM
                FROM (
                    SELECT DISTINCT
                        WEB.Web受注ID
                        ,WEB.Web得意先ＣＤ
						,WEBTOK.Web得意先名
                        ,WEB.配送日
                        ,WEB.注文日時
                        ,TOK.得意先ＣＤ
                        ,TOK.得意先名
						,TOK.売掛現金区分
                        ,TOK.部署CD
                        ,BSH.部署名
						,TOK.電話番号１
                        ,IIF(CHU.明細行Ｎｏ IS NULL, 0, 1) AS	確認済
						,WEBTOKDL.締切時刻
                    FROM
                        Web受注データ WEB
                        INNER JOIN Web受注得意先マスタ WEBTOK
                            ON  WEBTOK.Web得意先ＣＤ=WEB.Web得意先ＣＤ
                        INNER JOIN 得意先マスタ TOK
                            ON  TOK.得意先ＣＤ=WEBTOK.得意先ＣＤ
                        INNER JOIN 部署マスタ BSH
                            ON  BSH.部署CD=TOK.部署CD
                        LEFT OUTER JOIN Web受注得意先締切時刻マスタ WEBTOKDL
                            ON  WEBTOKDL.Web得意先ＣＤ=WEB.Web得意先ＣＤ
							AND WEBTOKDL.曜日=DATEPART(WEEKDAY, WEB.配送日)
                        LEFT OUTER JOIN 注文データ CHU
                            ON  CHU.配送日=WEB.配送日
                            AND CHU.得意先ＣＤ=TOK.得意先ＣＤ
                            AND CHU.Web受注ID=WEB.Web受注ID
                            AND CHU.注文区分=1
                    WHERE
                        0=0
                    $WhereBusho
                    $WhereKeyWord
                ) X
                WHERE
                    0=0
                $WhereTargetDate
                GROUP BY
                    X.Web受注ID
					,X.Web得意先ＣＤ
					,X.Web得意先名
                    ,X.配送日
                    ,X.注文日時
					,X.部署CD
					,X.部署名
                    ,X.締切時刻
				HAVING
					0=0
                    $HavingUnRegisted
                    $HavingTimeout
            ) G
            WHERE
                ROWNUM BETWEEN $Start AND $End
            ORDER BY
                ROWNUM
        ";

        // $DataList = DB::select($SearchSql);
        $stmt = $pdo->query($SearchSql);
        $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // $CountSql = "
        //     SELECT
        //         COUNT(Web受注ID) AS CNT
        //     FROM (
        //         SELECT
        //             X.Web受注ID
        //             ,X.配送日
		// 			,X.部署CD
		// 			,X.部署名
        //             ,STRING_AGG (X.得意先ＣＤ, '/') AS 得意先ＣＤ
        //             ,STRING_AGG (X.得意先名, '/') AS 得意先名
        //             ,IIF(SUM(X.確認済) > 0, 1, 0) AS 確認
        //     		,ROW_NUMBER() OVER (ORDER BY X.Web受注ID) AS ROWNUM
        //         FROM (
        //             SELECT DISTINCT
        //                 WEB.Web受注ID
        //                 ,WEB.Web得意先ＣＤ
		// 				,WEBTOK.Web得意先名
        //                 ,WEB.配送日
        //                 ,WEB.注文日時
        //                 ,TOK.得意先ＣＤ
        //                 ,TOK.得意先名
		// 				,TOK.売掛現金区分
        //                 ,TOK.部署CD
        //                 ,BSH.部署名
		// 				,TOK.電話番号１
        //                 ,IIF(CHU.明細行Ｎｏ IS NULL, 0, 1) AS	確認済
        //             FROM
        //                 Web受注データ WEB
        //                 INNER JOIN Web受注得意先マスタ WEBTOK
        //                     ON  WEBTOK.Web得意先ＣＤ=WEB.Web得意先ＣＤ
        //                 INNER JOIN 得意先マスタ TOK
        //                     ON  TOK.得意先ＣＤ=WEBTOK.得意先ＣＤ
        //                 INNER JOIN 部署マスタ BSH
        //                     ON  BSH.部署CD=TOK.部署CD
        //                 LEFT OUTER JOIN 注文データ CHU
        //                     ON  CHU.配送日=WEB.配送日
        //                     AND CHU.得意先ＣＤ=TOK.得意先ＣＤ
        //                     AND CHU.注文区分=0
        //             WHERE
        //                 0=0
        //             $WhereBusho
        //             $WhereKeyWord
        //         ) X
        //         WHERE
        //             0=0
        //         $WhereTargetDate
        //         GROUP BY
        //             X.Web受注ID
        //             ,X.配送日
		// 			,X.部署CD
		// 			,X.部署名
        //         $HavingUnRegisted
        //     ) G
        // ";
        // $stmt = $pdo->query($CountSql);
        // $Count = $stmt->fetch()["CNT"];

        $pdo = null;

        return response()->json([
            [
                "End" => $End,
                "Count" => count($DataList),
                "Result" => $DataList,
            ]
        ]);
    }

    /**
     * SetWebOrderDummy
     */
    public function SetWebOrderDummy() {
        $DeliveryDate = Carbon::now()->format('Y-m-d');
        $OrderDate = Carbon::now()->format('Y-m-d H:i:s');

        $Current = Web受注データ::query()
            ->where('Web得意先ＣＤ', 'Web-01')
            ->where('配送日', "$DeliveryDate")
            ->orderByRaw('配送日 DESC')
            ->get();

        if (count($Current) != 0) {
            $c = new Carbon($Current[0]->配送日);
            $d = $c->addDays(1);
            $DeliveryDate = $d->format('Y-m-d');
            $OrderDate = $d->format('Y-m-d H:i:s');
        }

        $WebOrderId = Web受注データ::query()
            ->max('Web受注ID') + 1;

        $OrderId = Web受注データ利用者情報::query()
            ->max('注文ID') + 1;
        $OrderId2 = $OrderId + 1;

        $InfoId = Web受注データ利用者別詳細::query()
            ->max('注文情報ID') + 1;
        $InfoId2 = $InfoId + 1;

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';

        $pdo = new PDO($dsn, $user, $password);

        $pdo->exec(
            "
                INSERT [dbo].[Web受注データ] ([Web受注ID], [Web得意先ＣＤ], [配送日], [注文日時], [修正担当者ＣＤ], [修正日])
                    VALUES ($WebOrderId, N'Web-01', '$DeliveryDate', '$OrderDate', 9999, GETDATE())
            "
        );
        $pdo->exec(
            "
                INSERT [dbo].[Web受注データ利用者情報] ([Web受注ID], [注文ID], [利用者ID], [利用者CD], [備考ID], [修正担当者ＣＤ], [修正日])
                    VALUES ($WebOrderId, $OrderId, 1, N'Web-01-01', NULL, 9999, GETDATE())
            "
        );
        $pdo->exec(
            "
                INSERT [dbo].[Web受注データ利用者情報] ([Web受注ID], [注文ID], [利用者ID], [利用者CD], [備考ID], [修正担当者ＣＤ], [修正日])
                    VALUES ($WebOrderId, $OrderId2, 2, N'Web-01-02', 1, 9999, GETDATE())
            "
        );
        $pdo->exec(
            "
                INSERT [dbo].[Web受注データ利用者別詳細] ([Web受注ID], [注文ID], [注文情報ID], [注文日時], [商品ＣＤ], [単価], [現金個数], [現金金額], [掛売個数], [掛売金額], [届け先ID], [修正担当者ＣＤ], [修正日])
                    VALUES ($WebOrderId, $OrderId, $InfoId, '$OrderDate', 10, 320, CAST(1 AS Numeric(18, 0)), CAST(320 AS Numeric(18, 0)), CAST(0 AS Numeric(18, 0)), CAST(0 AS Numeric(18, 0)), 2, 1, GETDATE())
            "
        );
        $pdo->exec(
            "
                INSERT [dbo].[Web受注データ利用者別詳細] ([Web受注ID], [注文ID], [注文情報ID], [注文日時], [商品ＣＤ], [単価], [現金個数], [現金金額], [掛売個数], [掛売金額], [届け先ID], [修正担当者ＣＤ], [修正日])
                    VALUES ($WebOrderId, $OrderId2, $InfoId, '$OrderDate', 11, 360, CAST(2 AS Numeric(18, 0)), CAST(720 AS Numeric(18, 0)), CAST(0 AS Numeric(18, 0)), CAST(0 AS Numeric(18, 0)), 1, 9999, GETDATE())
            "
        );
        $pdo->exec(
            "
                INSERT [dbo].[Web受注データ利用者別詳細] ([Web受注ID], [注文ID], [注文情報ID], [注文日時], [商品ＣＤ], [単価], [現金個数], [現金金額], [掛売個数], [掛売金額], [届け先ID], [修正担当者ＣＤ], [修正日])
                    VALUES ($WebOrderId, $OrderId2, $InfoId2, $OrderDate, 10, 320, CAST(1 AS Numeric(18, 0)), CAST(320 AS Numeric(18, 0)), CAST(1 AS Numeric(18, 0)), CAST(320 AS Numeric(18, 0)), 1, 1, GETDATE())
            "
        );

        $pdo = null;

        return;
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
