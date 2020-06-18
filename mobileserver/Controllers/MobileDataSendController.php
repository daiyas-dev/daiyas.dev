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

            $tables[] = $request->input('TableName');

            //テーブルデータを取得
            $files=array();
            foreach($tables as $table)
            {
                $ret = $this->getTableData($table,$last_update_date);
                $files[] = $ret['file_name'];
                $max_updated_date = $ret['max_updated_date'];
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
    //指定したテーブルの、更新日付が指定日付以降のレコードをjson形式でファイルに保存する
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
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
}
