<?php

namespace App\Libs;

use App\Models\入金データ;
use App\Models\注文データ;
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
                    //更新日付の確認
                    $updatedat_field_name=  $cnv_table_name=="モバイル_更新予定リスト" ? "更新日" : "修正日";
                    $stmt = $pdo->query("SELECT $updatedat_field_name AS UPDATED_AT FROM $cnv_table_name WHERE $where");
                    $now_last_update_date = new Carbon($stmt->fetch()["UPDATED_AT"]);
                    $new_last_update_date = new Carbon($new_data[$updatedat_field_name]);

                    if ($now_last_update_date->format('Y-m-d H:i:s')<$new_last_update_date->format('Y-m-d H:i:s')) {
                        //UPDATE
                        $values="";
                        foreach ($new_data as $key=>$val) {
                            $q_val = ($val===null) ? "null" : "'$val'";
                            $values .= ", $key = $q_val";
                        }
                        $values=substr($values, 1);
                        $sql="UPDATE $cnv_table_name SET $values WHERE $where";
                        $pdo->exec($sql);
                    }
                } else {
                    //INSERT
                    $fields="";
                    $values="";
                    foreach ($new_data as $key=>$val) {
                        $fields .= ", $key";
                        $q_val = ($val===null) ? "null" : "'$val'";
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

            $result_info = (object) [];
            $result_info->table_name = $cnv_table_name;
            $result_info->records = [];

            //1レコードごとにデータを削除
            $table_data = json_decode(file_get_contents($data_file_path), true);
            $new_pk=array();
            foreach ($table_data as $record) {

                $rec_info = (object) [];
                $rec_info->key = $new_pk;
                $rec_info->kind = 'delete';

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
                    $result_info->result = false;
                    return $result_info;
                }
            }
            $result_info->result = true;
            return $result_info;
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
     * 受信リストの最終更新日時を更新する。(DB::使用)
     * @param object (参照)トランザクション
     * @param int    受信ID
     * @param string 最終更新日時。nullの場合は現在日付で更新。
     * @return void
     */
    public function updateLastUpdateDateByDB($receive_id, $last_update_date=null)
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

        $count = DB::selectOne("SELECT COUNT(*) AS CNT FROM $table_name WHERE 受信ＩＤ=$receive_id AND (最終更新日時 IS NULL OR 最終更新日時<'$q_last_update_date')");
        if (0<$count->CNT) {
            $sql="UPDATE $table_name SET 最終更新日時='$q_last_update_date' WHERE 受信ＩＤ=$receive_id";
            DB::update($sql);
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
            $pdo_err = new PDO($dsn, $user, $password);

            //試行回数を取得
            $seq_no=null;
            $next_seq_Sql = "
                    SELECT
                    MAX(試行回数)+1 AS NEXT_SEQ
                    FROM $table_name
                    WHERE 受信ＩＤ = $receive_id
                ";
            $stmt = $pdo_err->query($next_seq_Sql);
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
            $pdo_err->beginTransaction();
            $pdo_err->exec($mr_sql);
            $pdo_err->commit();
            $pdo_err = null;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * 連携対象更新後続処理
     * @param object (参照)トランザクション
     * @param array 更新結果リスト
     * @return void
     */
    public function ExecAfter(&$pdo, $result_list)
    {
        //連携後更新
        foreach ($result_list as $result_info) {
            //echo print_r($result_info, true) . "\n";
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
                case 'モバイル_更新予定リスト':
                    foreach ($records as $rec_info) {
                        $pk = $rec_info->key;
                        $data = $rec_info->data;

                        $data['日付'];
                        $data['得意先ＣＤ'];
                        $data['更新フラグ'];
                        $data['更新日'];
                        $data['処理区分'];
                        //$data['モバイルデータ更新_部署ＣＤ'];
                        //$data['モバイルデータ更新_コースＣＤ'];
                        $data['WebService_メソッド名'];
                        $data['WebService_部署ＣＤ'];
                        $data['WebService_コースＣＤ'];
                        $data['WebService_更新区分'];

                        if($data['WebService_更新区分']!='売上')
                        {
                            continue;
                        }

                        //該当する売上データ明細を削除
                        DB::connection('sqlsrv_batch')->table("売上データ明細")
                        ->where('部署ＣＤ', $data['WebService_部署ＣＤ'])
                        //->where('コースＣＤ', $data['WebService_コースＣＤ'])
                        ->where('得意先ＣＤ', $data['得意先ＣＤ'])
                        ->where('日付', $data['日付'])
                        ->delete();

                        //モバイル販売入力を取得
                        $MobileSalesList = DB::connection('sqlsrv_batch')->table("モバイル_販売入力")
                            ->where('部署ＣＤ', $data['WebService_部署ＣＤ'])
                            ->where('得意先ＣＤ', $data['得意先ＣＤ'])
                            ->where('日付', $data['日付'])
                            ->get();

                        //売上データ明細の作成
                        $row_detail_seq = 0;//売上データ明細の明細行No
                        foreach ($MobileSalesList as $MobileSales) {
                            //コーステーブル最小SEQ(配送順)を取得する
                            $CSeq = 1;
                            $CourseMng = DB::connection('sqlsrv_batch')->table("コーステーブル管理")
                                ->where('部署ＣＤ', $MobileSales->部署ＣＤ)
                                ->where('コースＣＤ', $MobileSales->コースＣＤ)
                                ->where(function ($q) use($MobileSales) {
                                    $q->orWhere('適用開始日', '<=', $MobileSales->日付)
                                        ->orWhere('適用終了日', '>=', $MobileSales->日付);
                                })
                                ->first();

                            if (!!$CourseMng) {
                                $CourseTable = $CourseMng->一時フラグ == 0 ? "コーステーブル" : "コーステーブル一時";

                                $CSeq = DB::connection('sqlsrv_batch')->table($CourseTable)
                                    ->where('部署ＣＤ', $MobileSales->部署ＣＤ)
                                    ->where('コースＣＤ', $MobileSales->コースＣＤ)
                                    ->when(
                                        $CourseMng->一時フラグ == 1,
                                        function ($q) use ($CourseMng) {
                                            return $q->where('管理ＣＤ', $CourseMng->管理ＣＤ);
                                        }
                                    )
                                    ->min('ＳＥＱ');
                            }

                            $rec = [];
                            if($MobileSales->実績入力 == 0)
                            {
                                $rec["現金個数"] = 0;
                                $rec["現金金額"] = 0;
                                $rec["現金値引"] = 0;
                                $rec["掛売個数"] = 0;
                                $rec["掛売金額"] = 0;
                                $rec["掛売値引"] = 0;
                            }
                            else
                            {
                                $rec["現金個数"] = $MobileSales->現金売掛区分 == 0 ? $MobileSales->実績数 : 0;
                                $rec["現金金額"] = $MobileSales->現金売掛区分 == 0 ? $MobileSales->金額 : 0;
                                $rec["現金値引"] = $MobileSales->現金売掛区分 == 0 ? $MobileSales->値引 : 0;
                                $rec["掛売個数"] = $MobileSales->現金売掛区分 != 0 ? $MobileSales->実績数 : 0;
                                $rec["掛売金額"] = $MobileSales->現金売掛区分 != 0 ? $MobileSales->金額 : 0;
                                $rec["掛売値引"] = $MobileSales->現金売掛区分 != 0 ? $MobileSales->値引 : 0;
                            }
                            $rec["売掛現金区分"] = $MobileSales->現金売掛区分;
                            $rec["現金値引事由ＣＤ"] = 0;
                            $rec["掛売値引事由ＣＤ"] = 0;
                            $rec["分配元数量"] = 0;
                            $rec["予備金額１"] = $MobileSales->単価;
                            $rec["食事区分"] = 2;
                            $rec["分配元数量"] = 0;
                            $rec['修正担当者ＣＤ'] = 9999;
                            $rec['修正日'] = Carbon::now()->format('Y/m/d H:i:s');
                            $rec["日付"] = $MobileSales->日付;
                            $rec["部署ＣＤ"] = $MobileSales->部署ＣＤ;
                            $rec["コースＣＤ"] = $MobileSales->コースＣＤ;
                            $rec["行Ｎｏ"] = $CSeq;
                            $rec["得意先ＣＤ"] = $MobileSales->得意先ＣＤ;
                            $row_detail_seq++;
                            $rec["明細行Ｎｏ"] = $row_detail_seq;
                            $rec["商品ＣＤ"] = $MobileSales->商品ＣＤ;
                            $rec["主食ＣＤ"] = $MobileSales->主食ＣＤ;
                            $rec["副食ＣＤ"] = $MobileSales->副食ＣＤ;
                            $rec["売掛現金区分"] = $MobileSales->現金売掛区分;

                            $Product = DB::connection('sqlsrv_batch')->table("商品マスタ")
                                ->where('商品ＣＤ', $MobileSales->商品ＣＤ)
                                ->first();

                            $rec["商品区分"] = $Product->商品区分;
                            $rec["請求日付"] = '';
                            $rec["予備金額２"] = 0;
                            $rec["予備ＣＤ２"] = 0;
                            $rec["備考"] = $MobileSales->メッセージ;
                            $rec["食事区分"] = 2;
                            DB::connection('sqlsrv_batch')->table("売上データ明細")->insert($rec);

                            //分配得意先
                            $DistCustomerList = DB::connection('sqlsrv_batch')->table("得意先マスタ")
                                ->where('受注得意先ＣＤ', $MobileSales->得意先ＣＤ)
                                ->whereColumn('得意先ＣＤ', '!=', '受注得意先ＣＤ')
                                ->select('得意先ＣＤ')
                                ->get();

                            if (count($DistCustomerList) > 0) {
                                //モバイル販売分配
                                $MobileDistList = DB::connection('sqlsrv_batch')->table("モバイル_販売分配")
                                    ->where('部署ＣＤ', $MobileSales->部署ＣＤ)
                                    ->where('コースＣＤ', $MobileSales->コースＣＤ)
                                    ->where('得意先ＣＤ', $MobileSales->得意先ＣＤ)
                                    ->where('日付', $MobileSales->日付)
                                    ->get();

                                $sql="select m1.得意先ＣＤ
                                    from 得意先マスタ m1
                                        ,得意先マスタ m2
                                    where m1.得意先ＣＤ<>m1.受注得意先ＣＤ
                                    and m1.受注得意先ＣＤ=m2.得意先ＣＤ
                                    and m1.売掛現金区分<>m2.売掛現金区分
                                    ";
                                $DataList = DB::connection('sqlsrv_batch')->select($sql);
                                $urigen_customer_code=[];
                                foreach($DataList as $DataItem)
                                {
                                    $urigen_customer_code[]=$DataItem->得意先ＣＤ;
                                }

                                //分配先の売上データ明細を削除
                                $customer_code_list = [];
                                foreach ($DistCustomerList as $DistCustomer) {
                                    array_push($customer_code_list, $DistCustomer->得意先ＣＤ);
                                }

                                //分配先得意先の売上データ明細も削除
                                $date = new Carbon($MobileSales->日付);
                                $sql="select distinct 分配先得意先ＣＤ from モバイル_販売分配
                                       where 得意先ＣＤ=$MobileSales->得意先ＣＤ
                                         and 日付='{$date->format('Y/m/d')}'";
                                $divide_customer_list = DB::connection('sqlsrv_batch')->select($sql);
                                foreach ($divide_customer_list as $DistCustomer) {
                                    array_push($customer_code_list, $DistCustomer->分配先得意先ＣＤ);
                                }

                                DB::connection('sqlsrv_batch')->table("売上データ明細")
                                    ->where('部署ＣＤ', $MobileSales->部署ＣＤ)
                                    ->where('日付', $MobileSales->日付)
                                    ->whereIn('得意先ＣＤ', $customer_code_list)
                                    ->delete();

                                //分配先更新
                                foreach ($MobileDistList as $MobileDist) {
                                    if(in_array($MobileDist->分配先得意先ＣＤ,$urigen_customer_code,true) && $MobileDist->実績数==0){
                                        continue;
                                    }

                                    $dist = [];

                                    $dist["日付"] = $MobileDist->日付;
                                    $dist["部署ＣＤ"] = $MobileDist->部署ＣＤ;
                                    $dist["コースＣＤ"] = $MobileDist->コースＣＤ;
                                    $dist["行Ｎｏ"] = $MobileDist->行Ｎｏ;
                                    $dist["得意先ＣＤ"] = $MobileDist->分配先得意先ＣＤ;
                                    $dist["明細行Ｎｏ"] = $MobileDist->行Ｎｏ;
                                    $dist["商品ＣＤ"] = $MobileDist->商品ＣＤ;
                                    $dist["主食ＣＤ"] = $MobileDist->主食ＣＤ;
                                    $dist["副食ＣＤ"] = $MobileDist->副食ＣＤ;
                                    $Product = DB::connection('sqlsrv_batch')->table("商品マスタ")
                                        ->where('商品ＣＤ', $MobileDist->商品ＣＤ)
                                        ->first();

                                    $dist["商品区分"] = $Product->商品区分;

                                    $dist["現金個数"] = $MobileDist->現金売掛区分 == 0 ? $MobileDist->実績数 : 0;
                                    $dist["現金金額"] = $MobileDist->現金売掛区分 == 0 ? $MobileDist->金額 : 0;
                                    $dist["現金値引"] = $MobileDist->現金売掛区分 == 0 ? $MobileDist->値引 : 0;
                                    $dist["掛売個数"] = $MobileDist->現金売掛区分 != 0 ? $MobileDist->実績数 : 0;
                                    $dist["掛売金額"] = $MobileDist->現金売掛区分 != 0 ? $MobileDist->金額 : 0;
                                    $dist["掛売値引"] = $MobileDist->現金売掛区分 != 0 ? $MobileDist->値引 : 0;
                                    $dist["売掛現金区分"] = $MobileDist->現金売掛区分;
                                    $dist["現金値引事由ＣＤ"] = 0;
                                    $dist["掛売値引事由ＣＤ"] = 0;
                                    $dist["分配元数量"] = 0;
                                    $dist["予備金額１"] = $MobileDist->単価;

                                    $dist["請求日付"] = '';
                                    $dist["予備金額２"] = 0;
                                    $dist["予備ＣＤ２"] = 0;
                                    $dist["備考"] = '';
                                    $dist["食事区分"] = 2;

                                    $dist['修正担当者ＣＤ'] = 9999;
                                    $dist['修正日'] = Carbon::now()->format('Y/m/d H:i:s');

                                    DB::connection('sqlsrv_batch')->table("売上データ明細")->insert($dist);

                                    //分配元更新
                                    $Parent = DB::connection('sqlsrv_batch')->table("売上データ明細")
                                        ->where('部署ＣＤ', $MobileDist->部署ＣＤ)
                                        ->where('コースＣＤ', $MobileDist->コースＣＤ)
                                        ->where('得意先ＣＤ', $MobileDist->得意先ＣＤ)
                                        ->where('日付', $MobileDist->日付)
                                        ->where('商品ＣＤ', $MobileDist->商品ＣＤ)
                                        ->where('売掛現金区分', $MobileSales->現金売掛区分)
                                        ->where(function ($q) {
                                            $q->orWhere('掛売個数', '>', 0)
                                                ->orWhere('現金個数', '>', 0);
                                        })
                                        ->first();

                                    if (!!$Parent) {
                                        $pdata = [];
                                        if ($MobileDist->現金売掛区分 == 0) {
                                            $pdata['現金個数'] = $Parent->現金個数 - $MobileDist->実績数;
                                        } else {
                                            $pdata['掛売個数'] = $Parent->掛売個数 - $MobileDist->実績数;
                                        }
                                        $pdata['分配元数量'] = $Parent->分配元数量 + $MobileDist->実績数;
                                        $pdata['修正担当者ＣＤ'] = 9999;
                                        $pdata['修正日'] = Carbon::now()->format('Y/m/d H:i:s');

                                        DB::connection('sqlsrv_batch')->table("売上データ明細")
                                            ->where('部署ＣＤ', $MobileDist->部署ＣＤ)
                                            ->where('コースＣＤ', $MobileDist->コースＣＤ)
                                            ->where('得意先ＣＤ', $MobileDist->得意先ＣＤ)
                                            ->where('日付', $MobileDist->日付)
                                            ->where('商品ＣＤ', $MobileDist->商品ＣＤ)
                                            ->where('売掛現金区分', $MobileSales->現金売掛区分)
                                            ->update($pdata);
                                    }
                                }
                            }
                        }
                    }
                    break;
            }
        }
    }
}
