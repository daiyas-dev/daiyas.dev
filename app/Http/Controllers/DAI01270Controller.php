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
                $TargetDate = $request->TargetDate;

                $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
                $user = 'daiyas';
                $password = 'daiyas';
                $pdo = new PDO($dsn, $user, $password);

                $file = $request->file('file');
                $FileName = $file->getClientOriginalName();
                $Existenceflg = 0;

                //外部受注システム取込履歴チェック確認
                $stmt = $pdo->query("
                    SELECT
                        ファイル名
                    FROM
                        外部受注システム取込履歴
                    WHERE
                        ファイル名='$FileName'
                ");
                $value = $stmt->fetch(PDO::FETCH_ASSOC);
                if($value){
                    $Existenceflg = 1;
                };

                return $this->GetResult($Contents, $TargetDate, $Existenceflg);
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
    public function GetResult($Contents, $TargetDate, $Existenceflg)
    {

        $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
        $user = 'daiyas';
        $password = 'daiyas';
        $pdo = new PDO($dsn, $user, $password);

        try {

            $Lines = explode("\r\n", $Contents);

            $Company = (object)[];
            $Customers = [];

            $ErrorList = array();
            $ErrorList = $this->CSVCheck($pdo,$Contents,$ErrorList);

            foreach ($Lines as $i => $Line) {
                $Evacuation = (object)[];
                $Customer = (object)[];

                $Evacuation = explode(",", $Line);

                //CSV2行目からスタート
                IF ($i <> 0) {
                    IF (($ErrorList[$i]->半角カンマ数 <> 'NG') AND ($ErrorList[$i]->InputErrorCount <> 4)) {
                        $Customer->行番号 = $i;
                        $Customer->配送日 = $Evacuation[0];

                        $Customer->得意先CD = $Evacuation[1];
                        IF ($ErrorList[$i]->得意先CD <> 'NG') {
                            IF ($Evacuation[1] <> "") {
                                $stmt = $pdo->query("
                            SELECT
                                得意先名略称
                            FROM
                                得意先マスタ
                            WHERE
                                得意先ＣＤ=$Customer->得意先CD
                            ");
                                $name = $stmt->fetch(PDO::FETCH_ASSOC);

                                IF ($name) {
                                    $Customer->顧客名 = $name["得意先名略称"];
                                };
                            };
                        };

                        $Customer->商品CD = $Evacuation[3];
                        IF ($ErrorList[$i]->商品CD <> 'NG') {
                            IF ($Evacuation[3] <> "") {
                                $stmt = $pdo->query("
                            SELECT
                                商品名
                            FROM
                                商品マスタ
                            WHERE
                                商品ＣＤ=$Customer->商品CD
                            ");
                                $name = $stmt->fetch(PDO::FETCH_ASSOC);

                                IF ($name) {
                                    $Customer->商品名 = $name["商品名"];
                                }
                            };
                        };

                        $Customer->数量 = $Evacuation[5];

                        array_push($Customers, $Customer);
                    };
                };

            };

            $message = $ErrorList[$i+1];
            //エラー表示を含んで値を返す
            IF ($message <> ""){
                return response()->json([
                    'result' => false,
                    'Contents' => $Contents,
                    'Company' => $Company,
                    'Customers' => $Customers,
                    'Existenceflg' => $Existenceflg,
                    "DataCheckNo" => 1,
                    "message" => $message,
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
            $pdo = null;
        }
    }

    /**
     * CSVCheck
     */
    public function CSVCheck($pdo,$Contents,$ErrorList)
    {
        try {

            $Lines = explode("\r\n", $Contents);
            $message = "";
            $ErrorCount = 0;

            foreach ($Lines as $i => $Line) {
                $Evacuation = (object)[];
                $Errorflg = (object)[];
                $InputErrorCount = 0;
                $NeedErrorNo = array();
                $ErrorNo = array();

                /*解説
                    $ErrorCount = エラーメッセージの数
                    $Errorflg = 必須入力の「OK」or「NG」
                    $InputErrorCount = 必須入力のエラー数
                    $NeedErrorNo = 必須入力エラーメッセージ作成用の番号
                    $NeedErrorNo = "5"まで使用 2022/03/25時点
                    $ErrorNo = エラーメッセージ作成用の番号
                    $ErrorNo = "14"まで使用 2022/03/25時点
                */

                //CSV2行目からスタート
                $Errorflg->RowNo = $i;
                IF ($i <> 0) {

                    //必須入力チェック
                    //「,」の個数チェック
                    $WordCount = mb_substr_count($Line, ",");
                    IF ($WordCount <> 5) {
                        IF ($WordCount <> 0){
                            IF ($ErrorCount == 0) {
                                $NeedErrorNo[] = "1";
                            } elseIF ($ErrorCount < 5) {
                                $NeedErrorNo[] = "1a";
                            };
                        };
                        $ErrorCount = $ErrorCount + 1;
                        $Errorflg->半角カンマ数 = 'NG';
                    }else{
                        $Errorflg->半角カンマ数 = 'OK';
                    };
                    //半角カンマ数 = NGなら取込行として不正のためスキップ
                    IF ($Errorflg->半角カンマ数 <> 'NG'){
                        $Evacuation = explode(",", $Line);

                        //配送日必須入力チェック
                        if(empty($Evacuation[0])){
                            IF ($ErrorCount == 0){
                                $NeedErrorNo[] = "2";
                            }elseIF ($ErrorCount < 5){
                                $NeedErrorNo[] = "2a";
                            };
                            $ErrorCount = $ErrorCount + 1;
                            $InputErrorCount = $InputErrorCount + 1;
                            $Errorflg->配送日 = 'NG';
                        }else{
                            $Errorflg->配送日 = 'OK';
                        };

                        //得意先CD必須入力チェック
                        IF (empty($Evacuation[1])) {
                            IF ($ErrorCount == 0) {
                                $NeedErrorNo[] = "3";
                            } elseIF ($ErrorCount < 6) {
                                $NeedErrorNo[] = "3a";
                            };
                            $ErrorCount = $ErrorCount + 1;
                            $InputErrorCount = $InputErrorCount + 1;
                            $Errorflg->得意先CD = 'NG';
                        }else{
                            $Errorflg->得意先CD = 'OK';
                        };

                        //商品CD必須入力チェック
                        if(empty($Evacuation[3])){
                            IF ($ErrorCount == 0){
                                $NeedErrorNo[] = "4";
                            }elseIF ($ErrorCount < 5){
                                $NeedErrorNo[] = "4a";
                            };
                            $ErrorCount = $ErrorCount + 1;
                            $InputErrorCount = $InputErrorCount + 1;
                            $Errorflg->商品CD = 'NG';
                        }else{
                            $Errorflg->商品CD = 'OK';
                        };

                        //数量必須入力チェック
                        if((empty($Evacuation[5]))){
                            IF ($ErrorCount == 0){
                                $NeedErrorNo[] = "5";
                            }elseIF ($ErrorCount < 5){
                                $NeedErrorNo[] = "5a";
                            };
                            $ErrorCount = $ErrorCount + 1;
                            $InputErrorCount = $InputErrorCount + 1;
                            $Errorflg->数量 = 'NG';
                        }else{
                            $Errorflg->数量 = 'OK';
                        };

                        /*
                            TODO
                            以下の「 IF($InputErrorCount == 4) {」で
                                落ちているように見えます。
                                 $InputErrorCount == 4
                                 は、必須入力チェックが4件エラー(必須項目空白)になっている
                                 と言う意味で
                                 ４件エラーの場合はエラーカウント(エラーの個数)を「ー４」行い
                                 必須入力メッセージＮＯ（$NeedErrorNo）を空にしたい処理となります
                        */
                        IF ($InputErrorCount == 4) {
                            $ErrorCount = $ErrorCount - 4;
                            $NeedErrorNo = array();
                        }else{
                            //必須入力のいずれかが入力されていれば以下のエラー処理
                            //配送日チェック
                            IF ($Errorflg->配送日 == 'OK'){
                                $WordCount = mb_substr_count($Evacuation[0], "/");
                                IF ($WordCount == 2) {
                                    list($y, $m, $d) = explode('/', $Evacuation[0]);
                                    IF ((!ctype_digit($m)) OR (!ctype_digit($d)) OR (!ctype_digit($y))) {
                                        IF ($ErrorCount == 0){
                                            $ErrorNo[] = "1";
                                        }elseIF ($ErrorCount < 5){
                                            $ErrorNo[] = "1a";
                                        };
                                        $ErrorCount = $ErrorCount + 1;
                                        $Errorflg->配送日 = 'NG';
                                    }elseIF (!checkdate($m, $d, $y)) {
                                        IF ($ErrorCount == 0){
                                            $ErrorNo[] = "2";
                                        }elseIF ($ErrorCount < 5){
                                            $ErrorNo[] = "2a";
                                        };
                                        $ErrorCount = $ErrorCount + 1;
                                        $Errorflg->配送日 = 'NG';
                                    };
                                }else{
                                    IF ($ErrorCount == 0){
                                        $ErrorNo[] = "3";
                                    }elseIF ($ErrorCount < 5){
                                        $ErrorNo[] = "3a";
                                    };
                                    $ErrorCount = $ErrorCount + 1;
                                    $Errorflg->配送日 = 'NG';
                                };
                            }

                            //得意先CDチェック
                            IF ((!ctype_digit($Evacuation[1])) OR (mb_strlen($Evacuation[1]) > 7)) {
                                IF ($ErrorCount == 0) {
                                    $ErrorNo[] = "4";
                                } elseIF ($ErrorCount < 6) {
                                    $ErrorNo[] = "4a";
                                };
                                $ErrorCount = $ErrorCount + 1;
                                $Errorflg->得意先CD = 'NG';
                            }else{
                                $Errorflg->得意先CD = 'OK';
                            };

                            //商品CDチェック
                            if(!ctype_digit($Evacuation[3])){
                                IF ($ErrorCount == 0){
                                    $ErrorNo[] = "5";
                                }elseIF ($ErrorCount < 5){
                                    $ErrorNo[] = "5a";
                                };
                                $ErrorCount = $ErrorCount + 1;
                                $Errorflg->商品CD = 'NG';
                            }else{
                                $Errorflg->商品CD = 'OK';
                            };

                            //数量チェック
                            if(!ctype_digit($Evacuation[5])){
                                IF ($ErrorCount == 0){
                                    $ErrorNo[] = "6";
                                }elseIF ($ErrorCount < 5){
                                    $ErrorNo[] = "6a";
                                };
                                $ErrorCount = $ErrorCount + 1;
                                $Errorflg->数量 = 'NG';
                            }else{
                                $Errorflg->数量 = 'OK';
                            };

                            if($Errorflg->得意先CD <> 'NG'){
                                $stmt = $pdo->query("
                                    SELECT
                                        得意先名略称,売掛現金区分
                                    FROM
                                        得意先マスタ
                                    WHERE
                                        得意先ＣＤ='$Evacuation[1]'
                                ");
                                $value = $stmt->fetch(PDO::FETCH_ASSOC);

                                //得意先CDチェック確認
                                if(!$value){
                                    IF ($ErrorCount == 0){
                                        $ErrorNo[] = "7";
                                    }elseIF ($ErrorCount < 5){
                                        $ErrorNo[] = "7a";
                                    };
                                    $ErrorCount = $ErrorCount + 1;
                                };
                                //売掛現金区分の売掛チェック確認
                                if($value["売掛現金区分"] <> 1){
                                    IF ($ErrorCount == 0){
                                        $ErrorNo[] = "8";
                                    }elseIF ($ErrorCount < 5){
                                        $ErrorNo[] = "8a";
                                    };
                                    $ErrorCount = $ErrorCount + 1;
                                };
                            };


                            IF ($Errorflg->商品CD <> 'NG') {
                                //商品ＣＤチェック
                                $stmt = $pdo->query("
                                SELECT
                                    商品名
                                FROM
                                    商品マスタ
                                WHERE
                                    商品ＣＤ='$Evacuation[3]'
                                ");
                                $names = $stmt->fetch(PDO::FETCH_ASSOC);
                                IF (!$names) {
                                    IF ($ErrorCount == 0) {
                                        $ErrorNo[] = "9";
                                    } elseIF ($ErrorCount < 6) {
                                        $ErrorNo[] = "9a";
                                    };
                                    $ErrorCount = $ErrorCount + 1;
                                };
                            };

                            IF ($Errorflg->得意先CD <> 'NG' AND $Errorflg->商品CD <> 'NG') {
                                //得意先単価マスタ新チェック
                                $stmt = $pdo->query("
                                SELECT
                                    適用開始日
                                FROM
                                    得意先単価マスタ新
                                WHERE
                                    商品ＣＤ='$Evacuation[3]'
                                    AND
                                    得意先ＣＤ='$Evacuation[1]'
                                ");
                                $value = $stmt->fetch(PDO::FETCH_ASSOC);

                                IF (!$value) {
                                    IF ($ErrorCount == 0) {
                                        $ErrorNo[] = "10";
                                    } elseIF ($ErrorCount < 6) {
                                        $ErrorNo[] = "10a";
                                    };
                                    $ErrorCount = $ErrorCount + 1;
                                };

                                IF ($Errorflg->配送日 <> 'NG' AND $value["適用開始日"] > $Evacuation[0]) {
                                    IF ($ErrorCount == 0) {
                                        $ErrorNo[] = "11";
                                    } elseIF ($ErrorCount < 6) {
                                        $ErrorNo[] = "11a";
                                    };
                                    $ErrorCount = $ErrorCount + 1;
                                };
                            };
                        };
                    };
                    $Errorflg->InputErrorCount =  $InputErrorCount;


                    //必須メッセージ作成
                    if(!(is_array($NeedErrorNo) && empty($NeedErrorNo))){
                        for ($j = 0 ; $j < count($NeedErrorNo); $j++) {
                            IF ($NeedErrorNo[$j] == "1") {
                                $message = '取込ファイルの'.$i.'行目の項目を区切る半角カンマの数をご確認下さい。';
                            }
                            IF ($NeedErrorNo[$j] == "1a") {
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の項目を区切る半角カンマの数をご確認下さい。';
                            }
                            IF ($NeedErrorNo[$j] == "2") {
                                $message = '注文情報ファイルの'.$i.'行目の配送日が入力されていません。';
                            }
                            IF ($NeedErrorNo[$j] == "2a") {
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の配送日が入力されていません。';
                            }
                            IF ($NeedErrorNo[$j] == "3") {
                                $message = '注文情報ファイルの'.$i.'行目の得意先CDが入力されていません。';
                            }
                            IF ($NeedErrorNo[$j] == "3a") {
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の得意先CDが入力されていません。';
                            }
                            IF ($NeedErrorNo[$j] == "4") {
                                $message = '注文情報ファイルの'.$i.'行目の商品CDが入力されていません。';
                            }
                            IF ($NeedErrorNo[$j] == "4a") {
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の商品CDが入力されていません。';
                            }
                            IF ($NeedErrorNo[$j] == "5") {
                                $message = '注文情報ファイルの'.$i.'行目の数量が正しく入力されていません。';
                            }
                            IF ($NeedErrorNo[$j] == "5a") {
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の数量が正しく入力されていません。';
                            }
                        };
                    }

                    //メッセージ作成
                    IF (!(is_array($ErrorNo) && empty($ErrorNo))) {
                        for ($j = 0 ; $j < count($ErrorNo); $j++) {
                            if($ErrorNo[$j] == "1"){
                                $message = '注文情報ファイルの'.$i.'行目の配送日が数字ではありません。';
                            }
                            if($ErrorNo[$j] == "1a"){
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の配送日が数字ではありません。';
                            }
                            if($ErrorNo[$j] == "2"){
                                $message = '注文情報ファイルの'.$i.'行目の配送日が正しく入力されていません。';
                            }
                            if($ErrorNo[$j] == "2a"){
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の配送日が正しく入力されていません。';
                            }
                            if($ErrorNo[$j] == "3"){
                                $message = '注文情報ファイルの'.$i.'行目の配送日が正しく入力されていません。';
                            }
                            if($ErrorNo[$j] == "3a"){
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の配送日が正しく入力されていません。';
                            }
                            if($ErrorNo[$j] == "4"){
                                $message = '注文情報ファイルの'.$i.'行目の得意先CDが正しく入力されていません。';
                            }
                            if($ErrorNo[$j] == "4a"){
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の得意先CDが正しく入力されていません。';
                            }
                            if($ErrorNo[$j] == "5"){
                                $message = '注文情報ファイルの'.$i.'行目の商品CDが正しく入力されていません。';
                            }
                            if($ErrorNo[$j] == "5a"){
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の商品CDが正しく入力されていません。';
                            }
                            if($ErrorNo[$j] == "6"){
                                $message = '注文情報ファイルの'.$i.'行目の数量が正しく入力されていません。';
                            }
                            if($ErrorNo[$j] == "6a"){
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の数量が正しく入力されていません。';
                            }
                            if($ErrorNo[$j] == "7"){
                                $message = '注文情報ファイルの'.$i.'行目の得意先CDが存在しません。';
                            }
                            if($ErrorNo[$j] == "7a"){
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の得意先CDが存在しません。';
                            }
                            if($ErrorNo[$j] == "8"){
                                $message = '注文情報ファイルの'.$i.'行目の得意先CDが売掛ではありません。';
                            }
                            if($ErrorNo[$j] == "8a"){
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の得意先CDが売掛ではありません。';
                            }
                            if($ErrorNo[$j] == "9"){
                                $message = '注文情報ファイルの'.$i.'行目の商品CDが存在しません。';
                            }
                            if($ErrorNo[$j] == "9a"){
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の商品CDが存在しません。';
                            }
                            if($ErrorNo[$j] == "10"){
                                $message = '注文情報ファイルの'.$i.'行目の得意先単価マスタへ登録されていません。';
                            }
                            if($ErrorNo[$j] == "10a"){
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の得意先単価マスタへ登録されていません。';
                            }
                            if($ErrorNo[$j] == "11"){
                                $message = '注文情報ファイルの'.$i.'行目の配送日が適用開始日より前の日付です。';
                            }
                            if($ErrorNo[$j] == "11a"){
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の配送日が適用開始日より前の日付です。';
                            }
                        };
                    }
                }
                $ErrorList[$i] = $Errorflg;
            }

            IF($ErrorCount > 5){
                $message = $message = $message .'<br>'.'その他エラーあり';
            };
            $ErrorList[] = $message;

            return $ErrorList;
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
