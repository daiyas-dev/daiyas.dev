<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Exception;
use DB;

//社内システムから受け取った、前回更新日時以降に更新されたデータを、
//Web受注DBから取得し、jsonファイルにして、zipファイルに圧縮して戻す。
class WebOrderDataSendController extends Controller
{
    public $send_data_enum=array("WebUserMaster"=>1,"WebOrderData"=>2);//Web受注受信リストのIDに対応する処理の定義
    public $data_id=null;

    public function send(Request $request)
    {
        try {
            //作業用のフォルダを作成する
            $this->tmp_path=public_path()."/send";
            if (!file_exists($this->tmp_path)) {
                mkdir($this->tmp_path);
            }

            //パラメータを取得
            $data_id=$request->input('DataID');
            //$table = $request->input('TableName');
            $param_last_update_date=$request->input('LastUpdateDate');
            $last_update_date = "" ;
            if ($param_last_update_date==null || $param_last_update_date=="") {
            }
            else{
                $dt=new Carbon($param_last_update_date);
                $last_update_date=$dt->format('Y/m/d H:i:s');
            }

            switch ($data_id)
            {
                case $this->send_data_enum["WebUserMaster"]:
                {
                    //テーブルデータを取得
                    $files=array();
                    $ret=$this->getWebUserMasterData($last_update_date);
                    $files[] = $ret['file_name'];
                    $max_updated_date = $ret['max_updated_date'];
                    break;
                }
                case $this->send_data_enum["WebOrderData"]:
                {
                    //テーブルデータを取得
                    $files=array();
                    $ret=$this->getWebOrderData($last_update_date);
                    $files[] = $ret['file_name'];
                    $max_updated_date = $ret['max_updated_date'];

                    $ret=$this->getWebOrderUserData($last_update_date);
                    $files[] = $ret['file_name'];
                    $max_updated_date = $max_updated_date<$ret['max_updated_date'] ? $ret['max_updated_date'] : $max_updated_date;

                    $ret=$this->getWebOrderInfoData($last_update_date);
                    $files[] = $ret['file_name'];
                    $max_updated_date = $max_updated_date<$ret['max_updated_date'] ? $ret['max_updated_date'] : $max_updated_date;
                    break;
                }
            }

            //テーブルデータをzip圧縮
            $dt = new Carbon($max_updated_date);
            $zip_path = $this->tmp_path."/". $dt->format('YmdHis') .".zip";
            $this->Compress($zip_path,$files);
            return $this->getResult(1,null,$zip_path,$max_updated_date);

        }
        catch (Exception $exception)
        {
            return $this->getResult(0,$exception,null);
        }
    }

    /**
     * UserMasterテーブルの、更新日付が指定日付以降のレコードをjson形式でファイルに保存する
     * @param 更新日付
     * @return void
     */
    private function getWebUserMasterData($last_update_date)
    {
        $where_lud="";
        if($last_update_date!="")
        {
            $where_lud=" AND(updated_at > '$last_update_date' OR deleted_at > '$last_update_date')";
        }

        $sql="
            SELECT
                web_customer_code AS Web得意先ＣＤ,
                user_id AS 利用者ID,
                user_code AS 利用者CD,
                memo1 AS 備考1,
                IF(deleted_at IS NULL, 0, 1) AS 削除フラグ,
                IF(deleted_at IS NULL, updated_at, deleted_at) AS 最終更新日
            FROM
                WebUserMaster
            WHERE
                0=0
                $where_lud
            ";

        //最終更新日を取得
        $sql_last_update_date="select max(q.最終更新日) as max_updated_at from ($sql)q";
        $max_updated_date = DB::selectOne($sql_last_update_date);
        $max_updated_date = $max_updated_date->max_updated_at;

        //データ本体を取得
        $tables = DB::select($sql);

        //jsonをファイルに書き込む
        $file_name = $this->tmp_path . "/WebUserMaster.txt";
        if ($file_handle = fopen($file_name, "w"))
        {
            // 書き込み
            fwrite($file_handle, json_encode($tables));
            // ファイルを閉じる
            fclose($file_handle);
        }
        chmod($file_name, 0777);
        return array("file_name"=>$file_name,"max_updated_date"=>$max_updated_date);
    }
    /**
     * WebOrderDataテーブルの、更新日付が指定日付以降のレコードを得意先・配送日別に取得し、json形式でファイルに保存する
     * @param 更新日付
     * @return array 作成したファイル名、最終更新日
     */
    private function getWebOrderData($last_update_date)
    {
        $where_lud="";
        if($last_update_date!="")
        {
            $where_lud=" AND(WebOrderData.updated_at > '$last_update_date' OR WebOrderData.deleted_at > '$last_update_date')";
        }

        $sql="
            SELECT
                WebOrderData.web_customer_code AS Web得意先ＣＤ,
                WebOrderData.delivery_date AS 配送日,
                MAX(WebOrderData.updated_at) AS 注文日時,
                COUNT(WebOrderData.order_id) = SUM(IF(WebOrderData.deleted_at IS NULL, 0, 1)) AS 削除フラグ
            FROM WebOrderData
            WHERE
                WebOrderData.delivery_date >= CURDATE()
            AND WebOrderData.deleted_at IS NULL
            GROUP BY
                WebOrderData.web_customer_code,
                WebOrderData.delivery_date
            ";

        //最終更新日を取得
        $sql_last_update_date="select max(q.注文日時) as max_updated_at from ($sql)q";
        $max_updated_date = DB::selectOne($sql_last_update_date);
        $max_updated_date = $max_updated_date->max_updated_at;

        //データ本体を取得
        $tables = DB::select($sql);

        //jsonをファイルに書き込む
        $file_name = $this->tmp_path . "/WebOrderData.txt";
        if ($file_handle = fopen($file_name, "w"))
        {
            // 書き込み
            fwrite($file_handle, json_encode($tables));
            // ファイルを閉じる
            fclose($file_handle);
        }
        chmod($file_name, 0777);
        return array("file_name"=>$file_name,"max_updated_date"=>$max_updated_date);
    }
    /**
     * WebOrderDataテーブルの、更新日付が指定日付以降のレコードを利用者別に取得し、json形式でファイルに保存する
     * @param 更新日付
     * @return array 作成したファイル名、最終更新日
     */
    private function getWebOrderUserData($last_update_date)
    {
        $where_lud="";
        if ($last_update_date!="")
        {
            $where_lud=" AND(WebOrderData.updated_at > '$last_update_date' OR WebOrderData.deleted_at > '$last_update_date')";
        }

        $sql="
                SELECT
                    WebOrderData.web_customer_code AS Web得意先ＣＤ,
                    WebOrderData.delivery_date AS 配送日,
                    WebOrderData.order_id AS 注文ID,
                    WebOrderData.user_id AS 利用者ID,
                    WebUserMaster.user_code AS 利用者CD,
                    WebOrderData.remarks_id AS 備考ID,
                    WebOrderData.updated_at,
                    IF(WebOrderData.deleted_at IS NULL, 0, 1) AS 削除フラグ
                FROM WebOrderData
                INNER JOIN WebUserMaster ON WebUserMaster.user_id = WebOrderData.user_id
                WHERE
                    WebOrderData.delivery_date >= CURDATE()
                AND WebOrderData.deleted_at IS NULL
            ";

        //最終更新日を取得
        $sql_last_update_date="select max(q.updated_at) as max_updated_at from ($sql)q";
        $max_updated_date = DB::selectOne($sql_last_update_date);
        $max_updated_date = $max_updated_date->max_updated_at;

        //データ本体を取得
        $tables = DB::select($sql);

        //jsonをファイルに書き込む
        $file_name = $this->tmp_path . "/WebOrderUserData.txt";
        if ($file_handle = fopen($file_name, "w"))
        {
            // 書き込み
            fwrite($file_handle, json_encode($tables));
            // ファイルを閉じる
            fclose($file_handle);
        }
        chmod($file_name, 0777);
        return array("file_name"=>$file_name,"max_updated_date"=>$max_updated_date);
    }
    /**
     * WebOrderDataとWeborderInfoDataの両テーブルから、更新日付が指定日付以降のレコードを利用者別に取得し、json形式でファイルに保存する
     * @param 更新日付
     * @return array 作成したファイル名、最終更新日
     */
    private function getWebOrderInfoData($last_update_date)
    {
        $where_lud="";
        if ($last_update_date!="")
        {
            $where_lud=" AND(WebOrderData.updated_at > '$last_update_date' OR WebOrderData.deleted_at > '$last_update_date')";
        }

        $sql="
                SELECT
                    WebOrderData.web_customer_code AS Web得意先ＣＤ,
                    WebOrderData.delivery_date AS 配送日,
                    WebOrderData.order_id AS 注文ID,
                    WebOrderInfoData.order_info_id AS 注文情報ID,
                    WebOrderInfoData.updated_at AS 注文日時,
                    WebOrderInfoData.web_product_code AS 商品ＣＤ,
                    WebOrderInfoData.price AS 単価,
                    IF(WebOrderInfoData.cash_type != 1, WebOrderInfoData.quantity, 0) AS 現金個数,
                    IF(WebOrderInfoData.cash_type != 1, WebOrderInfoData.quantity * WebOrderInfoData.price, 0) AS 現金金額,
                    IF(WebOrderInfoData.cash_type = 1, WebOrderInfoData.quantity, 0) AS 掛売個数,
                    IF(WebOrderInfoData.cash_type = 1, WebOrderInfoData.quantity * WebOrderInfoData.price, 0) AS 掛売金額,
                    WebOrderInfoData.destination_id AS 届け先ID,
                    WebOrderInfoData.updated_at,
                    IF(WebOrderInfoData.deleted_at IS NULL, 0, 1) AS 削除フラグ
                FROM WebOrderData
                INNER JOIN WebOrderInfoData ON WebOrderInfoData.order_id = WebOrderData.order_id
                INNER JOIN WebUserMaster ON WebUserMaster.user_id = WebOrderData.user_id
                WHERE
                    WebOrderData.delivery_date >= CURDATE()
                AND WebOrderData.deleted_at IS NULL
            ";

        //最終更新日を取得
        $sql_last_update_date="select max(q.updated_at) as max_updated_at from ($sql)q";
        $max_updated_date = DB::selectOne($sql_last_update_date);
        $max_updated_date = $max_updated_date->max_updated_at;

        //データ本体を取得
        $tables = DB::select($sql);

        //jsonをファイルに書き込む
        $file_name = $this->tmp_path . "/WebOrderInfoData.txt";
        if ($file_handle = fopen($file_name, "w"))
        {
            // 書き込み
            fwrite($file_handle, json_encode($tables));
            // ファイルを閉じる
            fclose($file_handle);
        }
        chmod($file_name, 0777);
        return array("file_name"=>$file_name,"max_updated_date"=>$max_updated_date);
    }
    /**
     * 指定したテーブルの、更新日付が指定日付以降のレコードをjson形式でファイルに保存する
     * @param テーブル名
     * @param 更新日付
     * @return array 作成したファイル名、最終更新日
     */
    private function getTableData($table_name,$last_update_date)
    {
        $where_lud="";
        if($last_update_date!="")
        {
            $where_lud=" and updated_at>'$last_update_date'";
        }
        $sql="select max(updated_at) as max_updated_at from $table_name where 0=0 $where_lud";
        $max_updated_date = DB::selectOne($sql);
        $max_updated_date = $max_updated_date->max_updated_at;

        $sql="select * from $table_name where 0=0 $where_lud";
        $tables = DB::select($sql);

        //jsonをファイルに書き込む
        $file_name = $this->tmp_path . "/" . $table_name . ".txt";
        if ($file_handle = fopen($file_name, "w"))
        {
            // 書き込み
            fwrite($file_handle, json_encode($tables));
            // ファイルを閉じる
            fclose($file_handle);
        }
        chmod($file_name, 0777);
        return array("file_name"=>$file_name,"max_updated_date"=>$max_updated_date);
    }
    /**
     * 応答を返す
     * @param 処理結果 1=成功 / 0=失敗
     * @param 失敗時のメッセージ
     * @param 圧縮ファイルのフルパス
     * @param string 取得したテーブルの最終更新日時(Y/m/d H:i:s)
     * @return 処理結果(Json文字列)
     */
    private function getResult($result,$message='',$file_path='',$max_updated_date)
    {
        $file_data='';
        if($file_path!='')
        {
            $file_data = file_get_contents($file_path);
            //圧縮ファイルを消す
            unlink($file_path);
        }

        return response()->json([
            'result' => $result,
            'message'=> $message!='' ? base64_encode($message) : '',
            'FileData'=> $file_data != '' ? base64_encode($file_data) : '',
            'LastUpdateDate'=>$max_updated_date
        ]);
    }
    /**
     * ファイルをzip圧縮する
     * @param zipファイル名
     * @param 圧縮するファイルフルパスの配列
     * @return void
     */
    private function Compress($zip_file_path,$file)
    {
        try {
            $zip = new \ZipArchive();
            $zip->open($zip_file_path, \ZipArchive::CREATE);
            foreach ($file as $f) {
                $zip->addFile($f, basename($f));
            }
            $zip->close();
            chmod($zip_file_path, 0777);
            //圧縮元ファイルを消す
            foreach ($file as $f) {
                unlink($f);
            }
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Web注文データの注文ID払出
     */
    public function generateOrderId(Request $request)
    {
        try {
        	$ret = DB::select("SELECT AUTOINC FROM information_schema.INNODB_TABLESTATS where name = 'daiyas/WebOrderData';");
        	$id = $ret[0]->AUTOINC;

        	//set increment OrderId
        	$alter = 'ALTER TABLE WebOrderData AUTO_INCREMENT = ' . ($id + 1);
        	DB::statement($alter);

        	return ["orderId" => $id, "alter" => $alter];
        }
        catch (Exception $exception) {
	        return response()->json([
	            'exception' => $exception,
	        ]);
        }
    }

    /**
     * Web注文情報データの注文情報ID払出
     */
    public function generateOrderInfoId(Request $request)
    {
        try {
        	//get current OrderInfoId
        	$ret = DB::select("SELECT AUTOINC FROM information_schema.INNODB_TABLESTATS where name = 'daiyas/WebOrderInfoData';");
        	$id = $ret[0]->AUTOINC;

        	//set increment OrderId
        	$alter = 'ALTER TABLE WebOrderInfoData AUTO_INCREMENT = ' . ($id + 1);
        	DB::statement($alter);

        	return ["orderInfoId" => $id];
        }
        catch (Exception $exception) {
	        return response()->json([
	            'exception' => $exception,
	        ]);
        }
    }
}
