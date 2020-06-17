<?php

namespace App\Http\Controllers;

use App\Models\注文データ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use PDO;

class DAI01032Controller extends Controller
{

    /**
     * GetProductList
     */
    public function GetProductList($request)
    {
        $CustomerCd = $request->CustomerCd;
        $DeliveryDate = $request->DeliveryDate;

        $sql = "
            SELECT
                MTT.Web得意先ＣＤ,
                MTT.商品ＣＤ,
                PM.商品名,
                PM.商品区分,
                MTT.単価
            FROM
                (
                    SELECT
                        *
                    FROM (
                        SELECT
                            *
                            , RANK() OVER(PARTITION BY Web得意先ＣＤ, 商品ＣＤ ORDER BY 適用開始日 DESC) AS RNK
                        FROM
                            Web受注得意先単価マスタ
                        WHERE
                            Web得意先ＣＤ='$CustomerCd'
                        AND 適用開始日 <= '$DeliveryDate'
                    ) TT
                    WHERE
                        RNK = 1
                ) MTT
                LEFT OUTER JOIN 商品マスタ PM
                    ON	PM.商品ＣＤ=MTT.商品ＣＤ
        ";

        $Result = collect(DB::select($sql))
            ->map(function ($product) {
                $vm = (object) $product;

                $vm->Cd = $product->商品ＣＤ;
                $vm->CdNm = $product->商品名;

                return $vm;
            })
            ->values();

        return response()->json($Result);
    }

    /**
     * GetUserList
     */
    public function GetUserList($request)
    {
        $CustomerCd = $request->CustomerCd;

        $sql = "
            SELECT
                W.利用者CD,
                W.利用者ID,
                W.得意先ＣＤ,
                T.得意先名
            FROM
                Web受注得意先利用者マスタ W
                LEFT OUTER JOIN 得意先マスタ T
                    ON T.得意先ＣＤ=W.得意先ＣＤ
            WHERE
                W.Web得意先ＣＤ='$CustomerCd'
        ";

        $Result = collect(DB::select($sql))
            ->map(function ($user) {
                $vm = (object) $user;

                $vm->Cd = $user->利用者CD;
                $vm->CdNm = $user->利用者ID;

                return $vm;
            })
            ->values();

        return response()->json($Result);
    }

    /**
     * GetPlaceList
     */
    public function GetPlaceList($request)
    {
        $CustomerCd = $request->CustomerCd;

        $sql = "
            SELECT
                W.届け先ID,
                W.届け先名
            FROM
                Web受注得意先届け先マスタ W
            WHERE
                W.Web得意先ＣＤ='$CustomerCd'
        ";

        $Result = collect(DB::select($sql))
            ->map(function ($place) {
                $vm = (object) $place;

                $vm->Cd = $place->届け先ID;
                $vm->CdNm = $place->届け先名;

                return $vm;
            })
            ->values();

        return response()->json($Result);
    }

    /**
     * GetMemoList
     */
    public function GetMemoList($request)
    {
        $CustomerCd = $request->CustomerCd;

        $sql = "
            SELECT
                W.備考ID,
                W.文言
            FROM
                Web受注得意先備考マスタ W
            WHERE
                W.Web得意先ＣＤ='$CustomerCd'
        ";

        $Result = collect(DB::select($sql))
            ->map(function ($memo) {
                $vm = (object) $memo;

                $vm->Cd = $memo->備考ID;
                $vm->CdNm = $memo->文言;

                return $vm;
            })
            ->values();

        return response()->json($Result);
    }

    /**
     * Search
     */
    public function Search($request)
    {
        return response()->json($this->GetOrderList($request));
    }

    /**
     * GetOrderList
     */
    public function GetOrderList($vm)
    {
        $WebOrderId = $vm->WebOrderId;
        $DeliveryDate = $vm->DeliveryDate;

        $sql = "
			WITH WEB AS (
				SELECT DISTINCT
                    Web受注ID
                    ,Web得意先ＣＤ
					,配送日
				FROM
					Web受注データ
				WHERE
					Web受注ID=$WebOrderId
				AND 配送日='$DeliveryDate'
			)
            SELECT
                WEB.Web受注ID
				,WEB.Web得意先ＣＤ
				,WEB.配送日
                ,USERS.注文ID
                ,USERS.利用者ID
                ,USERS.利用者CD
                ,USERS.備考ID
                ,USERM.得意先ＣＤ
                ,TM.得意先名
                ,DETAILS.注文情報ID
                ,DETAILS.注文日時
                ,DETAILS.商品ＣＤ
				,PM.商品名
				,PM.商品区分
                ,DETAILS.単価
                ,DETAILS.掛売個数
                ,DETAILS.掛売金額
                ,DETAILS.現金個数
                ,DETAILS.現金金額
                ,DETAILS.届け先ID
                ,DETAILS.修正日
            FROM
				WEB
                LEFT OUTER JOIN Web受注データ利用者情報 USERS
					ON  USERS.Web受注ID=WEB.Web受注ID
                LEFT OUTER JOIN Web受注データ利用者別詳細 DETAILS
                    ON  DETAILS.Web受注ID=USERS.Web受注ID
                    AND DETAILS.注文ID=USERS.注文ID
				LEFT OUTER JOIN 商品マスタ PM
					ON  PM.商品ＣＤ=DETAILS.商品ＣＤ
				LEFT OUTER JOIN Web受注得意先利用者マスタ USERM
                    ON  USERM.Web得意先ＣＤ=WEB.Web得意先ＣＤ
                    AND USERM.利用者ID=USERS.利用者ID
				LEFT OUTER JOIN 得意先マスタ TM
					ON  TM.得意先ＣＤ=USERM.得意先ＣＤ
            ORDER BY
                USERS.注文ID
                ,USERS.利用者CD
                ,DETAILS.注文情報ID
                ,DETAILS.商品ＣＤ
        ";

        $DataList = DB::select($sql);

        return $DataList;
    }

    /**
     * Save
     */
    public function Save($request)
    {
        $skip = [];

        DB::beginTransaction();

        try {
            $params = $request->all();

            $DeleteList = $params['DeleteList'];
            foreach ($DeleteList as $rec) {
                注文データ::query()
                    ->where('注文区分', $rec['注文区分'])
                    ->where('注文日付', $rec['注文日付'])
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                    ->where('配送日', $rec['配送日'])
                    ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                    ->delete();
            }

            $SaveList = $params['SaveList'];

            $date = Carbon::now()->format('Y-m-d H:i:s');
            foreach ($SaveList as $rec) {
                $no = null;
                if (isset($rec['修正日']) && !!$rec['修正日']) {
                    $r = 注文データ::query()
                        ->where('注文区分', $rec['注文区分'])
                        ->where('注文日付', $rec['注文日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->where('配送日', $rec['配送日'])
                        ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                        ->get();

                    if (count($r) != 1) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => null]);
                        continue;
                    } else if ($rec['修正日'] != $r[0]->修正日) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                        continue;
                    }

                    $no = $rec['明細行Ｎｏ'];

                    注文データ::query()
                        ->where('注文区分', $rec['注文区分'])
                        ->where('注文日付', $rec['注文日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->where('配送日', $rec['配送日'])
                        ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                        ->delete();
                } else {
                    $no = 注文データ::query()
                        ->where('注文区分', $rec['注文区分'])
                        ->where('注文日付', $rec['注文日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->where('配送日', $rec['配送日'])
                        ->max('明細行Ｎｏ') + 1;
                }

                if (!!isset($rec['現金個数']) && !!isset($rec['掛売個数'])) {
                    $data = $rec;
                    $data['修正日'] = $date;
                    $data['明細行Ｎｏ'] = $no;
                    $data['備考１'] = $data['備考１'] ?? '';
                    $data['備考２'] = $data['備考２'] ?? '';
                    $data['備考３'] = $data['備考３'] ?? '';
                    $data['備考４'] = $data['備考４'] ?? '';
                    $data['備考５'] = $data['備考５'] ?? '';
                    $data['特記_社内用'] = $data['特記_社内用'] ?? '';
                    $data['特記_配送用'] = $data['特記_配送用'] ?? '';
                    $data['特記_通知用'] = $data['特記_通知用'] ?? '';

                    注文データ::insert($data);
                }
            }

            if (count($skip) > 0) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            "edited" => count($skip) > 0,
            "current" => $this->GetOrderList($request),
        ]);
    }

    /**
     * Delete
     */
    public function Delete($request)
    {
        $skip = [];

        DB::beginTransaction();

        try {
            $params = $request->all();

            $DeleteList = $params['DeleteList'];

            foreach ($DeleteList as $rec) {
                if (isset($rec['修正日']) && !!$rec['修正日']) {
                    $r = 注文データ::query()
                        ->where('注文区分', $rec['注文区分'])
                        ->where('注文日付', $rec['注文日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->where('配送日', $rec['配送日'])
                        ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                        ->get();

                    if (count($r) != 1) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => null]);
                        continue;
                    } else if ($rec['修正日'] != $r[0]->修正日) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                        continue;
                    }

                    注文データ::query()
                        ->where('注文区分', $rec['注文区分'])
                        ->where('注文日付', $rec['注文日付'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->where('配送日', $rec['配送日'])
                        ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                        ->delete();
                }
            }

            if (count($skip) > 0) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            "edited" => count($skip) > 0 ? $this->GetOrderList($request) : [],
        ]);
    }
}
