<?php

namespace App\Libs;
use Exception;
use PDO;
use DB;
use Illuminate\Support\Carbon;
class WebOrderDataSendWrapper extends WebOrderDataSend
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
    public function Update($table_name,$record_data,$Immediate = null,$busho_cd = null,$customer_cd=null,$course_cd=null, $notify_message=null)
    {
        try {
            $map = $this->GetMapping($table_name);
            $sql = $this->CreateUpdateSQL($table_name,$map,$record_data);
            parent::StoreSendList($sql,$Immediate,$busho_cd,$customer_cd,$course_cd, $notify_message);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * 指定のテーブルのInsertSQLをモバイル送信リストに登録する
     * @param テーブル名
     * @param レコードの値
     * @param すぐに実行するか。null以外ならすぐに実行
     * @param 部署CD
     * @param 得意先CD
     * @param コースCD
     * @param 通知メッセージ
     * @param insert処理前に実行するSQL
     * @return void
     */
    public function Insert($table_name,$table_data,$Immediate = null,$busho_cd = null,$customer_cd=null,$course_cd=null, $notify_message = null, $before_sql = null)
    {
        try {
            $new_data=array();
            //マッピング情報を読み込む
            $map = json_decode(file_get_contents(public_path()."/dbmapping/weborder.txt"),true);
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
            $bit_fields = $this->GetBitField($table_name,$map);
            $new_table_name=$map["TableName"];
            $fields="";
            $values="";
            foreach($new_data as $key=>$val)
            {
                $fields .= ", $key";
                //値がNULLなら文字列NULL、そうでない場合で、元の型がbitならシングルクォートなしの値、それ以外はシングルクォート付きの値で戻す
                $q_val = $val===NULL ? "null" : (in_array($key,$bit_fields,true) ? "$val":"'$val'");
                $values .= ", $q_val";
            }
            $fields=substr($fields,1);
            $values=substr($values,1);
            $sql="insert into $new_table_name ( $fields )values( $values )";

            if($before_sql !== null)
            {
                $sql=$before_sql.";".$sql;
            }

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
            //マッピング情報を読み込む
            $map = json_decode(file_get_contents(public_path(). "/dbmapping/weborder.txt"),true);
            if (!array_key_exists($table_name, $map)) {
                throw new Exception("テーブルマッピング情報がありません。");
            }
            $sql = $this->CreateDeleteSQL($map[$table_name],$table_data);

            //送信リストに登録
            parent::StoreSendList($sql,$Immediate,$busho_cd,$customer_cd,$course_cd, $notify_message);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * 指定したテーブルに複数行をinsertする。事前にdelete文の発行可能
     * レコードデータ配列とレコードデータ取得用SQLを両方指定したらレコードデータ配列が優先
     * @param テーブル名
     * @param レコードデータ配列
     * @param レコードデータ取得用SQL
     * @param すぐに実行するか。null以外ならすぐに実行
     * @param 部署CD
     * @param 得意先CD
     * @param コースCD
     * @param 事前のDeleteSQL
     * @param 通知メッセージ
     * @param 更新後に実行したいSQL
     */
    private function InsertMultiRow($table_name,$table_data=null,$table_sql=null,$Immediate = null,$busho_cd = null,$customer_cd=null,$course_cd=null,$before_delete_sql=null, $notify_message=null, $after_sql=null)
    {
        try {
            $map = $this->GetMapping($table_name);

            //送信用SQLを生成(事前に削除がある場合)
            $send_sql = $before_delete_sql!=null ? $before_delete_sql.";" : "";

            if($table_data==null)
            {
                //テーブルのデータを取得
                $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
                $user = 'daiyas';
                $password = 'daiyas';
                $pdo = new PDO($dsn, $user, $password);
                $stmt = $pdo->query($table_sql);
                $table_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $pdo = null;
            }

            //送信用SQLを生成
            foreach($table_data as $recode_data)
            {
                $send_sql .= $this->CreateInsertSQL($table_name,$map,$recode_data).";";
            }

            if($after_sql != null)
            {
                $send_sql .= $after_sql;
            }

            //送信リストに登録
            parent::StoreSendList($send_sql,$Immediate,$busho_cd,$customer_cd,$course_cd, $notify_message);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * マッピングと1レコード分のデータを渡してUpdateSQLを戻す
     * @param テーブル名
     * @param マッピングデータ(1テーブル分)
     * @param レコードデータ
     * @return UpdateSQL文字列
    */
    private function CreateUpdateSQL($table_name,$map,$record_data)
    {
        $new_pk=array();
        $new_data=array();
        foreach($record_data as $key=>$val)
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
        $bit_fields = $this->GetBitField($table_name,$map);
        $new_table_name=$map["TableName"];
        $values="";
        foreach($new_data as $key=>$val)
        {
            //値がNULLなら文字列NULL、そうでない場合で、元の型がbitならシングルクォートなしの値、それ以外はシングルクォート付きの値で戻す
            $q_val = $val===NULL ? "null" : (in_array($key,$bit_fields,true) ? "$val":"'$val'");
            $values .= ", $key = $q_val";
        }
        $values=substr($values,1);

        $where="";
        foreach($new_pk as $key=>$val)
        {
            $where .= " AND $key = '$val'";
        }
        $where=substr($where,5);
        return "update $new_table_name set $values where $where";
    }
    /**
     * マッピングと1レコード分のデータを渡してInsertSQLを戻す
     * @param テーブル名
     * @param マッピングデータ(1テーブル分)
     * @param レコードデータ
     * @return InsertSQL文字列
    */
    private function CreateInsertSQL($table_name,$map,$record_data)
    {
        foreach($record_data as $key=>$val)
        {
            if(array_key_exists($key,$map["Field"]))
            {
                $new_key=$map["Field"][$key];
                $new_data[$new_key]=$val;
            }
        }

        //SQLの作成
        $bit_fields=[];
        $bit_fields = $this->GetBitField($table_name,$map);
        $new_table_name=$map["TableName"];
        $fields="";
        $values="";
        foreach($new_data as $key=>$val)
        {
            $fields .= ", $key";
            //値がNULLなら文字列NULL、そうでない場合で、元の型がbitならシングルクォートなしの値、それ以外はシングルクォート付きの値で戻す
            $q_val = $val===NULL ? "null" : (in_array($key,$bit_fields,true) ? "$val":"'$val'");
            $values .= ", $q_val";
        }
        $fields=substr($fields,1);
        $values=substr($values,1);
        return "insert into $new_table_name ( $fields )values( $values )";
    }
    /**
     * マッピングと1レコード分のデータを渡してInsertSQLを戻す
     * @param マッピングデータ(1テーブル分)
     * @param レコードデータ
     * @return DeleteSQL文字列
    */
    private function CreateDeleteSQL($map,$record_data)
    {
        $new_pk=array();
        foreach($record_data as $key=>$val)
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

        return "delete from $new_table_name where $where";
    }
    /**
     * 指定のテーブルのマッピング情報を取得する
     * @param テーブル名
     * @return マッピング情報配列
    */
    private function GetMapping($table_name)
    {
        //マッピング情報を読み込む
        $map = json_decode(file_get_contents(public_path()."/dbmapping/weborder.txt"),true);
        if (!array_key_exists($table_name, $map)) {
            throw new Exception("テーブルマッピング情報がありません。");
        }
        return $map[$table_name];
    }
    /**
     * 指定のテーブルのbit型列の一覧を取得し、PWA側の列名の配列で戻す
     * @param テーブル名
     * @param マッピングデータ(1テーブル分)
     * @return 取得結果
    */
    private function GetBitField($table_name,$map)
    {
        $sql="SELECT name
                FROM   sys.columns
                WHERE  object_id = (SELECT object_id
                                    FROM   sys.tables
                                    WHERE  name = '$table_name'
                                    )
                and system_type_id=104 ";
        $field_list=DB::select($sql);
        $fields=[];
        foreach ($field_list as $field)
        {
            if (array_key_exists($field->name, $map['Field']))
            {
                $fields[]=$map['Field'][$field->name];
            }
        }
        return $fields;
    }
    /**
     * Web受注データとWeb受注情報データを更新する
     * Web受注データは、Web得意先ＣＤ、配送日単位でDelete/Insertする
     * Web受注情報データは、注文ID単位でDelete/Insertする
     * @param Web得意先ＣＤ
     * @param 配送日
     * @param 通知メッセージ
     */
    public function UpdateWebOrderData($web_customer_code, $delivery_date, $notify_message = null)
    {
        try {
            $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
            $user = 'daiyas';
            $password = 'daiyas';
            $pdo = new PDO($dsn, $user, $password);

            //Web受注IDの取得
            $get_web_order_id = "select Web受注ID from Web受注データ where Web得意先ＣＤ='$web_customer_code' and 配送日='$delivery_date'";
            $stmt = $pdo->query($get_web_order_id);
            $ret = $stmt->fetch(PDO::FETCH_ASSOC);
            $web_order_id = $ret['Web受注ID'];

            //注文IDの取得
            $get_order_id_array = "select 注文ID from Web受注データ利用者情報 where Web受注ID=$web_order_id";
            $stmt = $pdo->query($get_order_id_array);
            $ret = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $order_id_array = collect($ret)->pluck('注文ID')->all();

            //注文情報IDの取得
            $get_order_info_id_array = "select 注文情報ID from Web受注データ利用者別詳細 where Web受注ID=$web_order_id";
            $stmt = $pdo->query($get_order_info_id_array);
            $ret = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $order_info_id_array = collect($ret)->pluck('注文情報ID')->all();

            //送信用SQLを生成

            // delete WebOrderData & WebOrderInfoData
            $order_id_IN = implode(",", $order_id_array);
            $send_sql =
                "delete from WebOrderInfoData where order_id in ($order_id_IN);"
                .
                "delete from WebOrderData where order_id in ($order_id_IN);"
            ;

            //insert WebOrderData
            foreach ($order_id_array as $order_id) {
                $get_insert_sql = "
                    select
                        'insert into WebOrderData values('
                            + TRIM(STR(wdd.注文ID)) + ','
                            + TRIM(STR(wdu.利用者ID)) + ','
                            + '''' + wd.Web得意先ＣＤ + ''','
                            + '''' + FORMAT(wd.配送日, 'yyyy-MM-dd') + ''','
                            + TRIM(STR(SUM(IIF(wdd.掛売個数 > 0, wdd.掛売金額, wdd.現金金額)))) + ','
                            + ISNULL(TRIM(STR(wdu.備考ID)), 'NULL') + ','
                            + 'NOW(),'
                            + 'NOW(),'
                            + 'NULL'
                            + ');' AS SQL
                    from
                        Web受注データ wd
                        inner join Web受注データ利用者情報 wdu
                            on wdu.Web受注ID=wd.Web受注ID
                        inner join Web受注データ利用者別詳細 wdd
                            on wdd.Web受注ID=wd.Web受注ID
                            and wdd.注文ID=wdu.注文ID
                    where
                        wdu.注文ID=$order_id
                    group by
                        wd.Web受注ID
                        ,wd.Web得意先ＣＤ
                        ,wd.配送日
                        ,wdd.注文ID
                        ,wdu.利用者ID
                        ,wdu.備考ID
                ";

                $stmt = $pdo->query($get_insert_sql);
                $inserts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($inserts as $insert) {
                    $send_sql .= $insert['SQL'];
                }
            }

            //insert WebOrderInfoData
            foreach ($order_info_id_array as $order_info_id) {
                $get_insert_sql = "
                    select
                        'insert into WebOrderInfoData values('
                            + TRIM(STR(注文情報ID)) + ','
                            + TRIM(STR(注文ID)) + ','
                            + TRIM(STR(商品ＣＤ)) + ','
                            + TRIM(STR(IIF(掛売個数 > 0, 1, 0))) + ','
                            + TRIM(STR(IIF(掛売個数 > 0, 掛売個数, 現金個数))) + ','
                            + TRIM(STR(単価)) + ','
                            + ISNULL(TRIM(STR(届け先ID)), 'NULL') + ','
                            + 'NOW(),'
                            + 'NOW(),'
                            + 'NULL'
                            + ');' AS SQL
                    from
                        Web受注データ利用者別詳細
                    where
                        注文情報ID=$order_info_id
                ";

                $stmt = $pdo->query($get_insert_sql);
                $inserts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($inserts as $insert) {
                    $send_sql .= $insert['SQL'];
                }
            }

            //送信リストに登録
            parent::StoreSendList($send_sql, true, $web_customer_code, $delivery_date, null, $notify_message);

        } catch (Exception $exception) {
            throw $exception;
        } finally {
            $pdo = null;
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
}
