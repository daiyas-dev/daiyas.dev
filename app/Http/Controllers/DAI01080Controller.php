<?php

namespace App\Http\Controllers;

use App\Libs\DataSendWrapper;
use App\Models\モバイル予測入力;
use App\Models\注文データ;
use App\Models\日別得意先製造パターン;
use App\Models\商品マスタ;
use App\Models\得意先マスタ;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;

class DAI01080Controller extends Controller
{

    /**
     *  ColumSerch
     */

    public function ColSearch($vm)
    {
        $BushoCd = $vm->BushoCd;
        $sql = "SELECT
                    SHOHIN.商品ＣＤ   ,
                    SHOHIN.商品名   ,
                    SHOHIN.商品略称   ,
                    SHOHIN.商品区分   ,
                    SHOHIN.売価単価   ,
                    SHOHIN.弁当区分   ,
                    SHOHIN.ｸﾞﾙｰﾌﾟ区分   ,
                    SHOHIN.副食ＣＤ   ,
                    SHOHIN.主食ＣＤ   ,
                    SHOHIN.修正担当者ＣＤ   ,
                    SHOHIN.修正日
                FROM
                    商品マスタ SHOHIN   LEFT OUTER JOIN 各種テーブル BUSHOGRP ON     BUSHOGRP.各種CD = 26
                    AND
                    (SHOHIN.部署グループ = BUSHOGRP.サブ各種CD2 OR SHOHIN.部署グループ = 9)
                WHERE
                    SHOHIN.表示ＦＬＧ = 0
                    AND SHOHIN.弁当区分 = 0
                    AND BUSHOGRP.サブ各種CD1 = $BushoCd
                ORDER BY   SHOHIN.商品区分   ,SHOHIN.商品ＣＤ";


        $DataList = DB::select($sql);

        return response()->json($DataList);
    }

    /**
     * Search
     */
    public function Search($vm)
    {
        return response()->json($this->GetOrderList($vm));
    }

    /**
     * GetOrderList
     */
    public function GetOrderList($vm)
    {
        $BushoCd = $vm->BushoCd;
        $DeliveryDate = $vm->DeliveryDate;
        $CourseCd = $vm->CourseCd;

        $sql = "
WITH WITH_注文データ AS
 (  SELECT
 		*
 	FROM
 		注文データ
 	WHERE
 		注文区分 = 0
 	  AND 部署CD = $BushoCd
 	  AND CONVERT(varchar, 注文日付, 112) = '$DeliveryDate'
 	  AND CONVERT(varchar, 配送日, 112)   = '$DeliveryDate'
)
,WITH_モバイル_予測入力 AS
(  SELECT
		*
	FROM
		モバイル_予測入力
	WHERE
		部署CD = $BushoCd
		AND CONVERT(varchar, 日付, 112) = '$DeliveryDate'
)
,WITH_注文予測 AS
(
   ----- 注文予測両方 -----
    SELECT
    	CHU.部署ＣＤ,
    	CHU.得意先ＣＤ,
    	MOB.商品ＣＤ  AS MOB商品ＣＤ,
    	ISNULL(MOB.見込数, 0) AS 見込数 ,
    	CHU.商品CD  AS CHU商品ＣＤ,
    	ISNULL(現金個数 + 掛売個数,0) AS 注文数
    FROM
    	WITH_注文データ CHU ,
    	WITH_モバイル_予測入力 MOB
    WHERE
    	CHU.部署ＣＤ   = MOB.部署ＣＤ
    	AND  CHU.得意先ＣＤ = MOB.得意先ＣＤ
    	AND CHU.商品ＣＤ   = MOB.商品ＣＤ
 UNION
   ----- 注文データのみ -----
   SELECT
   		CHU.部署ＣＤ,
   		CHU.得意先ＣＤ,
   		NULL AS MOB商品ＣＤ,
   		NULL AS 見込数,
   		CHU.商品CD AS CHU商品ＣＤ,
   		ISNULL(現金個数 + 掛売個数,0) AS 注文数
   FROM
   		WITH_注文データ CHU
   WHERE
   		NOT EXISTS
   		(
   			SELECT
   				'X'
   			 FROM
   			 	WITH_モバイル_予測入力 MOB
   			 WHERE
   			 	CHU.部署ＣＤ   = MOB.部署ＣＤ
   			 	AND CHU.得意先ＣＤ = MOB.得意先ＣＤ
   			 	AND CHU.商品ＣＤ   = MOB.商品ＣＤ
   		 )
  UNION
   ----- 予測のみ -----
   SELECT
   		MOB.部署ＣＤ,
   		MOB.得意先ＣＤ,
   		MOB.商品ＣＤ AS MOB商品ＣＤ,
   		ISNULL(MOB.見込数, 0) AS 見込数,
   		NULL AS CHU商品ＣＤ,
   		0 AS 注文数
   FROM
   		WITH_モバイル_予測入力 MOB
   WHERE
   		NOT EXISTS
   		(
   			SELECT
   				'X'
   			FROM
   				WITH_注文データ CHU
   	     	WHERE
   	     		CHU.部署ＣＤ   = MOB.部署ＣＤ
   	     		AND CHU.得意先ＣＤ = MOB.得意先ＣＤ
   	     		AND CHU.商品ＣＤ   = MOB.商品ＣＤ
   	     )
   	)
    SELECT
		ct.ＳＥＱ,
		ct.部署ＣＤ,
		ct.コースＣＤ,
		tokui.得意先ＣＤ,
		tokui.得意先名,
		ISNULL(MOBCHU.MOB商品ＣＤ,ISNULL(MOBCHU.CHU商品ＣＤ,0)) 商品ＣＤ,
		ISNULL(MOBCHU.見込数, 0) 見込数,
		ISNULL(MOBCHU.CHU商品ＣＤ,0) as CHU商品ＣＤ ,
		ISNULL(MOBCHU.注文数,0)as 注文数,
		IIF(日別得意先製造パターン.製造パターン IS NULL, tokui.既定製造パターン, 日別得意先製造パターン.製造パターン) AS 製造パターン,
		IIF(日別得意先製造パターン.製造パターン IS NULL, null, 日別得意先製造パターン.修正日) AS 製造パターン更新日時
		,(
			SELECT
				MAX(修正日)
			FROM
				WITH_注文データ
			WHERE
				WITH_注文データ.部署CD = ct.部署CD AND
				WITH_注文データ.得意先CD = ct.得意先CD AND
				WITH_注文データ.商品ＣＤ = ISNULL(MOBCHU.MOB商品ＣＤ,ISNULL(MOBCHU.CHU商品ＣＤ,0))
		) 注文データ更新日時
		,(
			SELECT
				MAX(修正日)
			FROM
				WITH_モバイル_予測入力
			WHERE
				WITH_モバイル_予測入力.部署CD = ct.部署CD AND
				WITH_モバイル_予測入力.得意先CD = ct.得意先CD AND
				WITH_モバイル_予測入力.商品ＣＤ = ISNULL(MOBCHU.MOB商品ＣＤ,ISNULL(MOBCHU.CHU商品ＣＤ,0))
		) 予測データ更新日時
   	FROM
        コーステーブル ct
        LEFT JOIN 得意先マスタ tokui ON ct.得意先ＣＤ = tokui.得意先ＣＤ
        AND ct.部署ＣＤ = tokui.部署ＣＤ
        LEFT JOIN WITH_注文予測 MOBCHU ON MOBCHU.得意先CD = ct.得意先CD AND MOBCHU.部署CD = ct.部署CD
		LEFT OUTER JOIN 日別得意先製造パターン
			ON tokui.得意先ＣＤ = 日別得意先製造パターン.得意先ＣＤ
			AND CONVERT(NVARCHAR, 日別得意先製造パターン.製造日, 112) = '$DeliveryDate'
			AND 日別得意先製造パターン.部署ＣＤ   = ct.部署ＣＤ
			AND 日別得意先製造パターン.コースＣＤ = ct.コースＣＤ
    WHERE
    	ct.部署ＣＤ   = $BushoCd
    	AND ct.コースＣＤ = $CourseCd
	ORDER BY ct.コースＣＤ,ct.ＳＥＱ
";

        $DataList = DB::select(DB::raw($sql));

        return $DataList;
    }


    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();

        $ProductList = $params['ProductList'];
        $PatternList = $params['PatternList'];
        $ResponsibleCode = $params['LoginInfo'];

        $date = Carbon::now()->format('Y-m-d H:i:s');
        $skipProduct = [];
        $skipPattern = [];

        try {
            DB::beginTransaction();

            //モバイル予測入力更新
            foreach ($ProductList as $rec) {
                $r = モバイル予測入力::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                    ->where('日付', $rec['日付'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->get();

                if (isset($rec['修正日']) && !!$rec['修正日']) {
                    if (count($r) != 1) {
                        $skipProduct = collect($skipProduct)->push(["target" => $rec, "current" => null]);
                        continue;
                    } else if ($rec['修正日'] != $r[0]->修正日) {
                        $skipProduct = collect($skipProduct)->push(["target" => $rec, "current" => $r[0]]);
                        continue;
                    }

                    $rec['行Ｎｏ'] = $r[0]->行Ｎｏ;
                    $rec['修正日'] = $date;

                    モバイル予測入力::query()
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->where('日付', $rec['日付'])
                        ->where('商品ＣＤ', $rec['商品ＣＤ'])
                        ->where('行Ｎｏ', $rec['行Ｎｏ'])
                        ->update($rec);
                } else {
                    $no = null;
                    if (count($r) == 0) {
                        $no = モバイル予測入力::query()
                            ->where('部署ＣＤ', $rec['部署ＣＤ'])
                            ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                            ->where('日付', $rec['日付'])
                            ->where('商品ＣＤ', $rec['商品ＣＤ'])
                            ->max('行Ｎｏ') + 1;

                        $rec['行Ｎｏ'] = $no;
                        $rec['修正日'] = $date;

                        モバイル予測入力::insert($rec);
                    } else {
                        $skipProduct = collect($skipProduct)->push(["target" => $rec, "current" => $r[0]]);
                        continue;
                    }
                }
            }

            //注文データ更新
            foreach ($ProductList as $rec) {
                //得意先単価マスタ新情報取得
                $CustomerCd = $rec['得意先ＣＤ'];
                $DeliveryDate = date('Y-m-d');
                $ProductCd = $rec['商品ＣＤ'];

                $sql = "
                SELECT
                 単価
                FROM (
                    SELECT
                        *
                        , RANK() OVER(PARTITION BY 得意先ＣＤ, 商品ＣＤ ORDER BY 適用開始日 DESC) AS RNK
                    FROM
                        得意先単価マスタ新
                    WHERE
                        得意先ＣＤ=$CustomerCd
                    AND 適用開始日 <= '$DeliveryDate'
                    AND 商品ＣＤ = '$ProductCd'
                ) TT
                WHERE
                    RNK = 1
                ";
                $p = DB::select($sql);

                if(empty($p)){
                    continue;
                }

                //商品マスタ情報取得
                $s = 商品マスタ::query()
                ->where('商品ＣＤ', $rec['商品ＣＤ'])
                ->get();

                //得意先マスタ情報取得
                $t = 得意先マスタ::query()
                ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                ->get();

                $rec['配送日'] = $rec['日付'];
                $rec['注文日付'] = $rec['日付'];
                $rec['注文時間'] = date('H:i:s');
                $rec['注文区分'] = 1;
                $rec['商品区分'] = $s[0]->商品区分;
                $rec['入力区分'] = 0;
                $rec['予備金額１'] = $p[0]->単価;
                $rec['予備金額２'] = 0;
                $rec['予備ＣＤ１'] = 0;
                $rec['予備ＣＤ２'] = 0;
                $rec['修正担当者ＣＤ'] = $ResponsibleCode;
                $rec['明細行Ｎｏ'] = 0;

                if($t[0]->売掛現金区分 == 0){
                    $rec['現金個数']=$rec['見込数'];
                    $rec['現金金額']=$p[0]->単価 * $rec['見込数'];
                    $rec['掛売個数']=0;
                    $rec['掛売金額']=0;
                }else{
                    $rec['現金個数']=0;
                    $rec['現金金額']=0;
                    $rec['掛売個数']=$rec['見込数'];;
                    $rec['掛売金額']=$p[0]->単価 * $rec['見込数'];
                }

                unset($rec['日付']);
                unset($rec['見込数']);
                unset($rec['見込入力']);
                unset($rec['更新フラグ']);
                unset($rec['行Ｎｏ']);

                $r = 注文データ::query()
                    ->where('注文区分', $rec['注文区分'])
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                    ->where('配送日', $rec['配送日'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->get();

                if (isset($rec['修正日']) && !!$rec['修正日']) {
                    if (count($r) != 1) {
                        $skipProduct = collect($skipProduct)->push(["target" => $rec, "current" => null]);
                        continue;
                    } else if ($rec['修正日'] != $r[0]->修正日) {
                        $skipProduct = collect($skipProduct)->push(["target" => $rec, "current" => $r[0]]);
                        continue;
                    }

                    $rec['明細行Ｎｏ'] = $r[0]->明細行Ｎｏ;
                    $rec['修正日'] = $date;

                    注文データ::query()
                        ->where('注文区分', $rec['注文区分'])
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->where('配送日', $rec['配送日'])
                        ->where('商品ＣＤ', $rec['商品ＣＤ'])
                        ->where('明細行Ｎｏ', $rec['明細行Ｎｏ'])
                        ->update($rec);
                } else {
                    $no = null;
                    if (count($r) == 0) {
                        $no = 注文データ::query()
                            ->where('注文区分', $rec['注文区分'])
                            ->where('部署ＣＤ', $rec['部署ＣＤ'])
                            ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                            ->where('配送日', $rec['配送日'])
                            ->max('明細行Ｎｏ') + 1;

                        $rec['明細行Ｎｏ'] = $no;
                        $rec['修正日'] = $date;

                        注文データ::insert($rec);
                    } else {
                        $skipProduct = collect($skipProduct)->push(["target" => $rec, "current" => $r[0]]);
                        continue;
                    }
                }
            }

            //日別得意先製造パターン更新
            foreach ($PatternList as $rec) {
                $r = 日別得意先製造パターン::query()
                    ->where('部署ＣＤ', $rec['部署ＣＤ'])
                    ->where('製造日', $rec['製造日'])
                    ->where('コースＣＤ', $rec['コースＣＤ'])
                    ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                    ->get();

                if (isset($rec['修正日']) && !!$rec['修正日']) {
                    if (count($r) != 1) {
                        $skipPattern = collect($skipPattern)->push(["target" => $rec, "current" => null]);
                        continue;
                    } else if ($rec['修正日'] != $r[0]->修正日) {
                        $skipPattern = collect($skipPattern)->push(["target" => $rec, "current" => $r[0]]);
                        continue;
                    }

                    $rec['修正日'] = $date;

                    日別得意先製造パターン::query()
                        ->where('部署ＣＤ', $rec['部署ＣＤ'])
                        ->where('製造日', $rec['製造日'])
                        ->where('コースＣＤ', $rec['コースＣＤ'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->update($rec);
                } else {
                    if (count($r) == 0) {
                        $rec['修正日'] = $date;
                        日別得意先製造パターン::insert($rec);
                    } else {
                        $skipPattern = collect($skipPattern)->push(["target" => $rec, "current" => $r[0]]);
                        continue;
                    }
                }
            }

            if (count($skipProduct) > 0 || count($skipPattern) > 0) {
                DB::rollBack();
            } else {
                DB::commit();

                //モバイルsv更新
                $Message = $params['Message'];
                $ds = new DataSendWrapper();
                $ds->UpdateExpectedInputData($params['BushoCd'],$params['CourseCd'],$params['DeliveryDate'], $Message);

                //モバイルsv更新
                $ds->UpdateExpectedData($params['BushoCd'],$params['DeliveryDate'],null);

            }
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return response()->json([
            'result' => true,
            "skipped" => count($skipProduct) > 0 || count($skipPattern) > 0,
            "edited" => $this->GetOrderList($request),
        ]);
    }
}
