<?php

namespace App\Http\Controllers;

use App\Models\注文データ;
use App\Models\商品マスタ;
use App\Models\得意先マスタ;
use App\Models\得意先単価マスタ新;
use App\Models\外部受注システム取込履歴;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class DAI01270Controller extends Controller
{
    /**
     * UploadFile
     */
    public function UploadFile($request)
    {
        $this->validate($request, [
            'file' => [
                'required',
                'file',
            ]
        ]);

        try {
            IF ($request->file('file')->isValid([])) {
                $spl = new \SplFileInfo($request->file);

                $Contents = mb_convert_encoding(file_get_contents($spl->getPathname()), "utf-8", "sjis");

                $file = $request->file('file');
                $FileName = $file->getClientOriginalName();
                $Existenceflg = 0;

                //外部受注システム取込履歴チェック確認
                $value = DB::select("SELECT ファイル名 FROM 外部受注システム取込履歴 WHERE ファイル名='$FileName'");
                if(count($value)>0){
                    $Existenceflg = 1;
                };

                return $this->GetResult($Contents, $Existenceflg);
            } else {
                return response()->json([
                    'result' => false,
                    "DataCheckNo" => 0,
                    "message" => '不正なデータです。',
                ]);
            }
        } catch (Exception $ex) {
            return response()->json([
                'result' => false,
                "DataCheckNo" => 0,
                "message" => '取込に失敗しました。',
                'exception' => $ex->getMessage(),
            ]);
        }
    }

    /**
     * GetResult
     */
    public function GetResult($Contents, $Existenceflg)
    {
        try {
            $Lines = explode("\r\n", $Contents);
            $Company = (object)[];
            $Customers = [];
            $arrReturn = $this->CSVCheck($Contents);
            $last_data_row = $arrReturn['last_data_row'];
            $error_message = $arrReturn['error_message'];

            foreach ($Lines as $i => $Line) {
                //1行目は見出しの為処理しない
                if($i===0){
                    continue;
                }

                $Customer = (object)[];

                //行に値がない場合は処理しない。画面にも表示しない
                if($Line==''){
                    continue;
                }
                //行が全てカンマのみの場合は処理しない。画面にも表示しない
                $LineStr=str_replace(' ','',$Line);
                if($LineStr==',,,,,'){
                    continue;
                }

                $Evacuation = (object)[];
                $Evacuation = explode(",", $Line);

                //要素数が6個以外の場合は画面に出さない。画面には表示する
                if(count($Evacuation)<>6){
                    if($i<=$last_data_row)
                    {
                        $Customer->行番号 = $i;
                        array_push($Customers, $Customer);
                    }
                    continue;
                }

                //画面に出力するリストを作成
                $Customer->行番号 = $i;
                $Customer->配送日 = $Evacuation[0];
                $Customer->得意先CD = $Evacuation[1];
                $Customer->商品CD = $Evacuation[3];
                $Customer->数量 = $Evacuation[5];

                if (!empty($Customer->得意先CD) && ctype_digit($Customer->得意先CD)){
                    $name = DB::select("SELECT 得意先名略称 FROM 得意先マスタ WHERE 得意先ＣＤ=$Customer->得意先CD");
                    if (count($name)>0) {
                        $Customer->顧客名 = $name[0]->得意先名略称;
                    }
                }

                if (!empty($Customer->商品CD) && ctype_digit($Customer->商品CD)) {
                    $name = DB::select("SELECT 商品名 FROM 商品マスタ WHERE 商品ＣＤ=$Customer->商品CD");
                    if (count($name)>0) {
                        $Customer->商品名 = $name[0]->商品名;
                    }
                }

                array_push($Customers, $Customer);
            };

            //エラー表示を含んで値を返す
            IF ($error_message <> ""){
                return response()->json([
                    'result' => false,
                    'Contents' => $Contents,
                    'Company' => $Company,
                    'Customers' => $Customers,
                    'Existenceflg' => $Existenceflg,
                    "DataCheckNo" => 1,
                    "message" => $error_message,
                ]);
            };

            return response()->json([
                'result' => true,
                'Contents' => $Contents,
                'Company' => $Company,
                'Customers' => $Customers,
                'Existenceflg' => $Existenceflg,
            ]);
        } catch (Exception $ex) {
            return response()->json([
                'result' => false,
                "DataCheckNo" => 0,
                "message" => '取込に失敗しました。',
                "exception" => $ex,
            ]);
        } finally {
        }
    }

    /**
     * CSVCheck
     */
    public function CSVCheck($Contents)
    {
        try {
            $last_data_row=0;
            $Lines = explode("\r\n", $Contents);
            $Errors = [];
            foreach ($Lines as $i => $Line) {
                //1行目は見出しの為処理しない
                if($i===0){
                    continue;
                }
                //行に値がない場合は処理しない
                if($Line==''){
                    continue;
                }
                //行が全てカンマのみの場合は処理しない
                $LineStr=str_replace(' ','',$Line);
                if($LineStr==',,,,,'){
                    continue;
                }

                $last_data_row=$i;
                $Evacuation = (object)[];
                $Evacuation = explode(",", $Line);

                //要素数チェック
                if(count($Evacuation)<>6){
                    $Errors[]=$i.'行目 項目を区切る半角カンマの数は6個にして下さい。';
                    continue;
                }

                //配送日
                $valid_DeliveryDate=false;
                if (!$Evacuation[0]) {
                    $Errors[]=$i.'行目 配送日が入力されていません。';
                }else{
                    //日付がハイフン区切りならスラッシュに置き換える
                    try {
                        $date = new \DateTime($Evacuation[0]);
                        if (!$date) {
                            $Errors[]=$i.'行目 配送日は年/月/日で指定して下さい。';
                        }
                        else{
                            if (checkdate($date->format('m'), $date->format('d'), $date->format('Y')) === true) {
                                $valid_DeliveryDate=true;
                            }else{
                                $Errors[]=$i.'行目 配送日が無効な日付です。';
                            }
                        }
                    } catch (Exception $ex) {
                        $Errors[]=$i.'行目 配送日は年/月/日で指定して下さい。';
                    }
                }

                //得意先ＣＤ
                $valid_customer=false;
                if (!$Evacuation[1]) {
                    $Errors[]=$i.'行目 得意先ＣＤが入力されていません。';
                }else if(!ctype_digit($Evacuation[1])){
                    $Errors[]=$i.'行目 得意先ＣＤは数値で入力して下さい。';
                }else{
                    //入力値妥当性チェック
                    $value = DB::select("SELECT 得意先名略称,売掛現金区分 FROM 得意先マスタ WHERE 得意先ＣＤ=$Evacuation[1]");
                    if (count($value)==0) {
                        $Errors[]=$i.'行目 得意先ＣＤが得意先マスタに存在しません。';
                    }else{
                        $Evacuation[2]=$value[0]->得意先名略称;
                        //売掛現金区分の売掛チェック確認
                        if ($value[0]->売掛現金区分 <> 1) {
                            $Errors[]=$i.'行目 得意先マスタの売掛現金区分が売掛ではありません。';
                        }else{
                            $valid_customer=true;
                        }
                    }
                }

                //商品ＣＤ
                $valid_product=false;
                if(!$Evacuation[3]){
                    $Errors[]=''.$i.'行目 商品ＣＤが入力されていません。';
                }else if(!ctype_digit($Evacuation[3])){
                    $Errors[]=$i.'行目 商品ＣＤは数値で入力して下さい。';
                }else{
                    //入力値妥当性チェック
                    $value = DB::select("SELECT 商品名 FROM 商品マスタ WHERE 商品ＣＤ=$Evacuation[3]");
                    if (count($value)==0) {
                        $Errors[]=$i.'行目 商品ＣＤが商品マスタに存在しません。';
                    }
                    else
                    {
                        $Evacuation[4]=$value[0]->商品名;
                        $valid_product=true;
                    }
                }

                //得意先ＣＤ、商品ＣＤ、配送日が全て有効な場合、得意先単価が存在するか確認
                if ($valid_customer && $valid_product && $valid_DeliveryDate) {
                    $value = DB::select("
                        WITH PRICE_MASTER AS(
                            SELECT
                                TT.*
                                ,IIF(
                                    (
                                        SELECT MAX(TT2.適用開始日)
                                        FROM 得意先単価マスタ新 TT2
                                        WHERE TT2.得意先ＣＤ=TT.得意先ＣＤ
                                        AND TT2.商品ＣＤ=TT.商品ＣＤ
                                        AND TT2.適用開始日<='$Evacuation[0]'
                                    )= TT.適用開始日
                                    , 1, 0
                                ) AS 状況
                            FROM
                                得意先単価マスタ新 TT
                            WHERE
                                    TT.得意先ＣＤ='$Evacuation[1]'
                        )
                        SELECT 単価 FROM PRICE_MASTER WHERE 状況=1 and 商品ＣＤ='$Evacuation[3]'
                    ");
                    if (count($value)==0) {
                        $Errors[]=$i.'行目 得意先単価が未設定か、適用開始日の指定が無効です。';
                    }
                }

                //数量
                if(!$Evacuation[5] && ($Evacuation[5]!==0) && ($Evacuation[5]!=='0')){
                    $Errors[]=''.$i.'行目 数量が入力されていません。';
                }else{
                    //入力値妥当性チェック
                    if(!ctype_digit($Evacuation[5])){
                        $Errors[]=''.$i.'行目 数量は数値で入力して下さい。';
                    }
                }
            }

            //発生したエラーのうち先頭5件のみ表示する
            $arrRet = array_slice($Errors,0,5);
            if(count($Errors)>5){
                $arrRet[]='その他エラーあり';
            }
            return array('last_data_row' => $last_data_row, 'error_message'=> implode('<br/>', $arrRet));
        } catch (Exception $ex) {
            return response()->json([
                'result' => false,
                "DataCheckNo" => 0,
                "message" => '取込に失敗しました。',
                "exception" => $ex,
            ]);
        }
    }
    /**
     * Save
     */
    public function Save($request)
    {
        $params = $request->all();
        $SaveList = $params['SaveList'];
        $FileName = $params['FileName'];
        $Manager = $params['Manager'];
        $Contents = $request->Contents;
        $TargetDate = $request->TargetDate;
        $Existenceflg = 0;

        $date = Carbon::now()->format('Y-m-d H:i:s');
        $date_t = Carbon::now()->format('H:i:s');
        $CustomerCd = "";

        DB::beginTransaction();
        try {
            //注文情報登録
            foreach ($SaveList as $rec) {

                IF ($CustomerCd <> $rec['得意先ＣＤ']){
                    $i = 0;

                    // CSVで読み込まれた見込入力の配送日と得意先ＣＤが一致するデータの確認
                    $exists = 注文データ::query()
                        ->where('注文区分', 1)
                        ->where('配送日', $rec['配送日'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->get();

                    // CSVで読み込まれた見込入力の配送日と得意先ＣＤが一致するデータを削除する
                    IF (count($exists) > 0) {

                        注文データ::query()
                        ->where('注文区分', 1)
                        ->where('配送日', $rec['配送日'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->delete();
                    }
                };

                $i = $i + 1;
                $CustomerCd = $rec['得意先ＣＤ'];

                //注文データへ登録
                $rec['注文区分'] = 1;
                $rec['注文日付'] = $date;
                $rec['注文時間'] = $date_t;

                // 部署CD取得
                $r = 得意先マスタ::query()
                    ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                    ->get();
                $rec['部署CD'] = $r[0]->部署CD;

                $rec['明細行No'] = $i;

                // 商品区分取得
                $r = 商品マスタ::query()
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->get();
                $rec['商品区分'] = $r[0]->商品区分;

                $rec['入力区分'] = 0;
                $rec['現金個数'] = 0;
                $rec['現金金額'] = 0;


                // 単価取得(予備金額1に登録する値)
                $r = 得意先単価マスタ新::query()
                    ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                    ->where('商品ＣＤ', $rec['商品ＣＤ'])
                    ->where('適用開始日', '<=', $date)
                    ->latest('適用開始日')
                    ->get();
                    $rec['予備金額1'] = $r[0]->単価;

                $rec['掛売金額'] = $rec['予備金額1'] * $rec['掛売個数'];
                $rec['予備金額2'] = 0;
                $rec['予備CD1'] = 0;
                $rec['予備CD2'] = 0;
                $rec['修正担当者CD'] = $rec['修正担当者CD'];
                $rec['修正日'] = $date;

                注文データ::insert($rec);
            }

            //外部受注システム取込履歴へ登録
            $rec2['ファイル名'] = $FileName;
            $rec2['取込日付'] = $date;
            $rec2['担当者CD'] = $Manager;
            外部受注システム取込履歴::insert($rec2);

            DB::commit();

            //更新後内容返却
            return $this->GetResult($Contents, $TargetDate, $Existenceflg);
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }
}
