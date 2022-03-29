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
use PDO;

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
        $arrAlignList=array();

        $pdo = null;
        try {
            $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
            $user = 'daiyas';
            $password = 'daiyas';
        
            $pdo = new PDO($dsn, $user, $password);
        
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
            $pdo->beginTransaction();

            //注文情報登録
            foreach ($SaveList as $rec) {

                if($CustomerCd <> $rec['得意先ＣＤ']){
                    $i = 0;

                    $DeleteSql = "DELETE FROM 注文データ　WHERE 注文区分=?　AND 得意先ＣＤ=?　AND 配送日=?";
                    $stmt = $pdo->prepare($DeleteSql);
                    $stmt->execute(array(0,$rec['得意先ＣＤ'],$rec['配送日'],));                        
                };

                $i++;
                $CustomerCd = $rec['得意先ＣＤ'];

                //注文データへ登録
                $rec['注文区分'] = 0;
                $rec['注文日付'] = $date;
                $rec['注文時間'] = $date_t;

                // 部署CD取得
                $SelectSql = "SELECT 部署CD FROM 得意先マスタ WHERE 得意先ＣＤ = ?";
                $stmt = $pdo->prepare($SelectSql);
                $stmt->execute(array($rec['得意先ＣＤ']));
                $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (count($r)>0) {
                    $rec['部署ＣＤ'] = $r[0]['部署CD'];
                }

                $rec['明細行Ｎｏ'] = $i;

                // 商品区分取得
                $SelectSql = "SELECT 商品区分 FROM 商品マスタ WHERE 商品ＣＤ = ?";
                $stmt = $pdo->prepare($SelectSql);
                $stmt->execute(array($rec['商品ＣＤ']));
                $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (count($r)>0) {
                    $rec['商品区分'] = $r[0]['商品区分'];
                }

                $rec['入力区分'] = 0;
                $rec['現金個数'] = 0;
                $rec['現金金額'] = 0;

                // 単価取得(予備金額1に登録する値)
                $SelectSql = "
                    WITH PRICE_MASTER AS(
                        SELECT
                            TT.*
                            ,IIF(
                                (
                                    SELECT MAX(TT2.適用開始日)
                                    FROM 得意先単価マスタ新 TT2
                                    WHERE TT2.得意先ＣＤ=TT.得意先ＣＤ
                                    AND TT2.商品ＣＤ=TT.商品ＣＤ
                                    AND TT2.適用開始日<=?
                                )= TT.適用開始日
                                , 1, 0
                            ) AS 状況
                        FROM
                            得意先単価マスタ新 TT
                    )
                    select 単価 from PRICE_MASTER where 状況=1 and 得意先ＣＤ=? and 商品ＣＤ=?
                ";
                $stmt = $pdo->prepare($SelectSql);
                $stmt->execute(array($date,$rec['得意先ＣＤ'],$rec['商品ＣＤ']));
                $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (count($r)>0) {
                    $rec['予備金額１'] = $r[0]['単価'];
                }
                $rec['掛売金額'] = $rec['予備金額１'] * $rec['掛売個数'];
                $rec['予備金額２'] = 0;
                $rec['予備ＣＤ１'] = 0;
                $rec['予備ＣＤ２'] = 0;
                $rec['修正担当者ＣＤ'] = $rec['修正担当者CD'];
                $rec['修正日'] = $date;

                //注文データへ登録
                if (!!isset($rec['現金個数']) && !!isset($rec['掛売個数'])) {
                    $this->SaveOrderData($rec,$date,$i,$pdo);

                    $ali_found=false;
                    foreach($arrAlignList as $ali_val){
                        if(($ali_val['配送日']==$rec['配送日']) && ($ali_val['得意先ＣＤ']==$rec['得意先ＣＤ'])){
                            $ali_found=true;
                            break;
                        }
                    }
                    if (!$ali_found) {
                        $arrAlignList[]=array('配送日'=>$rec['配送日'],'部署ＣＤ'=>$rec['部署ＣＤ'],'得意先ＣＤ'=>$rec['得意先ＣＤ']);
                    }
                }
            }

            //モバイル連携データを登録
            foreach ($arrAlignList as $ali_val) {
                /*メッセージ構成
                $message_json="
                        {
                            'department_code': vue.viewModel.BushoCd,
                            'course_code': vue.viewModel.CourseCd,
                            'custom_data': {
                                'message': '注文変更: ' + vue.viewModel.CustomerNm
                                    + (!!vue.viewModel.BikouForNotification ? ('\n' + vue.viewModel.BikouForNotification) : '')
                                    + (!!vue.viewModel.BikouForDelivery ? ('\n' + vue.viewModel.BikouForDelivery) : '')
                                ,
                                'values': '',
                            },
                        ";
                */
                // 得意先情報を取得
                $arrCustomerInfo = $this->GetCustomerInfo($ali_val['配送日'],$ali_val['得意先ＣＤ'],$pdo);
                $customer_name = $arrCustomerInfo['得意先名略称'];
                $CourseCd=$arrCustomerInfo['コースＣＤ'];

                $arrBikou = $this->GetBikou($ali_val['配送日'],$ali_val['得意先ＣＤ'],$pdo);
                $message='注文変更: ' . $customer_name;
                if(!empty($arrBikou['備考通知'])){
                    $message . "\n" . $arrBikou['備考通知'];
                }
                if(!empty($arrBikou['備考配送'])){
                    $message . "\n" . $arrBikou['備考配送'];
                }

                $req['Message']['department_code']=$ali_val['部署ＣＤ'];
                $req['Message']['course_code']=$CourseCd;
                $req['Message']['custom_data']['message']=$message;
                $req['Message']['custom_data']['values']='';
                
                $this->SaveMobileSendData($ali_val['配送日'], $ali_val['部署ＣＤ'], $CourseCd, $ali_val['得意先ＣＤ'], $req, $pdo);
            }

            //外部受注システム取込履歴へ登録
            $this->SaveImportHistoryData($FileName,$date,$Manager,$pdo);

            $pdo->commit();

            //更新後内容返却
            return $this->GetResult($Contents, $TargetDate, $Existenceflg);
        } catch (Exception $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    /*
    * 受注データへ登録
    */
    private function SaveOrderData($rec,$date,$i,$pdo)
    {
        //注文データInsert処理
        $data = $rec;
        $data['修正日'] = $date;
        $data['明細行Ｎｏ'] = $i;
        $data['備考１'] = $data['備考１'] ?? '';
        $data['備考２'] = $data['備考２'] ?? '';
        $data['備考３'] = $data['備考３'] ?? '';
        $data['備考４'] = $data['備考４'] ?? '';
        $data['備考５'] = $data['備考５'] ?? '';
        $data['特記_社内用'] = $data['特記_社内用'] ?? '';
        $data['特記_配送用'] = $data['特記_配送用'] ?? '';
        $data['特記_通知用'] = $data['特記_通知用'] ?? '';

        $InsertSql = "
            INSERT INTO 注文データ
            VALUES (
                ?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
                ,?
            )
        ";

        $stmt = $pdo->prepare($InsertSql);
        $stmt->execute(
            array(
                $data['注文区分'],
                $data['注文日付'],
                $data['注文時間'],
                $data['部署ＣＤ'],
                $data['得意先ＣＤ'],
                $data['配送日'],
                $data['明細行Ｎｏ'],
                $data['商品ＣＤ'],
                $data['商品区分'],
                $data['入力区分'],
                $data['現金個数'],
                $data['現金金額'],
                $data['掛売個数'],
                $data['掛売金額'],
                $data['備考１'],
                $data['備考２'],
                $data['備考３'],
                $data['備考４'],
                $data['備考５'],
                $data['予備金額１'],
                $data['予備金額２'],
                $data['予備ＣＤ１'],
                $data['予備ＣＤ２'],
                $data['修正担当者ＣＤ'],
                $data['修正日'],
                $data['Web受注ID'] ?? null,
                $data['特記_社内用'],
                $data['特記_配送用'],
                $data['特記_通知用']
            )
        );

    }

    /*
    * 外部受注システム取込履歴へ登録
    */
    private function SaveImportHistoryData($FileName,$date,$Manager,$pdo)
    {
        $rec2['ファイル名'] = $FileName;
        $rec2['取込日付'] = $date;
        $rec2['担当者CD'] = $Manager;
        $InsertSql = "INSERT INTO 外部受注システム取込履歴 VALUES (?,?,?)";

        $stmt = $pdo->prepare($InsertSql);
        $stmt->execute(
            array(
                $rec2['ファイル名'],
                $rec2['取込日付'],
                $rec2['担当者CD']
            )
        );            
    }

    /**
     * 得意先情報を取得する
     * DAI01030GetCustomerInfo.phpのSQLを全てコピーする
     */
    private function GetCustomerInfo($DeliveryDate,$CustomerCd,$pdo)
    {
        $sql = "
            SELECT
                M1.部署ＣＤ,
                MB.部署名,
                M1.得意先ＣＤ,
                M1.得意先名,
                M1.得意先名略称,
                M1.得意先名カナ,
                M1.売掛現金区分,
                M1.電話番号１,
                M1.備考１,
                M1.備考２,
                M1.備考３,
                MC.コースＣＤ,
                MC.コース名,
                MC.コース区分,
                MC.管理ＣＤ,
                MC.一時フラグ,
                MC.担当者ＣＤ,
                MT.担当者名
            FROM
                得意先マスタ M1
                LEFT OUTER JOIN 部署マスタ MB
                    ON MB.部署ＣＤ = M1.部署ＣＤ
                LEFT OUTER JOIN 祝日マスタ MH
                    ON  MH.対象日付 = '$DeliveryDate'
                    AND (対象部署ＣＤ IS NULL OR 対象部署ＣＤ LIKE '%' + CONVERT(varchar,MB.部署ＣＤ) + '%')
                LEFT OUTER JOIN (
                    SELECT
                        CT.部署ＣＤ
                        ,CT.コースＣＤ
                        ,CT.管理ＣＤ
                        ,CTC.一時フラグ
                        ,CM.コース名
                        ,CM.コース区分
                        ,CM.担当者ＣＤ
                        ,CT.得意先ＣＤ
                    FROM
                        (
                            SELECT
                                部署ＣＤ, コースＣＤ, 0 AS 管理ＣＤ, ＳＥＱ, 得意先ＣＤ, 修正担当者ＣＤ, 修正日
                            FROM
                                コーステーブル
                            UNION ALL
                            SELECT
                                部署ＣＤ, コースＣＤ, 管理ＣＤ, ＳＥＱ, 得意先ＣＤ, 修正担当者ＣＤ, 修正日
                            FROM
                                コーステーブル一時
                        ) CT
                            INNER JOIN (
                                SELECT
                                    *
                                FROM (
                                    SELECT
                                        部署ＣＤ
                                        ,コースＣＤ
                                        ,管理ＣＤ
                                        ,一時フラグ
                                        ,RANK() OVER(PARTITION BY 部署ＣＤ, コースＣＤ ORDER BY 一時フラグ DESC) AS RNK
                                    FROM
                                        コーステーブル管理
                                    WHERE
                                        適用開始日 <= '$DeliveryDate' AND 適用終了日 >= '$DeliveryDate'
                                ) X
                                WHERE
                                    RNK = 1
                            ) CTC
                                ON  CTC.部署ＣＤ=CT.部署ＣＤ
                                AND CTC.コースＣＤ=CT.コースＣＤ
                                AND CTC.管理ＣＤ=CT.管理ＣＤ
                        LEFT JOIN コースマスタ CM
                            ON  CM.部署ＣＤ = CTC.部署ＣＤ
                            AND CM.コースＣＤ = CTC.コースＣＤ
                ) MC
                    ON  MC.部署ＣＤ = M1.部署CD
                    AND MC.得意先ＣＤ = M1.得意先ＣＤ
                    AND MC.コース区分 = IIF(MH.対象日付 IS NOT NULL, 4, CASE DATEPART(WEEKDAY, '$DeliveryDate') WHEN 1 THEN 3 WHEN 7 THEN 2 ELSE 1 END)
                LEFT OUTER JOIN 担当者マスタ MT
                    ON MT.担当者ＣＤ = MC.担当者ＣＤ
            WHERE
                M1.得意先CD = $CustomerCd
            AND (M1.受注得意先ＣＤ = 0 OR M1.受注得意先ＣＤ = M1.得意先ＣＤ)
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($r)>0) {
            return $r[0];
        }
    }

    /**
     * 備考を取得する
     * DAI01030GetBikou.phpのSQLを全てコピーする
     */
    private function GetBikou($DeliveryDate,$CustomerCd,$pdo)
    {
        $sql = "
            SELECT
                TK.得意先ＣＤ
                ,ISNULL(CD.特記_社内用, TK.備考１) AS 備考社内
                ,ISNULL(CD.特記_配送用, TK.備考２) AS 備考配送
                ,ISNULL(CD.特記_通知用, TK.備考３) AS 備考通知
                ,CD.注文区分
            FROM
                得意先マスタ TK
                LEFT OUTER JOIN 注文データ CD
                    ON  CD.得意先ＣＤ = TK.得意先ＣＤ
                    AND CD.配送日 = '$DeliveryDate'
                    AND CD.注文区分=0
            WHERE
                TK.得意先ＣＤ = $CustomerCd
            ORDER BY
                CD.注文区分 DESC
                ,CD.商品ＣＤ
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($r)>0) {
            return $r[0];
        }
    }

    /**
     * モバイル送信予定データを作成する
     * DAI01030Save.phpのモバイル連携データ送信処理を全てコピーする
     */
    private function SaveMobileSendData($DeliveryDate,$BushoCd,$course_cd,$CustomerCd,$req,$pdo)
    {
        //事前に実行するSQLを作成(既存の注文データを削除)
        $DeliveryDate = date('Y/m/d', strtotime($DeliveryDate));

        $send_sql = "delete from OrderData where department_code = $BushoCd and customer_code = $CustomerCd and delivery_date='$DeliveryDate';";

        //メインのSQL
        $table_sql = "select * FROM 注文データ WHERE 部署ＣＤ = $BushoCd AND 得意先ＣＤ = $CustomerCd AND 配送日='$DeliveryDate'";
        $stmt = $pdo->query($table_sql);
        $table_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $public_path="C:/daiyas/workspace/daiyas/public";
        $map = json_decode(file_get_contents($public_path."/dbmapping/pwa.txt"),true);
        $map = $map["注文データ"];

        $stmt = $pdo->query($table_sql);
        $table_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        //送信用SQLを生成
        foreach($table_data as $record_data)
        {
            foreach($record_data as $key=>$val)
            {
                if(array_key_exists($key,$map["Field"]))
                {
                    $new_key=$map["Field"][$key];
                    $new_data[$new_key]=$val;
                }
            }

            //SQLの作成
            $new_table_name=$map["TableName"];
            $fields="";
            $values="";
            foreach($new_data as $key=>$val)
            {
                $fields .= ", $key";
                //値がNULLなら文字列NULL、それ以外はシングルクォート付きの値で戻す
                $q_val = $val===NULL ? "null" : "'$val'";
                $values .= ", $q_val";
            }
            $fields=substr($fields,1);
            $values=substr($values,1);
            $send_sql .= "insert into $new_table_name ( $fields )values( $values );";
        }

        //事後に実行するSQL(モバイル予測入力、モバイル販売入力の更新)
        //注文数の取得
        $order_num_sql="select 商品ＣＤ,(sum(isnull(現金個数,0)) + sum(isnull(掛売個数,0)))as 注文個数 from 注文データ where 注文区分=0 and 部署ＣＤ = $BushoCd and 得意先ＣＤ = $CustomerCd and 配送日='$DeliveryDate' group by 商品ＣＤ";
        $stmt = $pdo->query($order_num_sql);
        $order_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($order_list as $order_data)
        {
            $send_sql .= "update SaleInputData set
                updated_at=now()
                ,order_num={$order_data['注文個数']}
                where department_code=$BushoCd
                and customer_code=$CustomerCd
                and product_code={$order_data['商品ＣＤ']}
                and date='$DeliveryDate';";
            $send_sql .= "update SaleInputData set
                achievements_input=0
                ,order_input=1
                where department_code=$BushoCd
                and customer_code=$CustomerCd
                and date='$DeliveryDate';";
            $send_sql .= "update ExpectedInputData set
                updated_at=now()
                ,order_num={$order_data['注文個数']}
                ,order_input=1
                where department_code=$BushoCd
                and customer_code=$CustomerCd
                and product_code={$order_data['商品ＣＤ']}
                and date='$DeliveryDate';";
        }

        //モバイル送信リストに書き込むデータの作成
        $controller_id="DAI01270";
        $method_name="SaveMobileSendData";
        $q_BushoCd   = $BushoCd;
        $q_CustomerCd= $CustomerCd;
        $q_course_cd  = empty($course_cd) ? 'null' : $course_cd;
        $esc_sql=str_replace("'","''",$send_sql);
        //メッセージの作成
        $Message = null;
        $now = date("Y/m/d");
        if ($DeliveryDate == $now) {
            //当日注文の場合、通知
            $Message = $req['Message'];
        }
        $q_notify_message  = empty($Message) ? '' : json_encode($Message);

        //モバイル送信リストに書き込む
        $while_cnt=0;//SQLが成功するまでループさせるが、念のため最大5回まで試行する。
        while ($while_cnt<5) {
            //送信IDを取得
            $next_id_Sql = "SELECT MAX(送信ＩＤ)+1 AS NEXT_ID FROM モバイル送信リスト";
            $stmt = $pdo->query($next_id_Sql);
            $send_id = $stmt->fetch()["NEXT_ID"];
            $send_id = $send_id==null ? 1 : $send_id;

            $ms_sql="INSERT INTO モバイル送信リスト(
                    送信ＩＤ
                ,部署ＣＤ
                ,得意先ＣＤ
                ,コースＣＤ
                ,コントローラＩＤ
                ,メソッド名
                ,作成日時
                ,SQL
                ,通知メッセージ
                ,送信済フラグ
                ,送信済日時
                )VALUES(
                    $send_id
                    ,$q_BushoCd
                    ,$q_CustomerCd
                    ,$q_course_cd
                    ,'$controller_id'
                    ,'$method_name'
                    ,GETDATE()
                    ,'$esc_sql'
                    ,'$q_notify_message'
                    ,0
                    ,null
                )
                ";
            try {
                $pdo->exec($ms_sql);
                break;//SQLが実行出来たらWhileを抜ける
            } catch (Exception $e) {
                //エラーが発生したらログを採取する
                $path='C:/daiyas/workspace/daiyas/storage/logs/';
                $timestamp_er=date("Y/m/d H:i:s");
                $timestamp_fn=str_replace('.', '', microtime(true));
                error_log("[" . $timestamp_er ."] ". $e->getMessage(), 3, $path."dai01030save_error_".$timestamp_fn.".log");
                $while_cnt++;
            }
        }
    }
}
