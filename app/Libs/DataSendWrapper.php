<?php

namespace App\Libs;
use Exception;
use PDO;
class DataSendWrapper extends PWADataSend
{
    /**
     * 指定のテーブルのUpdateSQLをモバイル送信リストに登録する
     * @param テーブル名
     * @param テーブルの値
     * @param すぐに実行するか。null以外ならすぐに実行
     * @param 部署CD
     * @param 得意先CD
     * @param コースCD
     * @param 通知メッセージ
     * @return void
     */
    public function Update($table_name,$table_data,$Immediate = null,$busho_cd = null,$customer_cd=null,$course_cd=null, $notify_message=null)
    {
        try {
            $new_pk=array();
            $new_data=array();
            //マッピング情報を読み込む
            $map = json_decode(file_get_contents(public_path()."/dbmapping/pwa.txt"),true);
            if (!array_key_exists($table_name, $map)) {
                throw new Exception("テーブルマッピング情報がありません。");
            }
            $map = $map[$table_name];
            foreach($table_data as $key=>$val)
            {
                if(array_key_exists($key,$map["Field"]))
                {
                    $new_key=$map["Field"][$key];
                    if(in_array($key,$map["PrimaryKey"],true))
                    {
                        $new_pk[$new_key]=$val;
                    }
                    else{
                        $new_data[$new_key]=$val;
                    }
                }
            }

            //SQLの作成
            $new_table_name=$map["TableName"];
            $values="";
            foreach($new_data as $key=>$val)
            {
                $q_val = $val===NULL ? "null" : "'$val'";
                $values .= ", $key = $q_val";
            }
            $values=substr($values,1);

            $where="";
            foreach($new_pk as $key=>$val)
            {
                $where .= " AND $key = '$val'";
            }
            $where=substr($where,5);

            $sql="update $new_table_name set $values where $where";

            //送信リストに登録
            parent::StoreSendList($sql,$Immediate,$busho_cd,$customer_cd,$course_cd, $notify_message);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * 指定のテーブルのInsertSQLをモバイル送信リストに登録する
     * @param テーブル名
     * @param テーブルの値
     * @param すぐに実行するか。null以外ならすぐに実行
     * @param 部署CD
     * @param 得意先CD
     * @param コースCD
     * @param 通知メッセージ
     * @return void
     */
    public function Insert($table_name,$table_data,$Immediate = null,$busho_cd = null,$customer_cd=null,$course_cd=null, $notify_message = null)
    {
        try {
            $new_data=array();
            //マッピング情報を読み込む
            $map = json_decode(file_get_contents(public_path()."/dbmapping/pwa.txt"),true);
            if (!array_key_exists($table_name, $map)) {
                throw new Exception("テーブルマッピング情報がありません。");
            }
            $map = $map[$table_name];
            foreach($table_data as $key=>$val)
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
                $q_val = $val===NULL ? "null" : "'$val'";
                $values .= ", $q_val";
            }
            $fields=substr($fields,1);
            $values=substr($values,1);
            $sql="insert into $new_table_name ( $fields )values( $values )";

            //送信リストに登録
            parent::StoreSendList($sql,$Immediate,$busho_cd,$customer_cd,$course_cd, $notify_message);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * 指定のテーブルのDeleteSQLをモバイル送信リストに登録する
     * @param テーブル名
     * @param テーブルの値
     * @param すぐに実行するか。null以外ならすぐに実行
     * @param 部署CD
     * @param 得意先CD
     * @param コースCD
     * @param 通知メッセージ
     * @return void
     */
    public function Delete($table_name,$table_data,$Immediate = null,$busho_cd = null,$customer_cd=null,$course_cd=null, $notify_message = null)
    {
        try {
            $new_pk=array();
            //マッピング情報を読み込む
            $map = json_decode(file_get_contents(public_path()."/dbmapping/pwa.txt"),true);
            if (!array_key_exists($table_name, $map)) {
                throw new Exception("テーブルマッピング情報がありません。");
            }
            $map = $map[$table_name];
            foreach($table_data as $key=>$val)
            {
                if(array_key_exists($key,$map["Field"]))
                {
                    $new_key=$map["Field"][$key];
                    if(in_array($key,$map["PrimaryKey"],true))
                    {
                        $new_pk[$new_key]=$val;
                    }
                }
            }
            if(count($new_pk)===0)
            {
                throw new Exception("主キーが指定されていません。");
            }

            //SQLの作成
            $new_table_name=$map["TableName"];
            $where="";
            foreach($new_pk as $key=>$val)
            {
                $where .= " AND $key = '$val'";
            }
            $where=substr($where,5);

            $sql="delete from $new_table_name where $where";

            //送信リストに登録
            parent::StoreSendList($sql,$Immediate,$busho_cd,$customer_cd,$course_cd, $notify_message);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * コーステーブルを更新する
     * 部署ＣＤとコースＣＤ単位でDelete/Insertする
     * @param 部署CD
     * @param コースCD
     * @param 通知メッセージ
     */
    public function UpdateCourseTable($busho_cd,$course_cd, $notify_message = null)
    {
        try {
            $send_sql="delete from CourseData where department_code = $busho_cd and course_code = $course_cd;";
            $sql="
                    select
                        CK.部署ＣＤ AS department_code
                    ,CK.コースＣＤ AS course_code
                    ,CT.ＳＥＱ AS seq
                    ,case CK.一時フラグ when 'TRUE' then 0 else 1 end as basic_course_flag
                    ,CK.適用開始日 AS application_start_date
                    ,CK.適用終了日 AS application_end_date
                    ,CT.得意先ＣＤ AS customer_code
                    ,CT.修正担当者ＣＤ AS updated_responsible_code
                    ,GETDATE() AS updated_at
                from
                        コーステーブル管理 CK
                    ,コーステーブル CT
                where CK.部署ＣＤ=$busho_cd
                    and CK.コースＣＤ=$course_cd
                    and CK.部署ＣＤ=CT.部署ＣＤ
                    and CK.コースＣＤ=CT.コースＣＤ
                    and CK.管理ＣＤ=0
                union
                select
                        CK.部署ＣＤ
                    ,CK.コースＣＤ
                    ,CT.ＳＥＱ
                    ,case CK.一時フラグ when 'TRUE' then 1 else 0 end
                    ,CK.適用開始日
                    ,CK.適用終了日
                    ,CT.得意先ＣＤ
                    ,CT.修正担当者ＣＤ AS updated_responsible_code
                    ,GETDATE() AS updated_at
                from
                        コーステーブル管理 CK
                    ,コーステーブル一時 CT
                where CK.部署ＣＤ=$busho_cd
                    and CK.コースＣＤ=$course_cd
                    and CK.部署ＣＤ=CT.部署ＣＤ
                    and CK.コースＣＤ=CT.コースＣＤ
                    and CK.管理ＣＤ<>0
                order by
                        CK.部署ＣＤ
                    ,CK.コースＣＤ
                    ,case CK.一時フラグ when 'TRUE' then 0 else 1 end
                    ,CT.ＳＥＱ
                    ,CK.適用開始日
                    ,CT.修正担当者ＣＤ
                    ,GETDATE()
                ";
            $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
            $user = 'daiyas';
            $password = 'daiyas';
            $pdo = new PDO($dsn, $user, $password);
            $stmt = $pdo->query($sql);
            $CourseDataList = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;

            foreach($CourseDataList as $CourseData)
            {
                $fields="";
                $values="";
                foreach($CourseData as $key=>$val)
                {
                    $fields.=",$key";
                    if($key=="basic_course_flag")
                    {
                        $values.= ",".$val;
                    }
                    else
                    {
                        $values.= ",";
                        $values.= $val===null ? "null" : "'$val'";
                    }
                }
                $fields=substr($fields,1);
                $values=substr($values,1);
                $send_sql.="insert into CourseData($fields)values($values);";
            }
            $send_id=parent::StoreSendList($send_sql,false,$busho_cd,null,$course_cd, $notify_message);
            $this->SendAsync(array($send_id));
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * 指定したSQLをモバイル送信リストに登録する
     * @param SQL
     * @param すぐに実行するか。null以外ならすぐに実行
     * @param 部署CD
     * @param 得意先CD
     * @param コースCD
     * @param 通知メッセージ
     * @return void
     */
    public function Execute($sql,$Immediate = null,$busho_cd = null,$customer_cd=null,$course_cd=null, $notify_message = null)
    {
        try {
            parent::StoreSendList($sql,$Immediate,$busho_cd,$customer_cd,$course_cd, $notify_message);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * モバイルサーバへのデータ送信を別プロセスで実行する
     *
     * @param array 送信ID
     * @return void
     */
    public function SendAsync($SendID)
    {
        $artisan = base_path(). DIRECTORY_SEPARATOR. "artisan";
        $ParamSendID = join(" ", $SendID);
        $command = "php {$artisan} batch:MobileDataSend $ParamSendID";
        $fp = popen('start "" /min ' . $command, 'r');
        pclose($fp);
    }
}
