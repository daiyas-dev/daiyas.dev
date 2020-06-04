<?php

namespace App\Libs;
use Exception;
class DataSendWrapper extends DataSend
{
    public $ConversionMap='{
        "コースマスタ": {
            "TableName":"CourseMaster",
            "PrimaryKey": ["部署ＣＤ","コースＣＤ"],
            "Field": {
                "部署ＣＤ":"department_code",
                "コース区分":"course_division",
                "コースＣＤ":"course_code",
                "コース名":"course_name",
                "担当者ＣＤ":"responsible_code",
                "工場区分":"factory_classification",
                "修正担当者ＣＤ":"updated_responsible_code"
                }
            }
        }';

    /**
     * 指定のテーブルのUpdateSQLをモバイルサーバに送る
     * @param テーブル名
     * @param テーブルの値
     * @return void
     */
    public function Update($table_name,$table_data)
    {
        try {
            $new_pk=array();
            $new_data=array();
            $map = json_decode($this->ConversionMap, true);
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
                $values .= ", $key = '$val'";
            }
            $values=substr($values,1);

            $where="";
            foreach($new_pk as $key=>$val)
            {
                $where .= " AND $key = '$val'";
            }
            $where=substr($where,5);

            $sql="update $new_table_name set $values where $where";

            //送信
            parent::send($sql);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * 指定のテーブルのInsertSQLをモバイルサーバに送る
     * @param テーブル名
     * @param テーブルの値
     * @return void
     */
    public function Insert($table_name,$table_data)
    {
        try {
            $new_data=array();
            $map = json_decode($this->ConversionMap, true);
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
                $values .= ", '$val'";
            }
            $fields=substr($fields,1);
            $values=substr($values,1);
            $sql="insert into $new_table_name ( $fields )values( $values )";

            //送信
            parent::send($sql);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * 指定のテーブルのDeleteSQLをモバイルサーバに送る
     * @param テーブル名
     * @param テーブルの値
     * @return void
     */
    public function Delete($table_name,$table_data)
    {
        try {
            $new_pk=array();
            $map = json_decode($this->ConversionMap, true);
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

            //送信
            parent::send($sql);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
}
