<?php

namespace App\Libs;

use App\Models\モバイル予測入力;
use App\Models\モバイル販売入力;
use App\Models\入金データ;
use App\Models\商品マスタ;
use App\Models\得意先マスタ;
use App\Models\得意先単価マスタ新;
use App\Models\注文データ;
use App\Models\管理マスタ;
use Exception;
use Illuminate\Support\Facades\DB;
use PDO;
use Illuminate\Support\Carbon;

//モバイル・Web受注から社内DBへ取込(ベースクラス)
class DataReceiveBase
{
    public $tmp_path;
    public $ConversionMap;
    public $target_server_enum=array("PWA"=>1,"WebOrder"=>2);//AWSのターゲットの選択肢
    public $target_server = null;//AWSのターゲット

    public function __construct()
    {
        //作業用のフォルダを作成する
        $this->tmp_path=base_path()."\\tmp";
        if (!file_exists($this->tmp_path)) {
            mkdir($this->tmp_path);
        }
    }
    /**
     * マッピング情報を読み込む
     */
    public function ReadMap()
    {
        $this->ConversionMap = array();
        switch ($this->target_server) {
            case $this->target_server_enum["PWA"]:
            {
                $this->ConversionMap = json_decode(file_get_contents(public_path()."/dbmapping/pwa.txt"), true);
                break;
            }
            case $this->target_server_enum["WebOrder"]:
            {
                $this->ConversionMap = json_decode(file_get_contents(public_path()."/dbmapping/weborder.txt"), true);
                break;
            }
        }
    }
    /**
     * 指定のURLからzipファイルを取得する
     * @param  string   URL
     * @param  int      受信ID
     * @param  array    postするデータの連想配列
     * @return array    file_path=取得したzipファイルのフルパス,last_update_date=最終更新日時
     */
    public function GetResponse($url, $receive_id, $post_data)
    {
        try {
            // cURLセッションを初期化
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url); // 取得するURLを指定
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 実行結果を文字列で返す
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // サーバー証明書の検証を行わない

            //Post実行
            $ttl = ini_get('max_execution_time');
            set_time_limit(0);
            $result = curl_exec($ch);
            $curl_error=curl_error($ch);

            // セッションを終了
            curl_close($ch);
            set_time_limit($ttl);
            //echo 'RETURN:' . $result;

            //エラーチェック
            if ($curl_error!="") {
                $this->ErrorReceiveList($receive_id, "接続エラー", $curl_error, null);
                return "";
            }
            $arrResult=json_decode($result);
            if ($arrResult==null) {
                $this->ErrorReceiveList($receive_id, "接続エラー", $result, null);
                return "";
            } else {
                if ($arrResult->result==1) {
                    $tmp_file="";
                    if ($arrResult->FileData!=="") {
                        //取得したレスポンスをファイルにする
                        $tmp_file = tempnam($this->tmp_path, "zip");
                        if ($file_handle = fopen($tmp_file, "w")) {
                            // 書き込み
                            fwrite($file_handle, base64_decode($arrResult->FileData));
                            // ファイルを閉じる
                            fclose($file_handle);
                        }
                    }
                    return array("file_path"=>$tmp_file,"last_update_date"=>$arrResult->LastUpdateDate);
                } else {
                    $this->ErrorReceiveList($receive_id, "エラー", base64_decode($arrResult->message), null);
                    return "";
                }
            }
        } catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * ファイルを読み取って、所定のテーブルに格納する
     * @param object (参照)トランザクション
     * @param int    受信ID
     * @param string データファイルフルパス
     * @return object  result: 処理結果 true=成功 / false=エラー有り
     */
    public function DataImport(&$pdo, $receive_id, $data_file_path)
    {
        try {
            //テーブル名を取得
            $table_name = basename($data_file_path, ".txt");
            $table_name = substr($table_name, 0, -4);
            $field_list=null;

            //マッピング情報を取得
            $field_list=$this->getMapping($table_name);
            $cnv_table_name=$field_list['TableName'];

            //1レコードごとにデータを更新
            $table_data = json_decode(file_get_contents($data_file_path), true);
            $new_pk=array();
            $new_data=array();

            $result_info = (object)[];
            $result_info->table_name = $cnv_table_name;
            $result_info->records = [];

            foreach ($table_data as $record) {
                //列情報を取得
                $ret = $this->getRowData($record, $field_list);
                $new_pk=$ret['key'];
                $new_data=$ret['val'];

                //同一情報が存在するか確認
                $where="";
                foreach ($new_pk as $key => $val) {
                    $where .= " AND $key = '$val'";
                }
                $where=substr($where, 5);
                $stmt = $pdo->query("SELECT COUNT(*) AS CNT FROM $cnv_table_name WHERE $where");
                $count = $stmt->fetch()["CNT"];

                $rec_info = (object)[];
                $rec_info->key = $new_pk;
                $rec_info->data = $new_data;
                $rec_info->kind = 0 < $count ? 'update' : 'insert';

                //更新実施
                if (0<$count) {
                    //UPDATE
                    $values="";
                    foreach ($new_data as $key=>$val) {
                        $q_val = ($val===null || $val==='') ? "null" : "'$val'";
                        $values .= ", $key = $q_val";
                    }
                    $values=substr($values, 1);
                    $sql="UPDATE $cnv_table_name SET $values WHERE $where";
                    $pdo->exec($sql);
                } else {
                    //INSERT
                    $fields="";
                    $values="";
                    foreach ($new_data as $key=>$val) {
                        $fields .= ", $key";
                        $q_val = ($val===null || $val==='') ? "null" : "'$val'";
                        $values .= ", $q_val";
                    }
                    $fields=substr($fields, 1);
                    $values=substr($values, 1);
                    $sql="insert into $cnv_table_name ( $fields )values( $values )";
                    $pdo->exec($sql);
                }
                $error_info=$pdo->errorInfo();
                if ($error_info[0]!="00000" || $error_info[1]!=null || $error_info[2]!=null) {
                    //SQLを実行してエラーが発生した場合
                    $this->ErrorReceiveList($receive_id, "SQL実行エラー", $error_info[1]." ".$error_info[2] ." ".$sql, $data_file_path);
                    $result_info->result = false;
                    return $result_info;
                }

                array_push($result_info->records, $rec_info);
            }
            $result_info->result = true;
            return $result_info;
        } catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * ファイルを読み取って、所定のテーブルのレコードを削除する
     * @param object (参照)トランザクション
     * @param int    受信ID
     * @param string データファイルフルパス
     * @return bool  処理結果 true=成功 / false=エラー有り
     */
    public function DataDelete(&$pdo, $receive_id, $data_file_path)
    {
        try {
            //テーブル名を取得
            $table_name = basename($data_file_path, "_dellog.txt");
            $table_name = substr($table_name, 0, -4);
            $field_list=null;

            //マッピング情報を取得
            $field_list=$this->getMapping($table_name);
            $cnv_table_name=$field_list['TableName'];

            //1レコードごとにデータを削除
            $table_data = json_decode(file_get_contents($data_file_path), true);
            $new_pk=array();
            foreach ($table_data as $record) {
                //列情報を取得
                $ret = $this->getRowData($record, $field_list);
                $new_pk=$ret['key'];

                //削除実施
                $where="";
                foreach ($new_pk as $key => $val) {
                    $where .= " AND $key = '$val'";
                }
                $where=substr($where, 5);
                $sql="DELETE FROM $cnv_table_name WHERE $where";
                $pdo->exec($sql);
                $error_info=$pdo->errorInfo();
                if ($error_info[0]!="00000" || $error_info[1]!=null || $error_info[2]!=null) {
                    //SQLを実行してエラーが発生した場合
                    $this->ErrorReceiveList($receive_id, "SQL実行エラー", $error_info[1]." ".$error_info[2] ." ".$sql, $data_file_path);
                    return false;
                }
            }
            return true;
        } catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * 指定のテーブルのマッピング情報を返す
     * @param string テーブル名
     * @return array マッピング情報
     */
    public function getMapping($table_name)
    {
        foreach ($this->ConversionMap as $key=>$map) {
            if (strtolower($map['TableName'])===strtolower($table_name)) {
                $field_list=$map;
                //テーブル名を保持する(名称の都合でTablenameの値を差し替える)
                $field_list['MVTableName']=$map['TableName'];
                $field_list['TableName']=$key;
                break;
            }
        }
        if ($field_list==null) {
            throw new Exception("テーブルマッピング情報がありません。");
        }

        //モバイルDB->社内用マッピングを追加する
        foreach ($field_list['PrimaryKey'] as $val) {
            $mobile_field_name=$field_list['Field'][$val];
            $field_list['MVPrimaryKey'][]=$mobile_field_name;
        }
        foreach ($field_list['Field'] as $key=>$val) {
            $field_list['MVField'][$val]=$key;
        }
        return $field_list;
    }
    /**
     * 行データとマッピング情報を受取り、キーと値の配列を戻す
     * @param array 行データ
     * @param array マッピング情報
     * @return array キーと値の配列
     */
    public function getRowData($record, $field_list)
    {
        foreach ($record as $field_name => $field_value) {
            if (array_key_exists($field_name, $field_list['MVField'])) {
                $new_key=$field_list['MVField'][$field_name];
                if (in_array($field_name, $field_list['MVPrimaryKey'], true)) {
                    $new_pk[$new_key]=$field_value;
                }
                $new_data[$new_key]=$field_value;
            }
        }
        return array("key"=>$new_pk,"val"=>$new_data);
    }
    /**
     * 受信リストの最終更新日時を更新する。
     * @param object (参照)トランザクション
     * @param int    受信ID
     * @param string 最終更新日時。nullの場合は現在日付で更新。
     * @return void
     */
    public function updateLastUpdateDate(&$pdo, $receive_id, $last_update_date=null)
    {
        //対象テーブルを選択
        $table_name="";
        switch ($this->target_server) {
            case $this->target_server_enum["PWA"]:
            {
                $table_name="モバイル受信リスト";
                break;
            }
            case $this->target_server_enum["WebOrder"]:
            {
                $table_name="Web受注受信リスト";
                break;
            }
        }

        //最終更新日を更新する
        $q_last_update_date = $last_update_date==null ? Carbon::now()->format('Y/m/d H:i:s') : $last_update_date;

        $stmt = $pdo->query("SELECT COUNT(*) AS CNT FROM $table_name WHERE 受信ＩＤ=$receive_id AND (最終更新日時 IS NULL OR 最終更新日時<'$q_last_update_date')");
        $count = $stmt->fetch()["CNT"];
        if (0<$count) {
            $sql="UPDATE $table_name SET 最終更新日時='$q_last_update_date' WHERE 受信ＩＤ=$receive_id";
            $pdo->exec($sql);
        }
    }
    /**
     * 受信エラーテーブルに送信フラグを書き込む
     * @param string エラー理由
     * @param string エラーメッセージ(エラー発生時に取得したメッセージをそのまま保存する)
     * @return void
     */
    public function ErrorReceiveList($receive_id, $description, $message=null, $file_name=null)
    {
        try {
            //対象テーブルを選択
            $table_name="";
            switch ($this->target_server) {
                case $this->target_server_enum["PWA"]:
                {
                    $table_name="モバイル受信エラー";
                    break;
                }
                case $this->target_server_enum["WebOrder"]:
                {
                    $table_name="Web受注受信エラー";
                    break;
                }
            }

            //送信エラーに登録する
            $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
            $user = 'daiyas';
            $password = 'daiyas';
            $pdo = new PDO($dsn, $user, $password);

            //試行回数を取得
            $seq_no=null;
            $next_seq_Sql = "
                    SELECT
                    MAX(試行回数)+1 AS NEXT_SEQ
                    FROM $table_name
                    WHERE 受信ＩＤ = $receive_id
                ";
            $stmt = $pdo->query($next_seq_Sql);
            if (!!$stmt) {
                $seq_no = $stmt->fetch()["NEXT_SEQ"];
            }
            $seq_no = $seq_no==null ? 1 : $seq_no;

            //テーブルに書き込む値を取得
            $esc_file_name = $file_name==null ? 'null' : "'$file_name'";
            $esc_message = $message==null ? 'null' : "'" . str_replace("'", "''", $message) . "'";
            $mr_sql="INSERT INTO $table_name(
                    受信ＩＤ
                   ,試行回数
                   ,エラー発生日時
                   ,受信ファイル名
                   ,エラー理由
                   ,エラーメッセージ
                   )VALUES(
                     $receive_id
                    ,$seq_no
                    ,GETDATE()
                    ,$esc_file_name
                    ,'$description'
                    ,$esc_message
                   )
                ";
            $pdo->beginTransaction();
            $pdo->exec($mr_sql);
            $pdo->commit();
            $pdo = null;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * 連携対象更新後続処理
     * @param object (参照)トランザクション
     * @param array 更新結果リスト
     * @return bool  処理結果 true=成功 / false=エラー有り
     */
    public function ExecAfter(&$pdo, $result_list)
    {
        //連携後更新
        foreach ($result_list as $result_info) {
            $table_name = $result_info->table_name;
            $records = $result_info->records;

            echo "ExecAfter" . $table_name . "\n";

            switch ($table_name) {
                case 'モバイル_回収入力':
                    foreach ($records as $rec_info) {
                        $pk = $rec_info->key;
                        $data = $rec_info->data;
                        $kind = $rec_info->kind;

                        // echo print_r($pk, true) . "\n";
                        // echo print_r($data, true) . "\n";
                        // echo $kind . "\n";

                        $r = DB::connection('sqlsrv_batch')->table("入金データ")
                            ->where('入金日付', $pk['日付'])
                            ->where('部署ＣＤ', $pk['部署ＣＤ'])
                            ->where('得意先ＣＤ', $pk['得意先ＣＤ'])
                            ->where('入金区分', 1)
                            ->get();

                        $model = new 入金データ();
                        $model->fill($data);
                        $model['現金'] = $data['回収金額'];
                        $model['摘要'] = $data['月分'] . "月分入金";
                        $model['修正担当者ＣＤ'] = 9999;
                        $model['修正日'] = Carbon::now()->format('Y/m/d H:i:s');

                        $rec = collect($model)->all();

                        if (count($r) > 0) {
                            DB::connection('sqlsrv_batch')->table("入金データ")
                                ->where('入金日付', $pk['日付'])
                                ->where('部署ＣＤ', $pk['部署ＣＤ'])
                                ->where('得意先ＣＤ', $pk['得意先ＣＤ'])
                                ->where('入金区分', 1)
                                ->update($rec);
                        } else {
                            $no = DB::connection('sqlsrv_batch')->table("管理マスタ")
                                ->where('管理ＫＥＹ', 1)
                                ->max('入金伝票Ｎｏ') + 1;

                            DB::connection('sqlsrv_batch')->table("管理マスタ")
                                ->where('管理ＫＥＹ', 1)
                                ->update(['入金伝票Ｎｏ' => $no]);

                            $rec['入金日付'] = $data['日付'];
                            $rec['伝票Ｎｏ'] = $no;
                            $rec['部署ＣＤ'] = $data['部署ＣＤ'];
                            $rec['得意先ＣＤ'] = $data['得意先ＣＤ'];
                            $rec['入金区分'] = 1;
                            $rec['小切手'] = 0;
                            $rec['バークレー'] = 0;
                            $rec['振込'] = 0;
                            $rec['その他'] = 0;
                            $rec['相殺'] = 0;
                            $rec['値引'] = 0;
                            $rec['備考'] = "";
                            $rec['請求日付'] = "";
                            $rec['予備金額１'] = 0;
                            $rec['予備金額２'] = 0;
                            $rec['予備ＣＤ１'] = 0;
                            $rec['予備ＣＤ２'] = 0;

                            DB::connection('sqlsrv_batch')->table("入金データ")->insert($rec);
                        }
                    }
                    break;
                case 'モバイル_予測入力':
                    //予測更新
                    foreach ($records as $rec_info) {
                        $pk = $rec_info->key;
                        $data = $rec_info->data;
                        $kind = $rec_info->kind;

                        $MobileSales = モバイル販売入力::query()
                            ->where('部署ＣＤ', $data['部署ＣＤ'])
                            ->where('得意先ＣＤ', $data['得意先ＣＤ'])
                            ->where('日付', $data['日付'])
                            ->first();

                        $Customer = 得意先マスタ::query()
                            ->where('部署ＣＤ', $data['部署ＣＤ'])
                            ->where('得意先ＣＤ', $data['得意先ＣＤ'])
                            ->first();

                        $Product = 商品マスタ::query()
                            ->where('商品ＣＤ', $data['商品ＣＤ'])
                            ->first();

                        $CustomerPrice = DB::select(
                            "
                                SELECT
                                    *
                                FROM (
                                    SELECT
                                        *
                                        , RANK() OVER(PARTITION BY 得意先ＣＤ, 商品ＣＤ ORDER BY 適用開始日 DESC) AS RNK
                                    FROM
                                        得意先単価マスタ新
                                    WHERE
                                        得意先ＣＤ = ?
                                    AND 商品ＣＤ = ?
                                    AND 適用開始日 <= ?
                                ) TT
                                WHERE
                                    RNK = 1
                            ",
                            [$data['得意先ＣＤ'], $data['商品ＣＤ'], $data['日付']]
                        );

                        $model = new 注文データ();
                        $model->fill($data);

                        $model['配送日'] = $data['日付'];

                        $model['商品ＣＤ'] = $Product->商品ＣＤ;
                        $model['商品区分'] = $Product->商品区分;

                        $tanka = count($CustomerPrice) > 0 ? $CustomerPrice[0]->単価 : $Product->売価単価;
                        $model['予備金額１'] = $tanka;

                        $model['修正担当者ＣＤ'] = 9999;
                        $model['修正日'] = Carbon::now()->format('Y/m/d H:i:s');

                        $orderTime = !$MobileSales ? '00:00:00' : preg_replace("/\..+$/", '', preg_split("/ /", $MobileSales->修正日)[1]);

                        //見込入力用
                        $mikomi = clone $model;
                        if ($Customer->売掛現金区分 == 0) {
                            $mikomi["現金個数"] = $data["見込数"];
                            $mikomi["現金金額"] = ($tanka * $data["見込数"]);
                            $mikomi["掛売個数"] = 0;
                            $mikomi["掛売金額"] = 0;
                        } else {
                            $mikomi["現金個数"] = 0;
                            $mikomi["現金金額"] = 0;
                            $mikomi["掛売個数"] = $data["見込数"];
                            $mikomi["掛売金額"] = ($tanka * $data["見込数"]);
                        }

                        $Orders = DB::connection('sqlsrv_batch')->table("注文データ")
                            ->where('部署ＣＤ', $data['部署ＣＤ'])
                            ->where('得意先ＣＤ', $data['得意先ＣＤ'])
                            ->where('配送日', $data['日付'])
                            ->where('商品ＣＤ', $data['商品ＣＤ'])
                            ->where('注文区分', 0)
                            ->get();

                        if (count($Orders) > 0) {
                            DB::connection('sqlsrv_batch')->table("注文データ")
                                ->where('部署ＣＤ', $data['部署ＣＤ'])
                                ->where('得意先ＣＤ', $data['得意先ＣＤ'])
                                ->where('配送日', $data['日付'])
                                ->where('商品ＣＤ', $data['商品ＣＤ'])
                                ->where('注文区分', 0)
                                ->update(collect($mikomi)->all());
                            //echo "\t update 見込:" . $data['部署ＣＤ'] . "/" . $data['得意先ＣＤ'] . "/" . $data['商品ＣＤ'] . "\n";
                        } else {
                            $mikomi["注文区分"] = 0;
                            $mikomi["注文日付"] = $data['日付'];
                            $mikomi["注文時間"] = $orderTime;
                            $mikomi["部署ＣＤ"] = $data['部署ＣＤ'];
                            $mikomi["得意先ＣＤ"] = $data['得意先ＣＤ'];
                            $mikomi["入力区分"] = 0;

                            $mikomi["備考１"] = '';
                            $mikomi["備考２"] = '';
                            $mikomi["備考３"] = '';
                            $mikomi["備考４"] = '';
                            $mikomi["備考５"] = '';
                            $mikomi["予備金額１"] = $tanka;
                            $mikomi["予備金額２"] = 0;
                            $mikomi["予備ＣＤ１"] = 0;
                            $mikomi["予備ＣＤ２"] = 0;

                            $mikomi['特記_社内用'] = $Customer->備考１;
                            $mikomi['特記_配送用'] = $Customer->備考２;
                            $mikomi['特記_通知用'] = $Customer->備考３;

                            $no = DB::connection('sqlsrv_batch')->table("注文データ")
                                ->where('注文区分', 0)
                                ->where('注文日付', $data['日付'])
                                ->where('部署ＣＤ', $data['部署ＣＤ'])
                                ->where('得意先ＣＤ', $data['得意先ＣＤ'])
                                ->where('配送日', $data['日付'])
                                ->max('明細行Ｎｏ') + 1;

                            $mikomi["明細行Ｎｏ"] = $no;

                            DB::connection('sqlsrv_batch')->table("注文データ")->insert(collect($mikomi)->all());
                            //echo "\t insert 見込:" . $data['部署ＣＤ'] . "/" . $data['得意先ＣＤ'] . "/" . $data['商品ＣＤ'] . "\n";
                        }

                        //注文入力用
                        $chumon = clone $model;
                        if ($Customer->売掛現金区分 == 0) {
                            $chumon["現金個数"] = $data["注文数"];
                            $chumon["現金金額"] = ($tanka * $data["注文数"]);
                            $chumon["掛売個数"] = 0;
                            $chumon["掛売金額"] = 0;
                        } else {
                            $chumon["現金個数"] = 0;
                            $chumon["現金金額"] = 0;
                            $chumon["掛売個数"] = $data["注文数"];
                            $chumon["掛売金額"] = ($tanka * $data["注文数"]);
                        }

                        $Orders = DB::connection('sqlsrv_batch')->table("注文データ")
                            ->where('部署ＣＤ', $data['部署ＣＤ'])
                            ->where('得意先ＣＤ', $data['得意先ＣＤ'])
                            ->where('配送日', $data['日付'])
                            ->where('商品ＣＤ', $data['商品ＣＤ'])
                            ->where('注文区分', 1)
                            ->get();

                        if (count($Orders) > 0) {
                            DB::connection('sqlsrv_batch')->table("注文データ")
                                ->where('部署ＣＤ', $data['部署ＣＤ'])
                                ->where('得意先ＣＤ', $data['得意先ＣＤ'])
                                ->where('配送日', $data['日付'])
                                ->where('商品ＣＤ', $data['商品ＣＤ'])
                                ->where('注文区分', 1)
                                ->update(collect($chumon)->all());
                            //echo "\t update 注文:" . $data['部署ＣＤ'] . "/" . $data['得意先ＣＤ'] . "/" . $data['商品ＣＤ'] . "\n";
                        } else {
                            $chumon["注文区分"] = 1;
                            $chumon["注文日付"] = $data['日付'];
                            $chumon["注文時間"] = $orderTime;
                            $chumon["部署ＣＤ"] = $data['部署ＣＤ'];
                            $chumon["得意先ＣＤ"] = $data['得意先ＣＤ'];
                            $chumon["入力区分"] = 0;

                            $chumon["備考１"] = '';
                            $chumon["備考２"] = '';
                            $chumon["備考３"] = '';
                            $chumon["備考４"] = '';
                            $chumon["備考５"] = '';
                            $chumon["予備金額１"] = $tanka;
                            $chumon["予備金額２"] = 0;
                            $chumon["予備ＣＤ１"] = 0;
                            $chumon["予備ＣＤ２"] = 0;

                            $chumon['特記_社内用'] = $Customer->備考１;
                            $chumon['特記_配送用'] = $Customer->備考２;
                            $chumon['特記_通知用'] = $Customer->備考３;

                            $no = DB::connection('sqlsrv_batch')->table("注文データ")
                                ->where('注文区分', 0)
                                ->where('注文日付', $data['日付'])
                                ->where('部署ＣＤ', $data['部署ＣＤ'])
                                ->where('得意先ＣＤ', $data['得意先ＣＤ'])
                                ->where('配送日', $data['日付'])
                                ->max('明細行Ｎｏ') + 1;

                            $chumon["明細行Ｎｏ"] = $no;

                            DB::connection('sqlsrv_batch')->table("注文データ")->insert(collect($chumon)->all());
                            //echo "\t insert 注文:" . $data['部署ＣＤ'] . "/" . $data['得意先ＣＤ'] . "/" . $data['商品ＣＤ'] . "\n";
                        }
                    }

                    DB::connection('sqlsrv_batch')->table("モバイル_予測入力")
                        ->where('部署ＣＤ', $data['部署ＣＤ'])
                        ->where('得意先ＣＤ', $data['得意先ＣＤ'])
                        ->where('日付', $data['日付'])
                        ->where('商品ＣＤ', $data['商品ＣＤ'])
                        ->update(["更新フラグ" => 1]);

                    break;

                case 'モバイル_更新予定リスト':
                //     foreach ($records as $rec_info) {
                //         $pk = $rec_info->key;
                //         $data = $rec_info->data;
                //         $kind = $rec_info->kind;

                //         $data['日付'];
                //         $data['得意先ＣＤ'];
                //         $data['更新フラグ'];
                //         $data['更新日'];
                //         $data['処理区分'];
                //         $data['モバイルデータ更新_部署ＣＤ'];
                //         $data['モバイルデータ更新_コースＣＤ'];
                //         $data['WebService_メソッド名'];
                //         $data['WebService_部署ＣＤ'];
                //         $data['WebService_コースＣＤ'];
                //         $data['WebService_更新区分'];



        //モバイル更新予定リストの取得, 現行は引数にコースを取ることが出来る
        // string cmdText3 = !forceApply
        //     ? (
        //         iCourse != 0
        //         ?
        //             "
        //                 SET LOCK_TIMEOUT 3000;
        //                 SELECT [モバイル_更新予定リスト].*
        //                 FROM
        //                     モバイル_更新予定リスト" + "
        //                     LEFT OUTER JOIN コーステーブル
        //                         ON" + " [モバイル_更新予定リスト].得意先ＣＤ = [コーステーブル].得意先ＣＤ" + "
        //                     WHERE
        //                         日付 = '" + string.Format("{0:yyyy/MM/dd}", (object) DateTime.Now) + "'" + "
        //                         AND [コーステーブル].[コースＣＤ] = " + iCourse.ToString() + "
        //                         AND [コーステーブル].[部署ＣＤ] = " + this._BushoCD + "
        //                     ORDER BY [コーステーブル].ＳＥＱ"
        //             :
        //             "
        //                 SET LOCK_TIMEOUT 3000;
        //                 SELECT
        //                     モバイル_更新予定リスト.*
        //                 FROM
        //                     モバイル_更新予定リスト,
        //                     得意先マスタ " + "
        //                 WHERE
        //                     日付 = '" + string.Format("{0:yyyy/MM/dd}", (object) DateTime.Now) + "'" + "
        //                     AND 更新フラグ=0" + "
        //                     AND モバイル_更新予定リスト.得意先ＣＤ = 得意先マスタ.得意先ＣＤ" + "
        //                     AND 得意先マスタ.部署ＣＤ = " + this._BushoCD
        //         )
        //     :
        //         "
        //             SET LOCK_TIMEOUT 3000;
        //             SELECT *
        //             FROM コーステーブル" + "
        //             WHERE コースＣＤ = " + (object) iCourse + "
        //             AND 部署ＣＤ = " + this._BushoCD
        //         ";

        // SqlDataAdapter adapter3 = new SqlDataAdapter()
        // {
        //     SelectCommand = new SqlCommand(cmdText3, this.oConn.Connection)
        // };
        // adapter3.SelectCommand.Transaction = sqlTransaction;
        // adapter3.SelectCommand.CommandTimeout = 20;
        // SqlCommandBuilder sqlCommandBuilder3 = new SqlCommandBuilder(adapter3);
        // DataSet (コース毎)モバイル更新予定リスト = new DataSet();
        // adapter3.Fill((コース毎)モバイル更新予定リスト, "モバイル_更新予定リスト");

        // foreach (DataRow row1 in (InternalDataCollectionBase) (コース毎)モバイル更新予定リスト.Tables[0].Rows)
        // {
        //     //売上データ明細を日付/部署CD/得意先CDでlockして取得
        //     //   string cmdText1 = "
        //     //     SET LOCK_TIMEOUT 3000;
        //     //     SELECT *
        //     //     FROM 売上データ明細 " + "
        //     //     WHERE 日付 = '" + string.Format("{0:yyyy/MM/dd}", (object) DateTime.Now) + "'" + "
        //     //     AND 得意先ＣＤ=" + row1["得意先ＣＤ"].ToString() + "
        //     //     AND 部署ＣＤ=" + this._BushoCD;

        //     //   SqlDataAdapter adapter1 = new SqlDataAdapter()
        //     //   {
        //     //     SelectCommand = new SqlCommand(cmdText1, this.oConn.Connection)
        //     //   };
        //     //   adapter1.SelectCommand.Transaction = sqlTransaction;
        //     //   adapter1.SelectCommand.CommandTimeout = 20;
        //     //   SqlCommandBuilder sqlCommandBuilder1 = new SqlCommandBuilder(adapter1);
        //     //   DataSet 売上データ明細dataset = new DataSet();
        //     //   adapter1.Fill(売上データ明細dataset, "売上データ明細");

        //     //売上データ明細datasetクローンに食事区分 != 2(昼食以外) のレコードを入れている？
        //     //   DataSet dataSet2 = new DataSet();
        //     //   DataSet 売上データ明細datasetクローン = 売上データ明細dataset.Clone();
        //     //   foreach (DataRow row2 in (InternalDataCollectionBase) 売上データ明細dataset.Tables[0].Rows)
        //     //   {
        //     //     if (row2["食事区分"].ToString() != "2")
        //     //     {
        //     //       DataRow row3 = 売上データ明細datasetクローン.Tables[0].NewRow();
        //     //       row3.ItemArray = row2.ItemArray;
        //     //       売上データ明細datasetクローン.Tables[0].Rows.Add(row3);
        //     //     }
        //     //   }

        //     //検索した売上データ明細を削除
        //     // foreach (DataRow row2 in (InternalDataCollectionBase) 売上データ明細dataset.Tables[0].Rows)
        //     //     row2.Delete();

        //     //モバイル更新予定リストの得意先から、分配有無を取得
        //     // bool 分配有無フラグ = false;
        //     // string format =
        //     //     "
        //     //         SET LOCK_TIMEOUT 3000;
        //     //         SELECT
        //     //         'x'
        //     //         FROM
        //     //         得意先マスタ T1
        //     //         WHERE
        //     //         T1.受注得意先ＣＤ = {0}
        //     //         AND T1.受注得意先ＣＤ != T1.得意先ＣＤ";
        //     //     "
        //     // SqlDataAdapter adapter2 = new SqlDataAdapter()
        //     // {
        //     //     SelectCommand = new SqlCommand(string.Format(format, (object) row1["得意先ＣＤ"].ToString()), this.oConn.Connection)
        //     // };
        //     // adapter2.SelectCommand.Transaction = sqlTransaction;
        //     // adapter2.SelectCommand.CommandTimeout = 20;
        //     // SqlCommandBuilder sqlCommandBuilder2 = new SqlCommandBuilder(adapter2);
        //     // DataSet dataSet5 = new DataSet();
        //     // adapter2.Fill(dataSet5, "分配有無");

        //     // if (dataSet5.Tables.Count > 0 && dataSet5.Tables[0].Rows.Count > 0)
        //     //     分配有無フラグ = true;

        //     //更新予定リストの得意先から、モバイル販売入力を取得
        //     // string cmdText2 = "
        //     //     SET LOCK_TIMEOUT 3000;
        //     //     SELECT * FROM モバイル_販売入力" + "
        //     //     WHERE 日付 = '" + string.Format("{0:yyyy/MM/dd}", (object) DateTime.Now) + "'" + "
        //     //     AND 得意先ＣＤ=" + row1["得意先ＣＤ"].ToString() + "
        //     //     AND 実績入力 = 1" + " AND (実績数 != 0 OR 金額 != 0) " + "
        //     //     AND 部署ＣＤ=" + this._BushoCD + " ORDER BY コースＣＤ,得意先ＣＤ";

        //     // SqlDataAdapter adapter4 = new SqlDataAdapter()
        //     // {
        //     //     SelectCommand = new SqlCommand(cmdText2, this.oConn.Connection)
        //     // };
        //     // adapter4.SelectCommand.Transaction = sqlTransaction;
        //     // adapter4.SelectCommand.CommandTimeout = 20;
        //     // SqlCommandBuilder sqlCommandBuilder4 = new SqlCommandBuilder(adapter4);
        //     // DataSet モバイル販売入力dataset = new DataSet();
        //     // adapter4.Fill(モバイル販売入力dataset, "モバイル_販売入力");

        //     foreach (DataRow row2 in (InternalDataCollectionBase) モバイル販売入力dataset.Tables[0].Rows)
        //     {
        //         //モバイル販売入力の得意先CDとコースCDから、コーステーブルより最小のSEQを取得
        //         // int courseSeq = this.getCourseSEQ(row2["コースＣＤ"].ToString(), row2["得意先ＣＤ"].ToString(), sqlTransaction);

        //         //SEQとモバイル販売入力の値から、売上データ明細を取得
        //         // string filterExpression = "
        //         //     部署ＣＤ=" + row2["部署ＣＤ"].ToString() + "
        //         //     AND コースＣＤ=" + row2["コースＣＤ"].ToString() + "
        //         //     AND 得意先ＣＤ=" + row2["得意先ＣＤ"].ToString() + "
        //         //     AND 行Ｎｏ=" + courseSeq.ToString() + "
        //         //     AND 商品ＣＤ=" + row2["商品ＣＤ"].ToString() + "
        //         //     AND 主食ＣＤ=" + row2["主食ＣＤ"].ToString() + "
        //         //     AND 副食ＣＤ=" + row2["副食ＣＤ"].ToString() + "
        //         //     AND 明細行Ｎｏ=" + row2["行Ｎｏ"].ToString();

        //         // DataRow[] dataRowArray = 売上データ明細dataset.Tables[0].Select(filterExpression);


        //         if (dataRowArray.Length > 0)
        //         {

        //             //取得出来た場合(複数件取れる？)

        //             foreach (DataRow dataRow in dataRowArray)
        //             {
        //                 // dataRow.BeginEdit();

        //                 //商品ＣＤで検索しているのに、同じＣＤを入れる？
        //                 // dataRow["商品ＣＤ"] = row2["商品ＣＤ"];
        //                 // dataRow["商品区分"] = (object) this.getSyohinKbn(row2["商品ＣＤ"].ToString(), sqlTransaction);

        //                 //現金売掛を判別して設定
        //                 // if (row2["現金売掛区分"].ToString().Equals("0"))
        //                 // {
        //                 //     dataRow["現金個数"] = row2["実績数"];
        //                 //     dataRow["現金金額"] = row2["金額"];
        //                 //     dataRow["現金値引"] = row2["値引"];
        //                 //     dataRow["現金値引事由ＣＤ"] = (object) 0;
        //                 //     dataRow["掛売個数"] = (object) 0;
        //                 //     dataRow["掛売金額"] = (object) 0;
        //                 //     dataRow["掛売値引"] = (object) 0;
        //                 //     dataRow["掛売値引事由ＣＤ"] = (object) 0;
        //                 //     dataRow["売掛現金区分"] = row2["現金売掛区分"];
        //                 //     dataRow["分配元数量"] = (object) 0;
        //                 // }
        //                 // else
        //                 // {
        //                 //     dataRow["現金個数"] = (object) 0;
        //                 //     dataRow["現金金額"] = (object) 0;
        //                 //     dataRow["現金値引"] = (object) 0;
        //                 //     dataRow["現金値引事由ＣＤ"] = (object) 0;
        //                 //     dataRow["掛売個数"] = row2["実績数"];
        //                 //     dataRow["掛売金額"] = row2["金額"];
        //                 //     dataRow["掛売値引"] = row2["値引"];
        //                 //     dataRow["掛売値引事由ＣＤ"] = (object) 0;
        //                 //     dataRow["売掛現金区分"] = row2["現金売掛区分"];
        //                 //     dataRow["分配元数量"] = (object) 0;
        //                 // }

        //                 // dataRow["予備金額１"] = row2["単価"];
        //                 // dataRow["修正担当者ＣＤ"] = (object) 9999;
        //                 // dataRow["食事区分"] = (object) 2;
        //                 // dataRow["修正日"] = (object) DateTime.Now;
        //                 // dataRow.EndEdit();
        //             }
        //         }
        //         else
        //         {

        //             //取得出来ない場合、モバイル販売入力の値でinsert

        //             // DataRow row3 = 売上データ明細dataset.Tables[0].NewRow();
        //             // row3["日付"] = (object) string.Format("{0:yyyy/MM/dd}", (object) DateTime.Now);
        //             // row3["部署ＣＤ"] = row2["部署ＣＤ"];
        //             // row3["コースＣＤ"] = row2["コースＣＤ"];
        //             // row3["行Ｎｏ"] = (object) courseSeq;
        //             // row3["得意先ＣＤ"] = row2["得意先ＣＤ"];
        //             // row3["明細行Ｎｏ"] = row2["行Ｎｏ"];
        //             // row3["商品ＣＤ"] = row2["商品ＣＤ"];
        //             // row3["主食ＣＤ"] = row2["主食ＣＤ"];
        //             // row3["副食ＣＤ"] = row2["副食ＣＤ"];
        //             // row3["商品区分"] = (object) this.getSyohinKbn(row2["商品ＣＤ"].ToString(), sqlTransaction);

        //             // if (row2["現金売掛区分"].ToString().Equals("0"))
        //             // {
        //             //     row3["現金個数"] = row2["実績数"];
        //             //     row3["現金金額"] = row2["金額"];
        //             //     row3["現金値引"] = row2["値引"];
        //             //     row3["現金値引事由ＣＤ"] = (object) 0;
        //             //     row3["掛売個数"] = (object) 0;
        //             //     row3["掛売金額"] = (object) 0;
        //             //     row3["掛売値引"] = (object) 0;
        //             //     row3["掛売値引事由ＣＤ"] = (object) 0;
        //             //     row3["売掛現金区分"] = row2["現金売掛区分"];
        //             // }
        //             // else
        //             // {
        //             //     row3["現金個数"] = (object) 0;
        //             //     row3["現金金額"] = (object) 0;
        //             //     row3["現金値引"] = (object) 0;
        //             //     row3["現金値引事由ＣＤ"] = (object) 0;
        //             //     row3["掛売個数"] = row2["実績数"];
        //             //     row3["掛売金額"] = row2["金額"];
        //             //     row3["掛売値引"] = row2["値引"];
        //             //     row3["掛売値引事由ＣＤ"] = (object) 0;
        //             //     row3["売掛現金区分"] = row2["現金売掛区分"];
        //             // }
        //             // row3["請求日付"] = (object) "";
        //             // row3["予備金額１"] = row2["単価"];
        //             // row3["予備金額２"] = (object) 0;
        //             // row3["予備ＣＤ２"] = (object) 0;
        //             // row3["分配元数量"] = (object) 0;
        //             // row3["修正担当者ＣＤ"] = (object) 9999;
        //             // row3["食事区分"] = (object) 2;
        //             // row3["修正日"] = (object) DateTime.Now;
        //             // 売上データ明細dataset.Tables[0].Rows.Add(row3);
        //         }
        //     }

        //     //モバイル更新予定の得意先CDから、分配先の売上データ明細を取得
        //     // string str = string.Format("{0:yyyy/MM/dd}", (object) DateTime.Now);
        //     // int num1 = this.toInt((object) row1["得意先ＣＤ"].ToString());
        //     // string cmdText4 = "
        //     //     SET LOCK_TIMEOUT 3000;
        //     //     SELECT *
        //     //     FROM
        //     //     --売上データ明細 WITH(UPDLOCK)
        //     //     売上データ明細
        //     //     WHERE
        //     //     日付 = 'DateTime.Now'
        //     //     AND 得意先ＣＤ != row1["得意先ＣＤ"]
        //     //     AND 得意先ＣＤ IN (
        //     //         SELECT
        //     //         DMY.得意先ＣＤ
        //     //         FROM
        //     //         得意先マスタ DMY
        //     //         WHERE
        //     //         DMY.受注得意先ＣＤ = row1["得意先ＣＤ"]
        //     // "
        //     // SqlDataAdapter adapter5 = new SqlDataAdapter()
        //     // {
        //     //     SelectCommand = new SqlCommand(cmdText4, this.oConn.Connection)
        //     // };
        //     // adapter5.SelectCommand.Transaction = sqlTransaction;
        //     // adapter5.SelectCommand.CommandTimeout = 20;
        //     // SqlCommandBuilder sqlCommandBuilder5 = new SqlCommandBuilder(adapter5);
        //     // DataSet 分配先売上データ明細dataset = new DataSet();
        //     // adapter5.Fill(分配先売上データ明細dataset, "売上データ明細");

        //     //一旦、分配売上先の売上データ明細を削除
        //     // foreach (DataRow row2 in (InternalDataCollectionBase) 分配先売上データ明細dataset.Tables[0].Rows)
        //     //     row2.Delete();

        //     //モバイル更新予定の得意先CDから、モバイル販売分配を検索
        //     //   string cmdText5 = "
        //     //         SET LOCK_TIMEOUT 3000;
        //     //         SELECT
        //     //             部署ＣＤ
        //     //             ,コースＣＤ
        //     //             ,得意先ＣＤ
        //     //             ,日付
        //     //             ,分配先得意先ＣＤ
        //     //             ,行Ｎｏ
        //     //             ,商品ＣＤ
        //     //             ,単価
        //     //             ,見込数
        //     //             ,注文数
        //     //             ,実績数
        //     //             ,金額
        //     //             ,値引
        //     //             ,現金売掛区分
        //     //             ,主食ＣＤ
        //     //             ,副食ＣＤ
        //     //             ,修正日
        //     //         FROM
        //     //             モバイル_販売分配
        //     //         WHERE
        //     //             日付 = 'DateTime.now'
        //     //             AND 得意先ＣＤ=row1["得意先ＣＤ"]
        //     //             AND 金額 != 0"
        //     //   ";
        //     //   SqlDataAdapter adapter6 = new SqlDataAdapter()
        //     //   {
        //     //     SelectCommand = new SqlCommand(cmdText5, this.oConn.Connection)
        //     //   };
        //     //   adapter6.SelectCommand.Transaction = sqlTransaction;
        //     //   adapter6.SelectCommand.CommandTimeout = 20;
        //     //   SqlCommandBuilder sqlCommandBuilder6 = new SqlCommandBuilder(adapter6);
        //     //   DataSet モバイル販売分配dataset = new DataSet();
        //     //   adapter6.Fill(モバイル販売分配dataset, "モバイル_販売入力");　テーブル名が販売入力になっているが、販売分配


        //     foreach (DataRow row2 in (InternalDataCollectionBase) モバイル販売分配dataset.Tables[0].Rows)
        //     {
        //         //モバイル販売分配の値から、分配先の売上データ明細を検索
        //         // string filterExpression1 = "
        //         //     部署ＣＤ=" + row2["部署ＣＤ"].ToString() + "
        //         //     AND コースＣＤ=" + row2["コースＣＤ"].ToString() + "
        //         //     AND 得意先ＣＤ=" + row2["分配先得意先ＣＤ"].ToString() + "
        //         //     AND 商品ＣＤ=" + row2["商品ＣＤ"].ToString() + "
        //         //     AND 主食ＣＤ=" + row2["主食ＣＤ"].ToString() + "
        //         //     AND 副食ＣＤ=" + row2["副食ＣＤ"].ToString() + "
        //         //     AND 明細行Ｎｏ=" + row2["行Ｎｏ"].ToString();

        //         // DataRow[] dataRowArray1 = 分配先売上データ明細dataset.Tables[0].Select(filterExpression1);

        //         if (dataRowArray1.Length > 0)
        //         {
        //             //存在していればupdate、だけど上で一旦削除してなかったっけ？恐らく分配先が変わることを考慮して
        //             // foreach (DataRow dataRow in dataRowArray1)
        //             // {
        //             //     dataRow.BeginEdit();
        //             //     dataRow["商品ＣＤ"] = row2["商品ＣＤ"];
        //             //     dataRow["商品区分"] = (object) this.getSyohinKbn(row2["商品ＣＤ"].ToString(), sqlTransaction);
        //             //     if (row2["現金売掛区分"].ToString().Equals("0"))
        //             //     {
        //             //     dataRow["現金個数"] = row2["実績数"];
        //             //     dataRow["現金金額"] = row2["金額"];
        //             //     dataRow["現金値引"] = row2["値引"];
        //             //     dataRow["現金値引事由ＣＤ"] = (object) 0;
        //             //     dataRow["掛売個数"] = (object) 0;
        //             //     dataRow["掛売金額"] = (object) 0;
        //             //     dataRow["掛売値引"] = (object) 0;
        //             //     dataRow["掛売値引事由ＣＤ"] = (object) 0;
        //             //     dataRow["売掛現金区分"] = row2["現金売掛区分"];
        //             //     }
        //             //     else
        //             //     {
        //             //     dataRow["現金個数"] = (object) 0;
        //             //     dataRow["現金金額"] = (object) 0;
        //             //     dataRow["現金値引"] = (object) 0;
        //             //     dataRow["現金値引事由ＣＤ"] = (object) 0;
        //             //     dataRow["掛売個数"] = row2["実績数"];
        //             //     dataRow["掛売金額"] = row2["金額"];
        //             //     dataRow["掛売値引"] = row2["値引"];
        //             //     dataRow["掛売値引事由ＣＤ"] = (object) 0;
        //             //     dataRow["売掛現金区分"] = row2["現金売掛区分"];
        //             //     }
        //             //     dataRow["予備金額１"] = row2["単価"];
        //             //     dataRow["修正担当者ＣＤ"] = (object) 9999;
        //             //     dataRow["修正日"] = (object) DateTime.Now;
        //             //     dataRow.EndEdit();
        //             // }
        //         }
        //         else
        //         {
        //             //無ければinsert
        //             DataRow row3 = 分配先売上データ明細dataset.Tables[0].NewRow();
        //             row3["日付"] = (object) string.Format("{0:yyyy/MM/dd}", (object) DateTime.Now);
        //             row3["部署ＣＤ"] = row2["部署ＣＤ"];
        //             row3["コースＣＤ"] = row2["コースＣＤ"];
        //             row3["行Ｎｏ"] = row2["行Ｎｏ"];
        //             row3["得意先ＣＤ"] = (object) row2["分配先得意先ＣＤ"].ToString();
        //             row3["明細行Ｎｏ"] = row2["行Ｎｏ"];
        //             row3["商品ＣＤ"] = row2["商品ＣＤ"];
        //             row3["主食ＣＤ"] = row2["主食ＣＤ"];
        //             row3["副食ＣＤ"] = row2["副食ＣＤ"];
        //             row3["商品区分"] = (object) this.getSyohinKbn(row2["商品ＣＤ"].ToString(), sqlTransaction);
        //             if (row2["現金売掛区分"].ToString().Equals("0"))
        //             {
        //                 row3["現金個数"] = row2["実績数"];
        //                 row3["現金金額"] = row2["金額"];
        //                 row3["現金値引"] = row2["値引"];
        //                 row3["現金値引事由ＣＤ"] = (object) 0;
        //                 row3["掛売個数"] = (object) 0;
        //                 row3["掛売金額"] = (object) 0;
        //                 row3["掛売値引"] = (object) 0;
        //                 row3["掛売値引事由ＣＤ"] = (object) 0;
        //                 row3["売掛現金区分"] = row2["現金売掛区分"];
        //             }
        //             else
        //             {
        //                 row3["現金個数"] = (object) 0;
        //                 row3["現金金額"] = (object) 0;
        //                 row3["現金値引"] = (object) 0;
        //                 row3["現金値引事由ＣＤ"] = (object) 0;
        //                 row3["掛売個数"] = row2["実績数"];
        //                 row3["掛売金額"] = row2["金額"];
        //                 row3["掛売値引"] = row2["値引"];
        //                 row3["掛売値引事由ＣＤ"] = (object) 0;
        //                 row3["売掛現金区分"] = row2["現金売掛区分"];
        //             }
        //             row3["請求日付"] = (object) "";
        //             row3["予備金額１"] = row2["単価"];
        //             row3["予備金額２"] = (object) 0;
        //             row3["予備ＣＤ２"] = (object) 0;
        //             row3["分配元数量"] = (object) 0;
        //             row3["修正担当者ＣＤ"] = (object) 9999;
        //             row3["食事区分"] = (object) 2;
        //             row3["修正日"] = (object) DateTime.Now;
        //             分配先売上データ明細dataset.Tables[0].Rows.Add(row3);
        //         }

        //         //モバイル販売分配の得意先CDとコースCDから、コーステーブルより最小のSEQを取得
        //         int courseSeq = this.getCourseSEQ(row2["コースＣＤ"].ToString(), row2["得意先ＣＤ"].ToString(), sqlTransaction);

        //         //売上データ明細を検索、要は親得意先
        //         // string filterExpression2 = "
        //         //     部署ＣＤ=" + row2["部署ＣＤ"].ToString() + "
        //         //     AND コースＣＤ=" + row2["コースＣＤ"].ToString() + "
        //         //     AND 得意先ＣＤ=" + row2["得意先ＣＤ"].ToString() + "
        //         //     AND 行Ｎｏ=" + courseSeq.ToString() + "
        //         //     AND 商品ＣＤ=" + row2["商品ＣＤ"].ToString() + "
        //         //     AND 主食ＣＤ=" + row2["主食ＣＤ"].ToString() + "
        //         //     AND 副食ＣＤ=" + row2["副食ＣＤ"].ToString();
        //         // DataRow[] dataRowArray2 = 売上データ明細dataset.Tables[0].Select(filterExpression2);

        //         //分配処理　分配元数量 = 現金/掛売個数, 現金/掛売個数 = 0 のはずが実績数を足しているので、一度分配したものはおかしくなる
        //         // if (dataRowArray2.Length > 0)
        //         // {
        //         //     foreach (DataRow dataRow in dataRowArray2)
        //         //     {
        //         //         dataRow.BeginEdit();
        //         //         dataRow["商品ＣＤ"] = row2["商品ＣＤ"];
        //         //         dataRow["商品区分"] = (object) this.getSyohinKbn(row2["商品ＣＤ"].ToString(), sqlTransaction);
        //         //         if (row2["現金売掛区分"].ToString().Equals("0"))
        //         //             dataRow["現金個数"] = (object) (this.toDecimal(dataRow["現金個数"]) - this.toDecimal(row2["実績数"]));
        //         //         else
        //         //             dataRow["掛売個数"] = (object) (this.toDecimal(dataRow["掛売個数"]) - this.toDecimal(row2["実績数"]));
        //         //         dataRow["分配元数量"] = (object) (this.toDecimal(dataRow["分配元数量"]) + this.toDecimal(row2["実績数"]));
        //         //         dataRow["修正担当者ＣＤ"] = (object) 9999;
        //         //         dataRow["修正日"] = (object) DateTime.Now;
        //         //         dataRow.EndEdit();
        //         //     }
        //         // }
        //     }

        //     adapter5.Update(分配先売上データ明細dataset, "売上データ明細");

        //     //ログ
        //     // this.LOG(1, "得意先CD:" + row1["得意先ＣＤ"].ToString() + " dsUriの中身（昼食以外マージ前） ◇◇◇◇◇◇◇◇ Start ");
        //     // if (売上データ明細dataset.Tables[0].Rows.Count > 0)
        //     // {
        //     //     foreach (DataRow row2 in (InternalDataCollectionBase) 売上データ明細dataset.Tables[0].Rows)
        //     //     {
        //     //         try
        //     //         {
        //     //             this.LOG(2, "" + "日付:" + row2["日付"].ToString() + " " + "部署ＣＤ:" + row2["部署ＣＤ"].ToString() + " " + "コースＣＤ:" + row2["コースＣＤ"].ToString() + " " + "行Ｎｏ:" + row2["行Ｎｏ"].ToString() + " " + "得意先ＣＤ:" + row2["得意先ＣＤ"].ToString() + " " + "明細行Ｎｏ:" + row2["明細行Ｎｏ"].ToString() + " " + "受注Ｎｏ:" + row2["受注Ｎｏ"].ToString() + " " + "配送担当者ＣＤ:" + row2["配送担当者ＣＤ"].ToString() + " " + "商品ＣＤ:" + row2["商品ＣＤ"].ToString() + " " + "商品区分:" + row2["商品区分"].ToString() + " " + "現金個数:" + row2["現金個数"].ToString() + " " + "現金金額:" + row2["現金金額"].ToString() + " " + "現金値引:" + row2["現金値引"].ToString() + " " + "現金値引事由ＣＤ:" + row2["現金値引事由ＣＤ"].ToString() + " " + "掛売個数:" + row2["掛売個数"].ToString() + " " + "掛売金額:" + row2["掛売金額"].ToString() + " " + "掛売値引:" + row2["掛売値引"].ToString() + " " + "掛売値引事由ＣＤ:" + row2["掛売値引事由ＣＤ"].ToString() + " " + "請求日付:" + row2["請求日付"].ToString() + " " + "予備金額１:" + row2["予備金額１"].ToString() + " " + "予備金額２:" + row2["予備金額２"].ToString() + " " + "売掛現金区分:" + row2["売掛現金区分"].ToString() + " " + "予備ＣＤ２:" + row2["予備ＣＤ２"].ToString() + " " + "主食ＣＤ:" + row2["主食ＣＤ"].ToString() + " " + "副食ＣＤ:" + row2["副食ＣＤ"].ToString() + " " + "分配元数量:" + row2["分配元数量"].ToString() + " " + "食事区分:" + row2["食事区分"].ToString() + " " + "修正担当者ＣＤ:" + row2["修正担当者ＣＤ"].ToString() + " " + "修正日:" + row2["修正日"].ToString() + " ");
        //     //         }
        //     //         catch
        //     //         {
        //     //         }
        //     //     }
        //     // }
        //     // this.LOG(3, "得意先CD:" + row1["得意先ＣＤ"].ToString() + " dsUriの中身（昼食以外マージ前） ◇◇◇◇◇◇◇◇ End ");

        //     //明細行Noの振り直し
        //     // int num2 = this.toInt((object) 売上データ明細dataset.Tables[0].Compute(" MAX(明細行Ｎｏ) + 1 ", (string) null).ToString());
        //     // foreach (DataRow row2 in (InternalDataCollectionBase) 売上データ明細datasetクローン.Tables[0].Rows)
        //     // {
        //     //     row2["明細行Ｎｏ"] = (object) num2;
        //     //     ++num2;
        //     // }

        //     // if (売上データ明細datasetクローン.Tables[0].Rows.Count > 0)
        //     //     売上データ明細dataset.Merge(売上データ明細datasetクローン);

        //     //ログ
        //     // string strコースCD = "";
        //     // this.LOG(4, "得意先CD:" + row1["得意先ＣＤ"].ToString() + " dAdapter3.Update(dsUri, \"売上データ明細\");  ------------------ Start");
        //     // this.LOG(5, "得意先CD:" + row1["得意先ＣＤ"].ToString() + " dsUriの中身（昼食以外マージ後） ★★★★★★★★ Start ");
        //     // if (売上データ明細dataset.Tables[0].Rows.Count > 0)
        //     // {
        //     //     foreach (DataRow row2 in (InternalDataCollectionBase) 売上データ明細dataset.Tables[0].Rows)
        //     //     {
        //     //         try
        //     //         {
        //     //             strコースCD = row2["コースＣＤ"].ToString();
        //     //             this.LOG(6, "" + "日付:" + row2["日付"].ToString() + " " + "部署ＣＤ:" + row2["部署ＣＤ"].ToString() + " " + "コースＣＤ:" + row2["コースＣＤ"].ToString() + " " + "行Ｎｏ:" + row2["行Ｎｏ"].ToString() + " " + "得意先ＣＤ:" + row2["得意先ＣＤ"].ToString() + " " + "明細行Ｎｏ:" + row2["明細行Ｎｏ"].ToString() + " " + "受注Ｎｏ:" + row2["受注Ｎｏ"].ToString() + " " + "配送担当者ＣＤ:" + row2["配送担当者ＣＤ"].ToString() + " " + "商品ＣＤ:" + row2["商品ＣＤ"].ToString() + " " + "商品区分:" + row2["商品区分"].ToString() + " " + "現金個数:" + row2["現金個数"].ToString() + " " + "現金金額:" + row2["現金金額"].ToString() + " " + "現金値引:" + row2["現金値引"].ToString() + " " + "現金値引事由ＣＤ:" + row2["現金値引事由ＣＤ"].ToString() + " " + "掛売個数:" + row2["掛売個数"].ToString() + " " + "掛売金額:" + row2["掛売金額"].ToString() + " " + "掛売値引:" + row2["掛売値引"].ToString() + " " + "掛売値引事由ＣＤ:" + row2["掛売値引事由ＣＤ"].ToString() + " " + "請求日付:" + row2["請求日付"].ToString() + " " + "予備金額１:" + row2["予備金額１"].ToString() + " " + "予備金額２:" + row2["予備金額２"].ToString() + " " + "売掛現金区分:" + row2["売掛現金区分"].ToString() + " " + "予備ＣＤ２:" + row2["予備ＣＤ２"].ToString() + " " + "主食ＣＤ:" + row2["主食ＣＤ"].ToString() + " " + "副食ＣＤ:" + row2["副食ＣＤ"].ToString() + " " + "分配元数量:" + row2["分配元数量"].ToString() + " " + "食事区分:" + row2["食事区分"].ToString() + " " + "修正担当者ＣＤ:" + row2["修正担当者ＣＤ"].ToString() + " " + "修正日:" + row2["修正日"].ToString() + " ");
        //     //         }
        //     //         catch
        //     //         {
        //     //         }
        //     //     }
        //     // }
        //     // this.LOG(5, "得意先CD:" + row1["得意先ＣＤ"].ToString() + " dsUriの中身（昼食以外マージ後） ★★★★★★★★ End ");

        //     //売上データ明細の更新反映
        //     adapter1.Update(売上データ明細dataset, "売上データ明細");

        //     //ログ
        //     this.LOG(8, "得意先CD:" + row1["得意先ＣＤ"].ToString() + " dAdapter3.Update(dsUri, \"売上データ明細\");  ------------------ End");
        //     if (!forceApply)
        //     {
        //         this.LOG(4, "得意先CD:" + row1["得意先ＣＤ"].ToString() + " dAdapter.Fill(dsYotei, \"モバイル_更新予定リスト\");  ------------------ Start");


        //         // this.UpdateYoteiList(this.oConn.Connection, sqlTransaction, row1["得意先ＣＤ"].ToString(), strコースCD);
        //         //処理済のモバイル更新予定リストの内容を更新
        //         {
        //             string cmdText = "
        //                 SET LOCK_TIMEOUT 3000;
        //                 SELECT *
        //                 FROM モバイル_更新予定リスト" + "
        //                 WHERE 得意先ＣＤ=" + sTokCD + "
        //                 AND 日付 = '" + string.Format("{0:yyyy/MM/dd}", (object) DateTime.Now) + "'";

        //             SqlDataAdapter adapter = new SqlDataAdapter()
        //             {
        //                 SelectCommand = new SqlCommand(cmdText, sqlConn)
        //             };
        //             adapter.SelectCommand.Transaction = tx;
        //             adapter.SelectCommand.CommandTimeout = 20;
        //             SqlCommandBuilder sqlCommandBuilder = new SqlCommandBuilder(adapter);
        //             DataSet dataSet = new DataSet();
        //             adapter.Fill(dataSet, "モバイル_更新予定リスト");

        //             this.LOG(5, "dsYoteiの中身（変更前） ●●●●●●●● ここから ");
        //             if (dataSet.Tables[0].Rows.Count > 0)
        //             {
        //             foreach (DataRow row in (InternalDataCollectionBase) dataSet.Tables[0].Rows)
        //                 this.LOG(6, "" + "更新フラグ:" + row["更新フラグ"].ToString() + " " + "更新日:" + row["更新日"].ToString() + " " + "sTokCD:" + sTokCD + " " + "処理区分:" + row["更新日"].ToString() + " " + "モバイルデータ更新_部署ＣＤ:" + row["モバイルデータ更新_部署ＣＤ"].ToString() + " " + "モバイルデータ更新_コースＣＤ:" + row["モバイルデータ更新_コースＣＤ"].ToString() + " " + "WebService_メソッド名:" + row["WebService_メソッド名"].ToString() + " " + "WebService_部署ＣＤ:" + row["WebService_部署ＣＤ"].ToString() + " " + "WebService_コースＣＤ:" + row["WebService_コースＣＤ"].ToString() + " " + "WebService_更新区分:" + row["WebService_更新区分"].ToString() + " ");
        //             }
        //             this.LOG(5, "dsYoteiの中身（変更前） ●●●●●●●● ここまで ");
        //             if (dataSet.Tables[0].Rows.Count > 0)
        //             {
        //                 dataSet.Tables[0].Rows[0].BeginEdit();
        //                 dataSet.Tables[0].Rows[0]["更新フラグ"] = (object) true;
        //                 dataSet.Tables[0].Rows[0]["更新日"] = (object) DateTime.Now;
        //                 dataSet.Tables[0].Rows[0]["処理区分"] = (object) "モバイルデータ更新";
        //                 dataSet.Tables[0].Rows[0]["モバイルデータ更新_部署ＣＤ"] = (object) this._BushoCD;
        //                 dataSet.Tables[0].Rows[0]["モバイルデータ更新_コースＣＤ"] = strコースCD == "" ? (object) "0" : (object) strコースCD;
        //                 dataSet.Tables[0].Rows[0]["WebService_メソッド名"] = (object) "";
        //                 dataSet.Tables[0].Rows[0]["WebService_部署ＣＤ"] = (object) "0";
        //                 dataSet.Tables[0].Rows[0]["WebService_コースＣＤ"] = (object) "0";
        //                 dataSet.Tables[0].Rows[0]["WebService_更新区分"] = (object) "";
        //                 dataSet.Tables[0].Rows[0].EndEdit();
        //             }
        //             adapter.Update(dataSet, "モバイル_更新予定リスト");

        //             this.LOG(5, "dsYoteiの中身（更新後） ◎◎◎◎◎◎◎◎ ここから ");
        //             if (dataSet.Tables[0].Rows.Count > 0)
        //             {
        //             foreach (DataRow row in (InternalDataCollectionBase) dataSet.Tables[0].Rows)
        //                 this.LOG(6, "" + "更新フラグ:" + row["更新フラグ"].ToString() + " " + "更新日:" + row["更新日"].ToString() + " " + "sTokCD:" + sTokCD + " " + "処理区分:" + row["更新日"].ToString() + " " + "モバイルデータ更新_部署ＣＤ:" + row["モバイルデータ更新_部署ＣＤ"].ToString() + " " + "モバイルデータ更新_コースＣＤ:" + row["モバイルデータ更新_コースＣＤ"].ToString() + " " + "WebService_メソッド名:" + row["WebService_メソッド名"].ToString() + " " + "WebService_部署ＣＤ:" + row["WebService_部署ＣＤ"].ToString() + " " + "WebService_コースＣＤ:" + row["WebService_コースＣＤ"].ToString() + " " + "WebService_更新区分:" + row["WebService_更新区分"].ToString() + " ");
        //             }
        //             this.LOG(5, "dsYoteiの中身（更新後） ◎◎◎◎◎◎◎◎ ここまで ");
        //         }

        //         this.LOG(4, "得意先CD:" + row1["得意先ＣＤ"].ToString() + " dAdapter.Fill(dsYotei, \"モバイル_更新予定リスト\");  ------------------ End");
        //     }
        // }
        // } catch (Exception $exception) {
        //     throw $exception;
        // }

                    break;
            }
        }

        return true;
    }
}
