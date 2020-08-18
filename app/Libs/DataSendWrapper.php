<?php

namespace App\Libs;
use Exception;
use PDO;
use DB;
use Illuminate\Support\Carbon;
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
            $map = json_decode(file_get_contents(public_path()."/dbmapping/pwa.txt"),true);
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
        $map = json_decode(file_get_contents(public_path()."/dbmapping/pwa.txt"),true);
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
            $sql="select
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
                    ,case CK.一時フラグ when 'TRUE' then 0 else 1 end
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
            parent::StoreSendList($send_sql,true,$busho_cd,null,$course_cd, $notify_message);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * 得意先単価マスタ新テーブルを更新する
     * 得意先ＣＤ単位でDelete/Insertする
     * @param 得意先CD
     * @param 通知メッセージ
     */
    public function UpdateCustomerPricemasterNew($customer_cd, $notify_message = null)
    {
        try {
            //得意先の所属する部署を取得
            $sql="select 部署CD from 得意先マスタ where 得意先ＣＤ=$customer_cd;";
            $busho_cd = DB::selectOne($sql)->部署CD;

            $table_name="得意先単価マスタ新";
            $del_sql="delete from CustomerPriceMasterNew where customer_code = $customer_cd";
            $table_sql="select * from $table_name where 得意先ＣＤ=$customer_cd";
            $this->InsertMultiRow($table_name, null, $table_sql, false, $busho_cd, $customer_cd, null, $del_sql,$notify_message);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * 祝日マスタを連携する
     * @param array 祝日データ
     * @param 通知メッセージ
     */
    public function UpdateHolidayMaster($table_data,$Message)
    {
        $target_date="";
        foreach ($table_data as $rec) {
            $target_date.=",'{$rec['対象日付']}'";
        }
        $target_date=substr($target_date,1);
        $del_sql="delete from HolidayMaster where target_date in($target_date)";
        $this->InsertMultiRow('祝日マスタ', $table_data,null, true, null, null, null, $del_sql,$Message);
    }
    /**
     * 各種テーブルを更新する
     * 全件Delete/Insertする
     * @param 通知メッセージ
     */
    public function UpdateVariousData($notify_message = null)
    {
        try {
            $table_name="各種テーブル";
            $del_sql="delete from VariousData";
            $table_sql="select * from $table_name";
            $this->InsertMultiRow($table_name, null, $table_sql, true, null, null, null, $del_sql,$notify_message);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * モバイル_移動入力テーブルを更新する
     * 部署ＣＤ、コースＣＤ、日付単位でDelete/Insertする
     * @param 部署ＣＤ
     * @param コースＣＤ
     * @param 日付
     * @param 通知メッセージ
     */
    public function UpdateMovementInputData($busho_cd,$course_cd,$date, $notify_message = null)
    {
        try {
            $table_name="モバイル_移動入力";
            $del_sql="delete from MovementInputData
                        where department_code = $busho_cd
                          and (course_code=$course_cd or opponent_course_code=$course_cd)
                          and date = '$date'";
            $table_sql="select * from $table_name
                    where 部署ＣＤ = $busho_cd
                    and (コースＣＤ=$course_cd or 相手コースＣＤ=$course_cd)
                    and 日付 = '$date'";
            $this->InsertMultiRow($table_name, null, $table_sql, true, $busho_cd, null, $course_cd, $del_sql,$notify_message);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * モバイル_持ち出し入力テーブルを更新する
     * 部署ＣＤ、持ち出し日付単位でDelete/Insertする
     * @param 部署ＣＤ
     * @param 持ち出し日付
     * @param 通知メッセージ
     */
    public function UpdateTakeOutInputData($busho_cd, $date, $notify_message = null)
    {
        try {
            $table_name="モバイル_持ち出し入力";
            $del_sql="delete from TakeOutInputData
                    where department_code = $busho_cd
                      and take_out_at = '$date'
                ";
            $table_sql="select * from $table_name
                where 部署ＣＤ = $busho_cd
                and 持ち出し日付 = '$date'
            ";
            $this->InsertMultiRow($table_name, null, $table_sql, true, $busho_cd, null, null, $del_sql,$notify_message);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * 日別得意先製造パターンとモバイル_予測入力テーブルを更新する
     * モバイル_予測入力は、部署ＣＤ、日付単位でDelete/Insertする
     * 日別得意先製造パターンは、部署ＣＤ、コースＣＤ、日付単位でDelete/Insertする
     * @param 部署ＣＤ
     * @param コースＣＤ
     * @param 日付
     * @param 通知メッセージ
     */
    public function UpdateExpectedInputData($busho_cd,$course_cd,$date, $notify_message = null)
    {
        try {
            //モバイル_予測入力データの更新
            $table_name="モバイル_予測入力";

            //テーブルのデータを取得
            $table_sql="select * from $table_name
                    where 部署ＣＤ = $busho_cd
                    and 日付 = '$date'";

            $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
            $user = 'daiyas';
            $password = 'daiyas';
            $pdo = new PDO($dsn, $user, $password);
            $stmt = $pdo->query($table_sql);
            $table_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;

            //送信用SQLを生成
            $map = $this->GetMapping($table_name);
            $send_sql = "delete from {$map['TableName']} where department_code = $busho_cd and date = '$date';";
            foreach($table_data as $recode_data)
            {
                $send_sql .= $this->CreateInsertSQL($table_name,$map,$recode_data).";";
            }

            //日別得意先製造パターンデータの更新
            $table_name="日別得意先製造パターン";

            //テーブルのデータを取得
            $table_sql="select * from $table_name
                        where 部署ＣＤ = $busho_cd
                        and 製造日 = '$date'
                        and コースＣＤ=$course_cd
                        ";

            $pdo = new PDO($dsn, $user, $password);
            $stmt = $pdo->query($table_sql);
            $table_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;

            //送信用SQLを生成
            $map = $this->GetMapping($table_name);
            $send_sql .= "delete from {$map['TableName']}
                          where department_code = $busho_cd
                            and production_date = '$date'
                            and course_code= $course_cd
                        ;";
            foreach($table_data as $recode_data)
            {
                $send_sql .= $this->CreateInsertSQL($table_name,$map,$recode_data).";";
            }
            //送信リストに登録
            parent::StoreSendList($send_sql,true,$busho_cd,null,$course_cd, $notify_message);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * モバイル_対象商品テーブルを更新する
     * 部署ＣＤ単位でDelete/Insertする
     * @param 部署ＣＤ
     * @param 通知メッセージ
     */
    public function UpdateTargetProductData($busho_cd, $notify_message = null)
    {
        try {
            $table_name = "モバイル_対象商品";
            $del_sql = "delete from TargetProductData
                    where department_code = $busho_cd
                ";
            $table_sql = "select * from $table_name
                where 部署ＣＤ = $busho_cd
            ";
            $this->InsertMultiRow($table_name, null, $table_sql, true, $busho_cd, null, null, $del_sql, $notify_message);
        } catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * 受注テーブルを更新する
     * @param 部署ＣＤ
     * @param 配送日
     * @param 得意先ＣＤ
     * @param メッセージ
     */
    public function UpdateOrderData($busho_cd,$DeliveryDate,$customer_cd,$course_cd, $notify_message = null)
    {
        try {
            $q_date = new Carbon($DeliveryDate);
            $q_date = $q_date->format('Y/m/d');
            $table_name = "注文データ";
            $del_sql = "delete from OrderData
                    where order_date='$q_date'
                    and department_code = $busho_cd
                    and customer_code = $customer_cd
                    and delivery_date='$q_date'
                ";
            $table_sql = "select * from $table_name
                        where 注文日付='$q_date'
                        and 部署ＣＤ = $busho_cd
                        and 得意先ＣＤ = $customer_cd
                        and 配送日='$q_date'
                ";
            $order_num_sql="select 商品ＣＤ,(sum(isnull(現金個数,0)) + sum(isnull(掛売個数,0)))as 注文個数 from 注文データ
                            where 注文区分=0
                            and 注文日付='$q_date'
                            and 部署ＣＤ = $busho_cd
                            and 得意先ＣＤ = $customer_cd
                            and 配送日='$q_date'
                            group by 商品ＣＤ
                ";
            $order_list=DB::select($order_num_sql);
            $after_sql = "";
            foreach ($order_list as $order_data)
            {
                $after_sql .= "update SaleInputData set
                             updated_at=now()
                            ,achievements_input=0
                            ,order_num={$order_data->注文個数}
                            ,order_input=1
                            where department_code=$busho_cd
                            and customer_code=$customer_cd
                            and product_code={$order_data->商品ＣＤ}
                            and date='$q_date';";
                $after_sql .= "update ExpectedInputData set
                            updated_at=now()
                           ,order_num={$order_data->注文個数}
                           ,order_input=1
                           where department_code=$busho_cd
                           and customer_code=$customer_cd
                           and product_code={$order_data->商品ＣＤ}
                           and date='$q_date';";
           }
            $this->InsertMultiRow($table_name, null, $table_sql, false, $busho_cd, $customer_cd, $course_cd, $del_sql,null,$after_sql);
            $this->Execute(null, false, $busho_cd, $customer_cd, $course_cd ,$notify_message);
        } catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * 受注テーブルを一括更新する
     * @param 部署ＣＤ
     * @param 得意先ＣＤ
     * @param 配送期間の開始日
     * @param 配送期間の終了日
     */
    public function MultiUpdateOrderData($busho_cd,$customer_cd,$date_start,$date_end)
    {
        try {
            $cbs = new carbon($date_start);
            $q_date_start=$cbs->format("Y/m/d");
            $cbe = new carbon($date_end);
            $q_date_end=$cbe->format("Y/m/d");

            $table_name = "注文データ";
            $del_sql = "delete from OrderData
                    where delivery_date>='$q_date_start' and delivery_date<='$q_date_end'
                    and department_code = $busho_cd
                    and customer_code = $customer_cd
                ";
            $table_sql = "select * from $table_name
                        where 部署ＣＤ = $busho_cd
                        and 得意先ＣＤ = $customer_cd
                        and 配送日>='$q_date_start' and 配送日<='$q_date_end';
                ";
            $order_num_sql="select 商品ＣＤ,配送日,(sum(isnull(現金個数,0)) + sum(isnull(掛売個数,0)))as 注文個数 from 注文データ
                        where 注文区分=0
                        and 部署ＣＤ = $busho_cd
                        and 得意先ＣＤ = $customer_cd
                        and 配送日>='$q_date_start' and 配送日<='$q_date_end'
                        group by 商品ＣＤ,配送日
                        ;";
            $order_list=DB::select($order_num_sql);
            $after_sql="";
            foreach ($order_list as $order_data)
            {
                $cbh = new carbon($order_data->配送日);
                $q_order_date = $cbh->format('Y/m/d');

                $after_sql .= "update SaleInputData set
                             updated_at=now()
                            ,achievements_input=0
                            ,order_num={$order_data->注文個数}
                            ,order_input=1
                            where department_code=$busho_cd
                            and customer_code=$customer_cd
                            and product_code={$order_data->商品ＣＤ}
                            and date='{$q_order_date}';";
                $after_sql .= "update ExpectedInputData set
                             updated_at=now()
                            ,order_num={$order_data->注文個数}
                            ,order_input=1
                            where department_code=$busho_cd
                            and customer_code=$customer_cd
                            and product_code={$order_data->商品ＣＤ}
                            and date='{$q_order_date}';";
            }
            $this->InsertMultiRow($table_name, null, $table_sql, false, $busho_cd, $customer_cd, null, $del_sql,null,$after_sql);
        } catch (Exception $exception) {
            throw $exception;
        }
    }
    /**
     * 売上明細データを更新する
     * @param 更新データ
     */
    public function UpdateUriageMeisaiData($UriageMeisaiData)
    {
        try {
            $table_name = "売上データ明細";
            $map = $this->GetMapping($table_name);
            $send_sql="";
            foreach ($UriageMeisaiData as $record_data) {
                $sql = $this->CreateUpdateSQL($table_name,$map,$record_data);
                $send_sql .= ";$sql";
            }
            if($send_sql)
            {
                $send_sql=substr($send_sql,1);
            }
            parent::StoreSendList($send_sql,true);
        } catch (Exception $exception) {
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
