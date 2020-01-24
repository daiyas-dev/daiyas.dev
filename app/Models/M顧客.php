<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $顧客コード
 * @property integer $状態
 * @property integer $失客理由
 * @property string $失客日
 * @property string $承認日
 * @property int $承認者コード
 * @property string $登録日
 * @property string $新規顧客登録日
 * @property integer $受注顧客区分
 * @property int $部門コード
 * @property string $顧客名称
 * @property string $顧客略称
 * @property string $顧客カナ
 * @property string $顧客郵便番号
 * @property string $顧客住所1
 * @property string $顧客住所2
 * @property string $顧客TEL番号1
 * @property string $顧客内線番号1
 * @property string $顧客TEL番号2
 * @property string $顧客内線番号2
 * @property string $顧客TEL番号3
 * @property string $顧客内線番号3
 * @property string $顧客FAX番号1
 * @property string $顧客FAX番号2
 * @property string $顧客FAX番号3
 * @property string $顧客担当者名
 * @property int $分類コード1
 * @property int $分類コード2
 * @property int $分類コード3
 * @property integer $締サイクル
 * @property integer $締日付
 * @property integer $入金日
 * @property integer $入金サイト
 * @property integer $税区分
 * @property integer $税処理
 * @property integer $集金方法区分1
 * @property integer $集金方法区分2
 * @property integer $手数料顧客負担
 * @property string $振替元金融機関コード
 * @property string $振替元支店コード
 * @property integer $振替元預金種類
 * @property string $振替元口座
 * @property string $振替名義人
 * @property string $振替委託者コード
 * @property int $振替自社取引銀行ID
 * @property float $チケット内数
 * @property float $SV内数
 * @property int $受注顧客コード
 * @property int $請求顧客コード
 * @property int $配送グループ顧客コード
 * @property int $営業担当者コード
 * @property int $獲得営業担当者コード
 * @property int $登録担当者コード
 * @property integer $曜日0休日
 * @property integer $曜日1休日
 * @property integer $曜日2休日
 * @property integer $曜日3休日
 * @property integer $曜日4休日
 * @property integer $曜日5休日
 * @property integer $曜日6休日
 * @property integer $曜日9休日
 * @property integer $受注方法区分
 * @property string $電話確認時刻
 * @property integer $味噌汁配布区分
 * @property integer $ふりかけ配布区分
 * @property integer $弁当当日回収区分
 * @property int $既定製造パターン
 * @property integer $納品書発行区分
 * @property integer $領収書発行区分
 * @property integer $請求書区分別頁
 * @property integer $請求内訳区分
 * @property integer $請求書敬称
 * @property integer $現場区分
 * @property string $顧客メモ
 * @property string $発信メモ
 * @property string $配送メモ
 * @property integer $未使用フラグ
 * @property string $Last日時
 * @property int $Last社員コード
 * @property string $Lastホスト名
 */
class M顧客 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'M顧客';

    /**
     * @var array
     */
    protected $fillable = ['顧客コード', '状態', '失客理由', '失客日', '承認日', '承認者コード', '登録日', '新規顧客登録日', '受注顧客区分', '部門コード', '顧客名称', '顧客略称', '顧客カナ', '顧客郵便番号', '顧客住所1', '顧客住所2', '顧客TEL番号1', '顧客内線番号1', '顧客TEL番号2', '顧客内線番号2', '顧客TEL番号3', '顧客内線番号3', '顧客FAX番号1', '顧客FAX番号2', '顧客FAX番号3', '顧客担当者名', '分類コード1', '分類コード2', '分類コード3', '締サイクル', '締日付', '入金日', '入金サイト', '税区分', '税処理', '集金方法区分1', '集金方法区分2', '手数料顧客負担', '振替元金融機関コード', '振替元支店コード', '振替元預金種類', '振替元口座', '振替名義人', '振替委託者コード', '振替自社取引銀行ID', 'チケット内数', 'SV内数', '受注顧客コード', '請求顧客コード', '配送グループ顧客コード', '営業担当者コード', '獲得営業担当者コード', '登録担当者コード', '曜日0休日', '曜日1休日', '曜日2休日', '曜日3休日', '曜日4休日', '曜日5休日', '曜日6休日', '曜日9休日', '受注方法区分', '電話確認時刻', '味噌汁配布区分', 'ふりかけ配布区分', '弁当当日回収区分', '既定製造パターン', '納品書発行区分', '領収書発行区分', '請求書区分別頁', '請求内訳区分', '請求書敬称', '現場区分', '顧客メモ', '発信メモ', '配送メモ', '未使用フラグ', 'Last日時', 'Last社員コード', 'Lastホスト名'];

}
