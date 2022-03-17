<?php

namespace App\Http\Controllers;

use App\Models\モバイル対象商品;
use App\Models\注文データ;
use App\Models\部署マスタ;
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
            if ($request->file('file')->isValid([])) {
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
                if ($i <> 0) {
                    if (($ErrorList[$i]->半角カンマ数 <> 'NG') AND ($ErrorList[$i]->InputErrorCount <> 4)) {
                        $Customer->行番号 = $i;
                        $Customer->配送日 = $Evacuation[0];

                        $Customer->得意先CD = $Evacuation[1];
                        if ($ErrorList[$i]->得意先CD <> 'NG') {
                            if ($Evacuation[1] <> "") {
                                $stmt = $pdo->query("
                            SELECT
                                得意先名略称
                            FROM
                                得意先マスタ
                            WHERE
                                得意先ＣＤ=$Customer->得意先CD
                            ");
                                $name = $stmt->fetch(PDO::FETCH_ASSOC);

                                if ($name) {
                                    $Customer->顧客名 = $name["得意先名略称"];
                                };
                            };
                        };

                        $Customer->商品CD = $Evacuation[3];
                        if ($ErrorList[$i]->商品CD <> 'NG') {
                            if ($Evacuation[3] <> "") {
                                $stmt = $pdo->query("
                            SELECT
                                商品名
                            FROM
                                商品マスタ
                            WHERE
                                商品ＣＤ=$Customer->商品CD
                            ");
                                $name = $stmt->fetch(PDO::FETCH_ASSOC);

                                if ($name) {
                                    $Customer->商品名 = $name["商品名"];
                                }
                            };
                        };

                        $Customer->数量 = $Evacuation[5];

                        array_push($Customers, $Customer);
                    };
                }
            }

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

                /*解説
                    $ErrorCount = エラーメッセージの数
                    $Errorflg = 必須入力の「OK」or「NG」
                    $InputErrorCount = 必須入力のエラー数
                */

                //CSV2行目からスタート
                $Errorflg->RowNo = $i;
                if ($i <> 0) {
                    //「,」の個数チェック
                    $WordCount = mb_substr_count($Line, ",");
                    if ($WordCount <> 5) {
                        if ($ErrorCount == 0) {
                            $message = '取込ファイルの'.$i.'行目の項目を区切る半角カンマの数をご確認下さい。';
                        } elseIf ($ErrorCount < 5) {
                            $message = $message .'<br>注文情報ファイルの'.$i.'行目の項目を区切る半角カンマの数をご確認下さい。';
                        };
                        $ErrorCount = $ErrorCount + 1;
                        $Errorflg->半角カンマ数 = 'NG';
                    }else{
                        $Errorflg->半角カンマ数 = 'OK';
                    };
                    IF ($Errorflg->半角カンマ数 <> 'NG'){
                        $Evacuation = explode(",", $Line);

                        //配送日チェック
                        $Errorflg->配送日 = 'OK';
                        if((empty($Evacuation[0]))){
                            IF ($ErrorCount == 0){
                                $message = '注文情報ファイルの'.$i.'行目の配送日が入力されていません。';
                            }elseIF ($ErrorCount < 5){
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の配送日が入力されていません。';
                            };
                            $ErrorCount = $ErrorCount + 1;
                            $InputErrorCount = $InputErrorCount + 1;
                            $Errorflg->配送日 = 'NG';
                        }else{
                            $WordCount = mb_substr_count($Evacuation[0], "/");
                            if ($WordCount == 2) {
                                list($y, $m, $d) = explode('/', $Evacuation[0]);
                                if ((!ctype_digit($m)) OR (!ctype_digit($d)) OR (!ctype_digit($y))) {
                                    IF ($ErrorCount == 0){
                                        $message = '注文情報ファイルの'.$i.'行目の配送日が数字ではありません。';
                                    }elseIF ($ErrorCount < 5){
                                        $message = $message .'<br>注文情報ファイルの'.$i.'行目の配送日が数字ではありません。';
                                    };
                                    $ErrorCount = $ErrorCount + 1;
                                    $Errorflg->配送日 = 'NG';
                                }elseif (!checkdate($m, $d, $y)) {
                                    IF ($ErrorCount == 0){
                                        $message = '注文情報ファイルの'.$i.'行目の配送日が正しく入力されていません。';
                                    }elseIF ($ErrorCount < 5){
                                        $message = $message .'<br>注文情報ファイルの'.$i.'行目の配送日が正しく入力されていません。';
                                    };
                                    $ErrorCount = $ErrorCount + 1;
                                    $Errorflg->配送日 = 'NG';
                                };
                            }else{
                                IF ($ErrorCount == 0){
                                    $message = '注文情報ファイルの'.$i.'行目の配送日が正しく入力されていません。';
                                }elseIF ($ErrorCount < 5){
                                    $message = $message .'<br>注文情報ファイルの'.$i.'行目の配送日が正しく入力されていません。';
                                };
                                $ErrorCount = $ErrorCount + 1;
                                $Errorflg->配送日 = 'NG';
                            };
                        };

                        //得意先CDチェック
                        if ((empty($Evacuation[1])) OR (!ctype_digit($Evacuation[1])) OR (mb_strlen($Evacuation[1]) > 7)) {
                            if ($ErrorCount == 0) {
                                $message = '注文情報ファイルの'.$i.'行目の得意先CDが正しく入力されていません。';
                            } elseif ($ErrorCount < 6) {
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の得意先CDが正しく入力されていません。';
                            };
                            $ErrorCount = $ErrorCount + 1;
                            if(empty($Evacuation[1])){
                                $InputErrorCount = $InputErrorCount + 1;
                            };
                            $Errorflg->得意先CD = 'NG';
                        }else{
                            $Errorflg->得意先CD = 'OK';
                        };

                        //商品CDチェック
                        if((empty($Evacuation[3])) OR (!ctype_digit($Evacuation[3]))){
                            IF ($ErrorCount == 0){
                                $message = '注文情報ファイルの'.$i.'行目の商品CDが正しく入力されていません。';
                            }elseIF ($ErrorCount < 5){
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の商品CDが正しく入力されていません。';
                            };
                            $ErrorCount = $ErrorCount + 1;
                            if(empty($Evacuation[3])){
                                $InputErrorCount = $InputErrorCount + 1;
                            };
                            $Errorflg->商品CD = 'NG';
                        }else{
                            $Errorflg->商品CD = 'OK';
                        };

                        //数量チェック
                        if((empty($Evacuation[5])) OR !ctype_digit($Evacuation[5])){
                            IF ($ErrorCount == 0){
                                $message = '注文情報ファイルの'.$i.'行目の数量が正しく入力されていません。';
                            }elseIF ($ErrorCount < 5){
                                $message = $message .'<br>注文情報ファイルの'.$i.'行目の数量が正しく入力されていません。';
                            };
                            $ErrorCount = $ErrorCount + 1;
                            if(empty($Evacuation[4])){
                                $InputErrorCount = $InputErrorCount + 1;
                            };
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
                                    $message = '注文情報ファイルの'.$i.'行目の得意先CDが存在しません。';
                                }elseIF ($ErrorCount < 5){
                                    $message = $message .'<br>注文情報ファイルの'.$i.'行目の得意先CDが存在しません。';
                                };
                                $ErrorCount = $ErrorCount + 1;
                            };
                            //売掛現金区分の売掛チェック確認
                            if($value["売掛現金区分"] <> 1){
                                IF ($ErrorCount == 0){
                                    $message = '注文情報ファイルの'.$i.'行目の得意先CDが売掛ではありません。';
                                }elseIF ($ErrorCount < 5){
                                    $message = $message .'<br>注文情報ファイルの'.$i.'行目の得意先CDが売掛ではありません。';
                                };
                                $ErrorCount = $ErrorCount + 1;
                            };
                        };


                        if ($Errorflg->商品CD <> 'NG') {
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
                            if (!$names) {
                                if ($ErrorCount == 0) {
                                    $message = '注文情報ファイルの'.$i.'行目の商品CDが存在しません。';
                                } elseif ($ErrorCount < 6) {
                                    $message = $message .'<br>注文情報ファイルの'.$i.'行目の商品CDが存在しません。';
                                };
                                $ErrorCount = $ErrorCount + 1;
                            };
                        };

                        if ($Errorflg->得意先CD <> 'NG' AND $Errorflg->商品CD <> 'NG') {
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

                            if (!$value) {
                                if ($ErrorCount == 0) {
                                    $message = '注文情報ファイルの'.$i.'行目の得意先単価マスタ新へ登録されていません。';
                                } elseif ($ErrorCount < 6) {
                                    $message = $message .'<br>注文情報ファイルの'.$i.'行目の得意先単価マスタ新へ登録されていません。';
                                };
                                $ErrorCount = $ErrorCount + 1;
                            };

                            if ($Errorflg->配送日 <> 'NG' AND $value["適用開始日"] > $Evacuation[0]) {
                                if ($ErrorCount == 0) {
                                    $message = '注文情報ファイルの'.$i.'行目の配送日が適用開始日より前の日付です。';
                                } elseif ($ErrorCount < 6) {
                                    $message = $message .'<br>注文情報ファイルの'.$i.'行目の配送日が適用開始日より前の日付です。';
                                };
                                $ErrorCount = $ErrorCount + 1;
                            };
                        };
                    };
                    $Errorflg->InputErrorCount =  $InputErrorCount;
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

                if ($CustomerCd <> $rec['得意先ＣＤ']){
                    $i = 0;

                    // CSVで読み込まれた見込入力の配送日と得意先ＣＤが一致するデータの確認
                    $exists = 注文データ::query()
                        ->where('注文区分', 1)
                        ->where('配送日', $rec['配送日'])
                        ->where('得意先ＣＤ', $rec['得意先ＣＤ'])
                        ->get();

                    // CSVで読み込まれた見込入力の配送日と得意先ＣＤが一致するデータを削除する
                    if (count($exists) > 0) {

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
