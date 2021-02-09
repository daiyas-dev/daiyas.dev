<?php

namespace App\Http\Controllers;

use App\Libs\WebOrderDataSendWrapper;
use App\Libs\WebOrderDataSend;
use App\Models\Web受注データ;
use App\Models\Web受注データ利用者別詳細;
use App\Models\Web受注データ利用者情報;
use App\Models\注文データ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
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
                ,MEMO.文言 AS 備考
                ,USERM.備考1
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
				LEFT OUTER JOIN Web受注得意先備考マスタ MEMO
					ON  MEMO.Web得意先ＣＤ=WEB.Web得意先ＣＤ
					AND MEMO.備考ID=USERS.備考ID
            ORDER BY
                USERS.利用者ID
                ,DETAILS.商品ＣＤ
        ";

        /* ログ出力 START*/
        Log::info('GetOrderList sql\n' . $sql);
        /* ログ出力 END*/

        $DataList = DB::select($sql);

        return $DataList;
    }

    /**
     * Save
     */
    public function Save($request)
    {
        $skip = [];

        //Web受注sv更新用
        $WODeleteListForInfo = [];
        $WOInsertListForInfo = [];
        $WOUpdateListForInfo = [];
        $WODeleteListForOrder = [];

        DB::connection('sqlsrv_weborder')->beginTransaction();

        try {
            $WebOrderId = $request->WebOrderId;
            $EditUser = $request->EditUser;
            $IsRegisted = $request->IsRegisted;
            $params = $request->all();

            $DeleteList = $params['DeleteList'];
            foreach ($DeleteList as $rec) {
                Web受注データ利用者別詳細::query()
                    ->where('Web受注ID', $rec['Web受注ID'])
                    ->where('注文ID', $rec['注文ID'])
                    ->where('注文情報ID', $rec['注文情報ID'])
                    ->delete();

                //AWS連携 -> Web注文情報データ
                //Web受注sv更新用
                array_push($WODeleteListForInfo, $rec);
            }

            $SaveList = $params['SaveList'];

            $date = Carbon::now()->format('Y-m-d H:i:s');
            foreach ($SaveList as $rec) {
                if (isset($rec['修正日']) && !!$rec['修正日']) {
                    $r = Web受注データ利用者別詳細::query()
                        ->where('Web受注ID', $rec['Web受注ID'])
                        ->where('注文ID', $rec['注文ID'])
                        ->where('注文情報ID', $rec['注文情報ID'])
                        ->get();

                    if (count($r) != 1) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => null]);
                        continue;
                    } else if ($rec['修正日'] != $r[0]->修正日) {
                        $skip = collect($skip)->push(["target" => $rec, "current" => $r[0]]);
                        continue;
                    }

                    if (!!isset($rec['現金個数']) && !!isset($rec['掛売個数'])) {
                        //個数ありはupdate
                        $data = $rec;
                        $data['修正日'] = $date;

                        unset(
                            $data['Web得意先ＣＤ'],
                            $data['配送日'],
                            $data['利用者ID'],
                            $data['利用者CD'],
                            $data['得意先ＣＤ'],
                            $data['備考'],
                            $data['備考1'],
                        );

                        Web受注データ利用者別詳細::query()
                            ->where('Web受注ID', $rec['Web受注ID'])
                            ->where('注文ID', $rec['注文ID'])
                            ->where('注文情報ID', $rec['注文情報ID'])
                            ->update($data);

                        //AWS連携 -> Web注文情報データ
                        //Web受注sv更新用
                        array_push($WOUpdateListForInfo, $data);

                    } else {
                        //個数無しはdelete
                        Web受注データ利用者別詳細::query()
                            ->where('Web受注ID', $rec['Web受注ID'])
                            ->where('注文ID', $rec['注文ID'])
                            ->where('注文情報ID', $rec['注文情報ID'])
                            ->delete();

                        //AWS連携 -> Web注文情報データ
                        //Web受注sv更新用
                        array_push($WODeleteListForInfo, $rec);
                    }
                } else {
                    if (!!isset($rec['現金個数']) && !!isset($rec['掛売個数'])) {
                        $Order = Web受注データ利用者情報::query()
                            ->where('Web受注ID', $WebOrderId)
                            ->where('利用者ID', $rec['利用者ID'])
                            ->get();

                        $OrderId = count($Order) == 0 ? null : $Order[0]->注文ID;

                        if (!$OrderId) {
                            //注文IDをAWSから発番して貰う
                            $sender = new WebOrderDataSend();
                            $OrderId = $sender->GetOrderId();

                            $data = [];
                            $data['Web受注ID'] = $WebOrderId;
                            $data['注文ID'] = $OrderId;
                            $data['利用者ID'] = $rec['利用者ID'];
                            $data['利用者CD'] = $rec['利用者CD'];
                            $data['修正担当者ＣＤ'] = $rec['修正担当者ＣＤ'];
                            $data['修正日'] = $date;

                            Web受注データ利用者情報::insert($data);

                        }

                        //注文情報IDをAWSから発番して貰う
                        $sender = new WebOrderDataSend();
                        $InfoId = $sender->GetOrderInfoId();

                        $data = $rec;
                        $data['Web受注ID'] = $WebOrderId;
                        $data['注文ID'] = $OrderId;
                        $data['注文情報ID'] = $InfoId;
                        $data['注文日時'] = $date;
                        $data['修正日'] = $date;

                        unset(
                            $data['Web得意先ＣＤ'],
                            $data['配送日'],
                            $data['利用者ID'],
                            $data['利用者CD'],
                            $data['得意先ＣＤ'],
                            $data['備考'],
                        );

                        Web受注データ利用者別詳細::insert($data);

                        //AWS連携 -> Web注文情報データ
                        //Web受注sv更新用
                        array_push($WOInsertListForInfo, $data);
                    }
                }
            }

            //注文データ更新
            $request->EditDate = $date;
            $request->EditUser = $EditUser;
            $OrderToMobile = $this->SaveOrderFromWeb($request);

            //後処理(件数が無くなった場合の削除)
            $InfoCountSQL = "
                SELECT
                    WEB.Web受注ID
					,WEB.Web得意先ＣＤ
                    ,USERS.注文ID
                    ,COUNT(DETAILS.注文情報ID) AS COUNT
                FROM
					Web受注データ WEB
					LEFT OUTER JOIN Web受注データ利用者情報 USERS
					    ON  USERS.Web受注ID=WEB.Web受注ID
                    LEFT OUTER JOIN Web受注データ利用者別詳細 DETAILS
                        ON  DETAILS.Web受注ID=USERS.Web受注ID
                        AND DETAILS.注文ID=USERS.注文ID
                WHERE
                    USERS.Web受注ID=$WebOrderId
                GROUP BY
                    WEB.Web受注ID
					,WEB.Web得意先ＣＤ
                    ,USERS.注文ID
            ";

            $info_count = DB::connection('sqlsrv_weborder')->select($InfoCountSQL);

            foreach ($info_count as $info) {
                if ($info->COUNT == 0) {
                    Web受注データ利用者情報::query()
                        ->where('Web受注ID', $info->Web受注ID)
                        ->where('注文ID', $info->注文ID)
                        ->delete();

                    //AWS連携 -> Web注文データ
                    //Web受注sv更新用
                    array_push($WODeleteListForOrder, $info);
                }
            }

            $OrderCountSQL = "
                SELECT
                    WEB.Web受注ID
                    ,WEB.Web得意先ＣＤ
                    ,WEB.配送日
                    ,COUNT(USERS.注文ID) AS COUNT
                FROM
					Web受注データ WEB
					LEFT OUTER JOIN Web受注データ利用者情報 USERS
						ON  USERS.Web受注ID=WEB.Web受注ID
                WHERE
                    WEB.Web受注ID=$WebOrderId
                GROUP BY
                    WEB.Web受注ID
					,WEB.Web得意先ＣＤ
                    ,WEB.配送日
            ";

            $order_count = DB::connection('sqlsrv_weborder')->select($OrderCountSQL);

            foreach ($order_count as $order) {
                if ($order->COUNT == 0) {
                    Web受注データ::query()
                        ->where('Web受注ID', $order->Web受注ID)
                        ->delete();
                }
            }

            if (count($skip) > 0) {
                DB::connection('sqlsrv_weborder')->rollBack();
            } else {
                DB::connection('sqlsrv_weborder')->commit();

                //Web受注sv更新
                foreach ($order_count as $order) {
                    $ds = new WebOrderDataSendWrapper();
                    $ds->UpdateWebOrderData($order->Web得意先ＣＤ, $order->配送日);
                }

                // foreach ($WODeleteListForOrder as $rec) {
                //     $ds = new WebOrderDataSendWrapper();
                //     $ds->Delete('Web注文データ', $rec, true, null, null, null);
                // }
                // //TODO: Web受注svのWeb注文データのinsert/updateは、社内側のWeb受注データ利用者別詳細を集計したもので行う

                // foreach ($WODeleteListForInfo as $rec) {
                //     $ds = new WebOrderDataSendWrapper();
                //     $ds->Delete('Web注文情報データ', $rec, true, null, null, null);
                // }
                // foreach ($WOInsertListForInfo as $data) {
                //     $ds = new WebOrderDataSendWrapper();
                //     $ds->Insert('Web注文情報データ', $data, true, null, null, null);
                // }
                // foreach ($WOUpdateListForInfo as $data) {
                //     $ds = new WebOrderDataSendWrapper();
                //     $ds->Update('Web注文情報データ', $data, true, null, null, null);
                // }

                if ($IsRegisted == 0) {
                    //TODO: 初回取込時はAWSに何かを送信して、確認メールを送る
                }

                // //モバイルsv更新
                // foreach ($OrderToMobile["MDeleteList"] as $rec) {
                //     $ds = new WebOrderDataSendWrapper();
                //     $ds->Delete('注文データ', $rec, true, $rec['部署ＣＤ'], null, null);
                // }
                // foreach ($OrderToMobile["MInsertList"] as $data) {
                //     $ds = new WebOrderDataSendWrapper();
                //     $ds->Insert('注文データ', $data, true, $rec['部署ＣＤ'], null, null);
                // }
            }
        } catch (Exception $exception) {
            DB::connection('sqlsrv_weborder')->rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            "edited" => count($skip) > 0,
            "current" => $this->GetOrderList($request),
        ]);
    }

    /**
     * SaveOrderFromWeb
     */
    public function SaveOrderFromWeb($request) {
        $WebOrderId = $request->WebOrderId;
        $DeliveryDate = $request->DeliveryDate;
        $EditDate = $request->EditDate ?? Carbon::now()->format('Y-m-d H:i:s');
        $EditUser = $request->EditUser;

        //モバイルsv更新用
        $MInsertList = [];
        $MUpdateList = [];

        $GetOrderSQL = "
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
				1 AS 注文区分
				,FORMAT(MAX(注文日時), 'yyyy-MM-dd') AS 注文日付
				,FORMAT(MAX(注文日時), 'HH:mm:ss') AS 注文時間
				,部署CD AS 部署ＣＤ
				,得意先ＣＤ
				,配送日
				, 0 AS 明細行Ｎｏ
                ,商品ＣＤ
				,商品区分
				,0 AS 入力区分
                ,SUM(現金個数) AS 現金個数
                ,SUM(現金金額) AS 現金金額
                ,SUM(掛売個数) AS 掛売個数
                ,SUM(掛売金額) AS 掛売金額
				,'' AS 備考１
				,'' AS 備考２
				,'' AS 備考３
				,'' AS 備考４
				,'' AS 備考５
                ,単価 AS 予備金額１
                ,0 AS 予備金額２
                ,0 AS 予備ＣＤ１
                ,0 AS 予備ＣＤ２
                ,MAX(修正日) AS 修正日
				,(
					STRING_AGG(
						IIF(
							((売掛現金区分=0 AND 現金個数 > 0) OR (売掛現金区分=1 AND 掛売個数 > 0))
							AND
							(備考 IS NOT NULL OR 届け先名 IS NOT NULL),
							利用者CD + ':'
								+ IIF(届け先名 IS NOT NULL, ' ' + 届け先名, '')
								+ IIF(備考 IS NOT NULL, ' ' + 備考, '')
						,NULL)
						,CHAR(13) + CHAR(10)
					)
				) AS 特記_Web受注
                ,特記_社内
                ,特記_配送
                ,特記_通知
                ,Web受注ID
            FROM
			(
				SELECT
					WEB.Web受注ID
					,WEB.Web得意先ＣＤ
					,WEBTOK.得意先ＣＤ
					,TOK.得意先名
					,TOK.部署CD
                    ,TOK.売掛現金区分
                    ,TOK.備考１ AS 特記_社内
                    ,TOK.備考２ AS 特記_配送
                    ,TOK.備考３ AS 特記_通知
					,WEB.配送日
					,MAX(DETAILS.注文日時) OVER(PARTITION BY WEB.Web受注ID, TOK.得意先名) AS 注文日時
					,DETAILS.商品ＣＤ
					,PM.商品名
					,PM.商品区分
					,DETAILS.単価
					,IIF(TOK.売掛現金区分=0, DETAILS.現金個数, 0) AS 現金個数
					,IIF(TOK.売掛現金区分=0, DETAILS.現金金額, 0) AS 現金金額
					,IIF(TOK.売掛現金区分=1, DETAILS.掛売個数, 0) AS 掛売個数
					,IIF(TOK.売掛現金区分=1, DETAILS.掛売金額, 0) AS 掛売金額
					,USERS.利用者CD
					,PLACE.届け先名
					,MEMO.文言 AS 備考
					,DETAILS.修正日
				FROM
					WEB
					LEFT OUTER JOIN Web受注得意先マスタ WEBTOK
						ON  WEBTOK.Web得意先ＣＤ=WEB.Web得意先ＣＤ
					LEFT OUTER JOIN 得意先マスタ TOK
						ON  TOK.得意先ＣＤ=WEBTOK.得意先ＣＤ
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
					LEFT OUTER JOIN Web受注得意先備考マスタ MEMO
						ON  MEMO.Web得意先ＣＤ=WEB.Web得意先ＣＤ
						AND MEMO.備考ID=USERS.備考ID
					LEFT OUTER JOIN Web受注得意先届け先マスタ PLACE
						ON  PLACE.Web得意先ＣＤ=WEB.Web得意先ＣＤ
						AND PLACE.届け先ID=DETAILS.届け先ID
				WHERE
					(TOK.売掛現金区分=0 AND DETAILS.現金個数 > 0)
					OR
					(TOK.売掛現金区分=1 AND DETAILS.掛売個数 > 0)
			) X
			GROUP BY
                Web受注ID
				,部署CD
				,得意先ＣＤ
				,配送日
                ,商品ＣＤ
				,商品区分
                ,単価
                ,特記_社内
                ,特記_配送
                ,特記_通知
            ORDER BY
                得意先ＣＤ
                ,商品ＣＤ
        ";

        $OrderList = DB::select($GetOrderSQL);

        //取り込みの前に指定の注文の見込を削除する
        if (isset($OrderList) && is_array($OrderList) && 0<count($OrderList)) {
            注文データ::query()
            ->where('注文区分', 1)
            ->where('部署ＣＤ', $OrderList[0]->部署ＣＤ)
            ->where('得意先ＣＤ', $OrderList[0]->得意先ＣＤ)
            ->where('配送日', $DeliveryDate)
            ->delete();
        }

        foreach ($OrderList as $rec) {
            $rec = (array) $rec;

            $r = 注文データ::query()
                ->where('注文区分', $rec['注文区分'])
                ->where('部署ＣＤ', $rec['部署ＣＤ'])
                ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                ->where('配送日', $rec['配送日'])
                ->where('商品ＣＤ', $rec['商品ＣＤ'])
                ->get();

            $data = $rec;
            $data['修正担当者ＣＤ'] = $EditUser;
            $data['修正日'] = $EditDate;

            if (!!$data['特記_Web受注']) {
                if (count($r) == 0) {
                    $data['特記_社内用'] = ($data['特記_社内'] != '' ? $data['特記_社内'] . "\r\n" : '') . $data['特記_Web受注'];
                    $data['特記_配送用'] = ($data['特記_配送'] != '' ? $data['特記_配送'] . "\r\n" : '') . $data['特記_Web受注'];
                    $data['特記_通知用'] = $data['特記_通知'];
                } else {
                    if (preg_match('/' . $data['特記_Web受注'] . '/s', $r[0]->特記_社内用) == 0) {
                        $data['特記_社内用'] = (!!$r[0]->特記_社内用 ? $r[0]->特記_社内用 . "\r\n" : '') . $data['特記_Web受注'];
                    } else {
                        $data['特記_社内用'] = $r[0]->特記_社内用;
                    }
                    if (preg_match('/' . $data['特記_Web受注'] . '/s', $r[0]->特記_配送用) == 0) {
                        $data['特記_配送用'] = (!!$r[0]->特記_配送用 ? $r[0]->特記_配送用 . "\r\n" : '') . $data['特記_Web受注'];
                    } else {
                        $data['特記_配送用'] = $r[0]->特記_配送用;
                    }
                    $data['特記_通知用'] = $r[0]->特記_通知用;
                }
            }

            //補正
            $data['備考１'] = $data['備考１'] ?? '';
            $data['備考２'] = $data['備考２'] ?? '';
            $data['備考３'] = $data['備考３'] ?? '';
            $data['備考４'] = $data['備考４'] ?? '';
            $data['備考５'] = $data['備考５'] ?? '';
            $data['特記_社内用'] = $data['特記_社内用'] ?? '';
            $data['特記_配送用'] = $data['特記_配送用'] ?? '';
            $data['特記_通知用'] = $data['特記_通知用'] ?? '';

            unset($data['特記_Web受注']);
            unset($data['特記_社内']);
            unset($data['特記_配送']);
            unset($data['特記_通知']);

            if (count($r) == 1) {
                $data['明細行Ｎｏ'] = $r[0]->明細行Ｎｏ;

                注文データ::query()
                    ->where('注文区分', $data['注文区分'])
                    ->where('部署ＣＤ', $data['部署ＣＤ'])
                    ->where('得意先ＣＤ', $data['得意先ＣＤ'])
                    ->where('配送日', $data['配送日'])
                    ->where('明細行Ｎｏ', $data['明細行Ｎｏ'])
                    ->update($data);

                //モバイルsv更新用
                array_push($MUpdateList, $data);

            } else {
                $no = 注文データ::query()
                    ->where('注文区分', $rec['注文区分'])
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                    ->where('配送日', $rec['配送日'])
                    ->max('明細行Ｎｏ') + 1;

                $data['明細行Ｎｏ'] = $no;

                注文データ::insert($data);

                //モバイルsv更新用
                array_push($MInsertList, $data);
            }
        }

        return [
            "MInsertList" => $MInsertList,
            "MUpdateList" => $MUpdateList,
        ];
    }
}
