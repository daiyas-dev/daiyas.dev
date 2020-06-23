<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Exception;
use DB;

//社内システムから受け取った、前回更新日時以降に更新されたデータを、
//モバイル・Web受注DBからテーブルごとに取得し、jsonファイルにして、
//zipファイルに圧縮して戻す。
class MobileDataSendController extends Controller
{
    public function send(Request $request)
    {
        try {
            //作業用のフォルダを作成する
            $this->tmp_path=public_path()."/send";
            if (!file_exists($this->tmp_path)) {
                mkdir($this->tmp_path);
            }

            //パラメータを取得
            $param_last_update_date=$request->input('LastUpdateDate');
            $last_update_date = "" ;
            if ($param_last_update_date==null || $param_last_update_date=="") {
            }
            else{
                $dt=new Carbon($param_last_update_date);
                $last_update_date=$dt->format('Y/m/d H:i:s');
            }

            $table = $request->input('TableName');

            //テーブルデータを取得
            $files=array();
            $max_updated_date="";
            $upd_file = $this->getTableData($table,$last_update_date);
            if($upd_file!=null && $upd_file!="")
            {
                $files[] = $upd_file['file_name'];
                $max_updated_date = $upd_file['max_updated_date'];
            }
            $dellog_file = $this->getDeleteData($table,$last_update_date);
            if($dellog_file!=null && $dellog_file!="")
            {
                $files[] = $dellog_file['file_name'];
                if($max_updated_date==""||$max_updated_date<$dellog_file['max_updated_date'])
                {
                    //$max_updated_dateが入っていないか、dellogの更新日より小さかったら、置き換える
                    $max_updated_date = $dellog_file['max_updated_date'];
                }
            }

            $zip_path="";
            if (0<count($files))
            {
                //テーブルデータをzip圧縮
                $dt = new Carbon($max_updated_date);
                $zip_path = $this->tmp_path."/". $dt->format('YmdHis') .".zip";
                $this->Compress($zip_path, $files);
            }
            return $this->getResult(1,null,$zip_path,$max_updated_date);
        }
        catch (Exception $exception)
        {
            return $this->getResult(0,$exception,null);
        }
    }
    /**
     * 指定したテーブルの、更新日付が指定日付以降のレコードをjson形式でファイルに保存する
     * @param string テーブル名
     * @param string 取得したテーブルの最終更新日時(Y/m/d H:i:s)
     * @return array 保存したファイル名,最終更新日時
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
        $table_data = DB::select($sql);

        if($table_data==null||count($table_data)<=0)
        {
            return "";
        }

        //jsonをファイルに書き込む
        $file_name = $this->tmp_path . "/" . $table_name . ".txt";
        if ($file_handle = fopen($file_name, "w"))
        {
            // 書き込み
            fwrite($file_handle, json_encode($table_data));
            // ファイルを閉じる
            fclose($file_handle);
        }
        chmod($file_name, 0777);

        return array("file_name"=>$file_name,"max_updated_date"=>$max_updated_date);
    }
    /**
     * 削除されたレコード情報を取得して戻す
     * @param string テーブル名
     * @param string 取得したテーブルの最終更新日時(Y/m/d H:i:s)
     * @return array 保存したファイル名,最終更新日時
     */
    private function getDeleteData($table_name,$last_update_date)
    {
        //削除済データを取得
        $sql="show tables like '" . $table_name . "_dellog'";
        $delog_tables = DB::selectOne($sql);
        if($delog_tables==null)
        {
            return "";
        }

        //取得結果の末尾を取得
        foreach($delog_tables as $delog_table)
        {
            //このループ内に処理はありませんが除去しないで下さい。
        }

        //検索条件(テーブルの主キー)を生成
        $sql="select column_name
                from information_schema.columns c
                where c.table_schema = 'daiyas_app'
                    and c.table_name   = '$table_name'
                    and c.column_key   = 'PRI'
                order by ordinal_position
            ";
        $dellog_pks = DB::select($sql);
        $select_column="";
        $where_pk="";
        foreach($dellog_pks as $delog_pk)
        {
            $where_pk .= " and org.$delog_pk->COLUMN_NAME=del.$delog_pk->COLUMN_NAME";
            $select_column .=",".$delog_pk->COLUMN_NAME;
        }
        $select_column=substr($select_column,1);

        //検索条件(最終更新日)を生成
        $where_lud="";
        if($last_update_date!="")
        {
            $where_lud=" and updated_at>'$last_update_date'";
        }
        $sql="select max(updated_at) as max_updated_at from $delog_table where 0=0 $where_lud";
        $max_updated_date = DB::selectOne($sql);
        $max_updated_date = $max_updated_date->max_updated_at;

        //削除済レコード情報を取得
        $sql="select $select_column
                from $delog_table del
                where 0=0 $where_lud
                and not exists(
                    select 1 from $table_name org
                    where 0=0 $where_pk
                    )
            ";
        $deleted_data = DB::select($sql);

        if($deleted_data==null||count($deleted_data)<=0)
        {
            return "";
        }

        //jsonをファイルに書き込む
        $file_name = $this->tmp_path . "/" . $table_name . "_dellog.txt";
        if ($file_handle = fopen($file_name, "w"))
        {
            // 書き込み
            fwrite($file_handle, json_encode($deleted_data));
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
}
