<?php

namespace App\Libs;
use Exception;
use PDO;

//AVS(PWA)から社内DBへ取込
class PWADataReceive extends DataReceiveBase
{
    function __construct()
    {
        parent::__construct();
        $this->target_server=$this->target_server_enum["PWA"];
        $this->ReadMap();
    }
    public function Receive()
    {
        //TODO:テスト用URL(NEW社内)
        $url = "http://192.168.1.210/hellolaravel/public/api/mobiledatasend";
        //TODO:本番URL
        //$url="http://52.197.70.172/api/mobiledatasend";
        try {
            $sql ="
                    SELECT 受信ＩＤ,テーブル名,最終更新日時
                      FROM モバイル受信リスト
                     ORDER BY 受信ＩＤ
                  ";
            $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
            $user = 'daiyas';
            $password = 'daiyas';
            $pdo = new PDO($dsn, $user, $password);
            $stmt = $pdo->query($sql);
            $DataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;

            //1テーブルごとにリクエストを投げて、処理する
            foreach ($DataList as $DataItem)
            {
                if (!array_key_exists($DataItem['テーブル名'], $this->ConversionMap)) {
                    $this->ErrorReceiveList($DataItem['受信ＩＤ'],"エラー","テーブルマッピング情報がありません。",null);
                    continue;
                }
                $mv_table_name=$this->ConversionMap[$DataItem['テーブル名']]['TableName'];

                //モバイル・Web受注サーバからデータを取得する
                $response = $this->GetResponse_MobileTable($url,$DataItem['受信ＩＤ'],$mv_table_name,$DataItem['最終更新日時']);
                if($response=="") {
                    continue;
                }
                if(!isset($response["file_path"])) {
                    continue;
                }
                $zip_path = $response["file_path"];
                if ($zip_path=="") {
                    //ファイルが送信されなかったら、更新なしとみなし、現在日時を最終更新日時をとして更新する。
                    $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
                    $user = 'daiyas';
                    $password = 'daiyas';
                    $pdo = new PDO($dsn, $user, $password);
                    $this->updateLastUpdateDate($pdo,$DataItem['受信ＩＤ']);
                    $pdo = null;
                    continue;
                }
                //zipを解凍する
                $zip = new \ZipArchive();
                if ($zip->open($zip_path) === true) {
                    $zip_dir_path = $this->tmp_path."\\".basename($zip_path,".tmp");
                    mkdir($zip_dir_path);
                    $zip->extractTo($zip_dir_path);
                    $zip->close();
                }
                else{
                    //解凍できなかった場合
                    $this->ErrorReceiveList($DataItem['受信ＩＤ'],"エラー","ZIPファイルを展開できません。",basename($zip_path));
                    continue;
                }

                //解凍したファイルを読み込んでSQLを実行
                //1つのzipに含まれているファイルは1トランザクションで処理する
                $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
                $user = 'daiyas';
                $password = 'daiyas';
                $pdo = new PDO($dsn, $user, $password);
                $pdo->beginTransaction();
                foreach (glob($zip_dir_path.'\\*') as $datafile) {
                    if (is_file($datafile)) {
                        if(strpos($datafile,"_dellog") !== false)
                        {
                            //削除
                            $result = $this->DataDelete($pdo,$DataItem['受信ＩＤ'],$datafile);
                        }
                        else
                        {
                            //Insert or Update
                            $result = $this->DataImport($pdo,$DataItem['受信ＩＤ'], $datafile);
                        }
                        if($result===true)
                        {
                            $this->updateLastUpdateDate($pdo,$DataItem['受信ＩＤ'],$response["last_update_date"]);
                            $is_error=false;
                        }
                        else
                        {
                            $is_error=true;
                            $pdo->rollBack();
                            exit;//ループを終了
                        }
                    }
                }
                if (!$is_error) {
                    $pdo->commit();
                }
                $pdo = null;

                //使用したテンポラリファイルを消す
                if (!$is_error) {
                    unlink($zip_path);
                    foreach (glob($zip_dir_path.'\\*') as $sqlfile) {
                        if (is_file($sqlfile)) {
                            unlink($sqlfile);
                        }
                    }
                    rmdir($zip_dir_path);
                }
            }
        }
        catch (Exception $exception) {
            $err_receive_id = isset($DataItem['受信ＩＤ']) ? $DataItem['受信ＩＤ'] : 0;
            $this->ErrorReceiveList($err_receive_id,"エラー",$exception->getMessage(),null);
        }
    }
    /**
     * 指定のURLからzipファイルを取得する
     * @param  string   URL
     * @param  int      受信ID
     * @param  string   モバイルDBのテーブル名
     * @param  datetime 最終更新日時。nullの場合は全件取得
     * @return array    file_path=取得したzipファイルのフルパス,last_update_date=最終更新日時
     */
    private function GetResponse_MobileTable($url,$receive_id,$table_name,$last_update_date)
    {
        try {
            $post_data = array(
                 'TableName'=> $table_name
                ,'LastUpdateDate' => $last_update_date
            );
            return $this->GetResponse($url,$receive_id,$post_data);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
}
