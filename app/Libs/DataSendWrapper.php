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
        },
        "得意先マスタ": {
            "TableName":"CustomerMaster",
            "PrimaryKey": ["得意先ＣＤ"],
            "Field": {
                "得意先ＣＤ":"customer_code",
                "得意先名":"customer_name",
                "得意先名カナ":"customer_kana",
                "得意先名略称":"customer_abbreviation",
                "郵便番号":"zip",
                "住所１":"address_1",
                "住所２":"address_2",
                "電話番号１":"tel_1",
                "ＦＡＸ１":"fax_1",
                "電話番号２":"tel_2",
                "ＦＡＸ２":"fax_2",
                "お届け先郵便番号":"delivery_zip",
                "お届け先住所１":"delivery_address_1",
                "お届け先住所２":"delivery_address_2",
                "お届け先電話番号１":"delivery_tel_1",
                "お届け先ＦＡＸ１":"delivery_fax_1",
                "お届け先電話番号２":"delivery_tel_2",
                "お届け先ＦＡＸ２":"delivery_fax_2",
                "部署CD":"department_code",
                "売掛現金区分":"cash_type",
                "電話確認区分":"tel_confirm_type",
                "締区分":"deadline_type",
                "締日１":"deadline_1",
                "締日２":"deadline_2",
                "支払サイト":"payment_site",
                "支払日":"payment_date",
                "集金区分":"collection_type",
                "請求先ＣＤ":"billing_code",
                "支払方法１":"payment_method_1",
                "支払方法２":"payment_method_2",
                "集金手数料":"collection_fee",
                "金融機関CD":"financial_code",
                "金融機関支店CD":"financial_branch_code",
                "記号番号":"symbol_num",
                "口座種別":"account_type",
                "口座番号":"account_num",
                "口座名義人":"account_holder",
                "チケット枚数":"ticket_num",
                "サービスチケット枚数":"service_ticket_num",
                "営業担当者ＣＤ":"sales_representative_code",
                "獲得営業者ＣＤ":"acquired_sales_person_code",
                "登録担当者ＣＤ":"registration_person_code",
                "受注得意先ＣＤ":"order_customer_code",
                "配送ｸﾞﾙｰﾌﾟＣＤ":"delivery_group_code",
                "受注方法":"ordering_method",
                "電話確認時間_時":"phone_confirmation_time_hours",
                "電話確認時間_分":"phone_confirmation_time_min",
                "味噌汁区分":"miso_soup_type",
                "ふりかけ区分":"furikake_type",
                "納品書発行区分":"delivery_note_type",
                "空き容器回収区分":"empty_container_collection_type",
                "既定製造パターン":"default_manufacturing_pattern",
                "請求書敬称":"invoice_title",
                "請求書区分別頁":"invoice_type_page",
                "請求内訳区分":"billing_type",
                "備考１":"remark_1",
                "備考２":"remark_2",
                "備考３":"remark_3",
                "ＷＥＢ表示区分":"web_display_type",
                "取扱区分":"handling_type",
                "ＩＤ":"id",
                "パスワード":"password",
                "状態区分":"state_type",
                "承認日":"approval_date",
                "承認者ＣＤ":"approver_code",
                "状態理由区分":"status_reason_type",
                "受注顧客区分":"ordering_customer_type",
                "休日設定":"holiday_setting",
                "新規登録日":"new_registration_date",
                "税区分":"tax_type",
                "税処理":"tax_processing",
                "祝日配送区分":"holiday_delivery_type",
                "誕生日１":"birthday_1",
                "誕生日２":"birthday_2",
                "失客日":"deleted_at",
                "修正担当者ＣＤ":"updated_responsible_code",
                "修正日":"updated_at",
                "窓口担当者名":"contact_parson_name",
                "窓口部署":"contact_department",
                "窓口電話番号":"contact_tel",
                "窓口メール":"contact_email",
                "得意先名スマホ用":"smartphone_customer_name"
                }
            },
            "日別得意先製造パターン": {
                "TableName":"DailyCustomerProductionPattern",
                "PrimaryKey": ["部署ＣＤ","製造日","コースＣＤ","得意先ＣＤ"],
                "Field": {
                    "部署ＣＤ":"department_code",
                    "製造日":"production_date",
                    "コースＣＤ":"course_code",
                    "得意先ＣＤ":"customer_code",
                    "製造パターン":"production_pattern",
                    "修正担当者ＣＤ":"updated_responsible_code",
                    "修正日":"updated_at"
                }
            }
        }';

    /**
     * 指定のテーブルのUpdateSQLをモバイル送信リストに登録する
     * @param テーブル名
     * @param テーブルの値
     * @param すぐに実行するか。null以外ならすぐに実行
     * @param 部署CD
     * @param 得意先CD
     * @param コースCD
     * @return void
     */
    public function Update($table_name,$table_data,$Immediate = null,$busho_cd = null,$customer_cd=null,$course_cd=null)
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

            //送信リストに登録
            parent::StoreSendList($sql,$Immediate,$busho_cd,$customer_cd,$course_cd);
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
     * @return void
     */
    public function Insert($table_name,$table_data,$Immediate = null,$busho_cd = null,$customer_cd=null,$course_cd=null)
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

            //送信リストに登録
            parent::StoreSendList($sql,$Immediate,$busho_cd,$customer_cd,$course_cd);
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
     * @return void
     */
    public function Delete($table_name,$table_data,$Immediate = null,$busho_cd = null,$customer_cd=null,$course_cd=null)
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

            //送信リストに登録
            parent::StoreSendList($sql,$Immediate,$busho_cd,$customer_cd,$course_cd);
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
     * @return void
     */
    public function Execute($sql,$Immediate = null,$busho_cd = null,$customer_cd=null,$course_cd=null)
    {
        try {
            parent::StoreSendList($sql,$Immediate,$busho_cd,$customer_cd,$course_cd);
        }
        catch (Exception $exception) {
            throw $exception;
        }
    }
}
