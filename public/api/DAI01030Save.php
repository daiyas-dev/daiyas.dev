<?php
header('Content-Type: application/json; charset=UTF-8');
$req = count($_REQUEST) > 0 ? $_REQUEST : json_decode(file_get_contents("php://input"), true);

if (!isset($req)) {
    print json_encode([], JSON_PRETTY_PRINT);
   return;
}

$skip = [];

$BushoCd = $req['BushoCd'];
$CustomerCd = $req['CustomerCd'];
$DeliveryDate = $req['DeliveryDate'];

if (!!isset($req['CourseCd'])) {
    $CourseCd = $req['CourseCd'];
}

$SaveList = $req['SaveList'];

$pdo = null;
try {
    $dsn = 'sqlsrv:server=127.0.0.1;database=daiyas';
    $user = 'daiyas';
    $password = 'daiyas';

    $pdo = new PDO($dsn, $user, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->beginTransaction();

    date_default_timezone_set('Asia/Tokyo');
    $date = date("Y-m-d H:i:s");
    foreach ($SaveList as $rec) {
        if (isset($rec['修正日']) && !!$rec['修正日']) {
            $SelectSql = "
                SELECT
                    *
                FROM
                    注文データ
                WHERE
                    注文区分=?
                AND 注文日付=?
                AND 部署ＣＤ=?
                AND 得意先ＣＤ=?
                AND 配送日=?
                AND 明細行Ｎｏ=?
            ";

            $stmt = $pdo->prepare($SelectSql);
            $stmt->execute(
                array(
                    $rec['注文区分'],
                    $rec['注文日付'],
                    $rec['部署ＣＤ'],
                    $rec['得意先ＣＤ'],
                    $rec['配送日'],
                    $rec['明細行Ｎｏ']
                )
            );
            $r = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($r) != 1) {
                $skip[] = ["target" => $rec, "current" => null];
                continue;
            } elseif ($rec['修正日'] != $r[0]['修正日']) {
                $skip[] = ["target" => $rec, "current" => $r[0]];
                continue;
            }
        }
    }

    $DeleteSql = "
        DELETE 注文データ
        WHERE
            注文区分=?
        AND 部署ＣＤ=?
        AND 得意先ＣＤ=?
        AND 配送日=?
    ";
    $stmt = $pdo->prepare($DeleteSql);
    $stmt->execute(
        array(
            $rec['注文区分'],
            $rec['部署ＣＤ'],
            $rec['得意先ＣＤ'],
            $rec['配送日'],
        )
    );

    $no = 0;
    foreach ($SaveList as $rec) {
        $no++;
        if (!!isset($rec['現金個数']) && !!isset($rec['掛売個数'])) {
            $data = $rec;
            $data['修正日'] = $date;
            $data['明細行Ｎｏ'] = $no;
            $data['備考１'] = $data['備考１'] ?? '';
            $data['備考２'] = $data['備考２'] ?? '';
            $data['備考３'] = $data['備考３'] ?? '';
            $data['備考４'] = $data['備考４'] ?? '';
            $data['備考５'] = $data['備考５'] ?? '';
            $data['特記_社内用'] = $data['特記_社内用'] ?? '';
            $data['特記_配送用'] = $data['特記_配送用'] ?? '';
            $data['特記_通知用'] = $data['特記_通知用'] ?? '';

            $InsertSql = "
                INSERT INTO 注文データ
                VALUES (
                     ?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                    ,?
                )
            ";

            $stmt = $pdo->prepare($InsertSql);
            $stmt->execute(
                array(
                    $data['注文区分'],
                    $data['注文日付'],
                    $data['注文時間'],
                    $data['部署ＣＤ'],
                    $data['得意先ＣＤ'],
                    $data['配送日'],
                    $data['明細行Ｎｏ'],
                    $data['商品ＣＤ'],
                    $data['商品区分'],
                    $data['入力区分'],
                    $data['現金個数'],
                    $data['現金金額'],
                    $data['掛売個数'],
                    $data['掛売金額'],
                    $data['備考１'],
                    $data['備考２'],
                    $data['備考３'],
                    $data['備考４'],
                    $data['備考５'],
                    $data['予備金額１'],
                    $data['予備金額２'],
                    $data['予備ＣＤ１'],
                    $data['予備ＣＤ２'],
                    $data['修正担当者ＣＤ'],
                    $data['修正日'],
                    $data['Web受注ID'] ?? null,
                    $data['特記_社内用'],
                    $data['特記_配送用'],
                    $data['特記_通知用']
                )
            );
        }
    }
    if (count($skip) > 0) {
        $pdo->rollBack();
    } else {
        $pdo->commit();
    }

    //モバイル連携データの送信
    //事前に実行するSQLを作成(既存の注文データを削除)

    $DeliveryDate = date('Y/m/d', strtotime($DeliveryDate));

    $send_sql = "delete from OrderData where department_code = $BushoCd and customer_code = $CustomerCd and delivery_date='$DeliveryDate';";

    //メインのSQL
    $table_sql = "select * FROM 注文データ WHERE 部署ＣＤ = $BushoCd AND 得意先ＣＤ = $CustomerCd AND 配送日='$DeliveryDate'";
    $stmt = $pdo->query($table_sql);
    $table_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $public_path="C:/daiyas/workspace/daiyas/public";
    $map = json_decode(file_get_contents($public_path."/dbmapping/pwa.txt"),true);
    $map = $map["注文データ"];

    $stmt = $pdo->query($table_sql);
    $table_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //送信用SQLを生成
    foreach($table_data as $record_data)
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
        $new_table_name=$map["TableName"];
        $fields="";
        $values="";
        foreach($new_data as $key=>$val)
        {
            $fields .= ", $key";
            //値がNULLなら文字列NULL、それ以外はシングルクォート付きの値で戻す
            $q_val = $val===NULL ? "null" : "'$val'";
            $values .= ", $q_val";
        }
        $fields=substr($fields,1);
        $values=substr($values,1);
        $send_sql .= "insert into $new_table_name ( $fields )values( $values );";
    }

    //事後に実行するSQL(モバイル予測入力、モバイル販売入力の更新)
    //注文数の取得
    $order_num_sql="select 商品ＣＤ,(sum(isnull(現金個数,0)) + sum(isnull(掛売個数,0)))as 注文個数 from 注文データ where 注文区分=0 and 部署ＣＤ = $BushoCd and 得意先ＣＤ = $CustomerCd and 配送日='$DeliveryDate' group by 商品ＣＤ";
    $stmt = $pdo->query($order_num_sql);
    $order_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($order_list as $order_data)
    {
        $send_sql .= "update SaleInputData set
            updated_at=now()
            ,achievements_input=0
            ,order_num={$order_data['注文個数']}
            ,order_input=1
            where department_code=$BushoCd
            and customer_code=$CustomerCd
            and product_code={$order_data['商品ＣＤ']}
            and date='$DeliveryDate';";
        $send_sql .= "update ExpectedInputData set
            updated_at=now()
        ,order_num={$order_data['注文個数']}
        ,order_input=1
        where department_code=$BushoCd
        and customer_code=$CustomerCd
        and product_code={$order_data['商品ＣＤ']}
        and date='$DeliveryDate';";
    }

    //モバイル送信リストに書き込むデータの作成
    $controller_id="DAI0130Save";
    $method_name="DAI0130Save";
    $q_BushoCd   = $BushoCd;
    $q_CustomerCd= $CustomerCd;
    $q_course_cd  = empty($course_cd) ? 'null' : $course_cd;
    $esc_sql=str_replace("'","''",$send_sql);
    //メッセージの作成
    $Message = null;
    $now = date("Y/m/d");
    if ($DeliveryDate == $now) {
        //当日注文の場合、通知
        $Message = $req['Message'];
    }
    $q_notify_message  = empty($Message) ? '' : json_encode($Message);

    //モバイル送信リストに書き込む
    $while_cnt=0;//SQLが成功するまでループさせるが、念のため最大5回まで試行する。
    while ($while_cnt<5) {
        //送信IDを取得
        $next_id_Sql = "SELECT MAX(送信ＩＤ)+1 AS NEXT_ID FROM モバイル送信リスト";
        $stmt = $pdo->query($next_id_Sql);
        $send_id = $stmt->fetch()["NEXT_ID"];
        $send_id = $send_id==null ? 1 : $send_id;

        $ms_sql="INSERT INTO モバイル送信リスト(
                送信ＩＤ
            ,部署ＣＤ
            ,得意先ＣＤ
            ,コースＣＤ
            ,コントローラＩＤ
            ,メソッド名
            ,作成日時
            ,SQL
            ,通知メッセージ
            ,送信済フラグ
            ,送信済日時
            )VALUES(
                $send_id
                ,$q_BushoCd
                ,$q_CustomerCd
                ,$q_course_cd
                ,'$controller_id'
                ,'$method_name'
                ,GETDATE()
                ,'$esc_sql'
                ,'$q_notify_message'
                ,0
                ,null
            )
            ";
        try {
            $pdo->exec($ms_sql);
            break;//SQLが実行出来たらWhileを抜ける
        } catch (Exception $e) {
            //エラーが発生したらログを採取する
            $path='C:/daiyas/workspace/daiyas/storage/logs/';
            $timestamp_er=date("Y/m/d H:i:s");
            $timestamp_fn=str_replace('.', '', microtime(true));
            error_log("[" . $timestamp_er ."] ". $e->getMessage(), 3, $path."dai01030save_error_".$timestamp_fn.".log");
            $while_cnt++;
        }
    }

    //Web受注サーバへの注文データ連携(Web受注対象得意先のみ)
    $sql = "SELECT * FROM Web受注得意先マスタ WHERE 得意先ＣＤ = '$CustomerCd'";
    $stmt = $pdo->query($sql);
    $Data = $stmt->fetch(PDO::FETCH_ASSOC);
    $pdo = null;

    $Web_CustomerCd = $Data['Web得意先ＣＤ'] ?? null;

    if(!!$Web_CustomerCd){
        try{
            //2020-10-16
            //$url = "http://192.168.1.211/hellolaravel/public/api/setregistered";
            //TODO:本番URL
            $url="http://18.178.211.62/api/setregistered";

            // base64エンコード
            $post_data = array(
                'web_customer_code' => $Web_CustomerCd,
                'delivery_date' => $DeliveryDate,
                'registered_date'=> $date,
            );

            // cURLセッションを初期化
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url); // 取得するURLを指定
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // サーバー証明書の検証を行わない

            //Post実行
            $result = curl_exec($ch);
            $curl_error=curl_error($ch);

            // セッションを終了
            curl_close($ch);
            //echo 'RETURN:' . $result;
        }
        catch (Exception $e) {
        }
    }

    //登録完了後の後処理
    require("DAI01030SearchFunc.php");
    $Result = [
        'result' => true,
        "edited" => count($skip) > 0,
        "current" => count($skip) > 0 ? Search() : [],
        "busho_cd"=>$BushoCd,
        "customer_cd"=>$CustomerCd,
        "delivery_date"=>$DeliveryDate,
        "course_code"=> isset($req['CourseCd']) ? $req['CourseCd'] : null,
    ];
    print json_encode($Result, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    $pdo->rollBack();
    throw $e;
} finally {
    $pdo = null;
}

