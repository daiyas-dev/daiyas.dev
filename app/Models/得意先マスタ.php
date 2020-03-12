<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property float $得意先ＣＤ
 * @property string $得意先名
 * @property string $得意先名カナ
 * @property string $得意先名略称
 * @property string $郵便番号
 * @property string $住所１
 * @property string $住所２
 * @property string $電話番号１
 * @property string $ＦＡＸ１
 * @property string $電話番号２
 * @property string $ＦＡＸ２
 * @property string $お届け先郵便番号
 * @property string $お届け先住所１
 * @property string $お届け先住所２
 * @property string $お届け先電話番号１
 * @property string $お届け先ＦＡＸ１
 * @property string $お届け先電話番号２
 * @property string $お届け先ＦＡＸ２
 * @property int $部署CD
 * @property int $売掛現金区分
 * @property int $電話確認区分
 * @property int $締区分
 * @property int $締日１
 * @property int $締日２
 * @property int $支払サイト
 * @property int $支払日
 * @property int $集金区分
 * @property float $請求先ＣＤ
 * @property int $支払方法１
 * @property int $支払方法２
 * @property float $集金手数料
 * @property float $金融機関CD
 * @property float $金融機関支店CD
 * @property string $記号番号
 * @property float $口座種別
 * @property float $口座番号
 * @property string $口座名義人
 * @property float $チケット枚数
 * @property float $サービスチケット枚数
 * @property float $営業担当者ＣＤ
 * @property float $獲得営業者ＣＤ
 * @property float $登録担当者ＣＤ
 * @property float $受注得意先ＣＤ
 * @property float $配送ｸﾞﾙｰﾌﾟＣＤ
 * @property int $受注方法
 * @property int $電話確認時間_時
 * @property int $電話確認時間_分
 * @property int $味噌汁区分
 * @property int $ふりかけ区分
 * @property int $納品書発行区分
 * @property int $空き容器回収区分
 * @property int $既定製造パターン
 * @property string $請求書敬称
 * @property int $請求書区分別頁
 * @property int $請求内訳区分
 * @property string $備考１
 * @property string $備考２
 * @property string $備考３
 * @property int $ＷＥＢ表示区分
 * @property int $取扱区分
 * @property string $ＩＤ
 * @property string $パスワード
 * @property int $状態区分
 * @property string $承認日
 * @property int $承認者ＣＤ
 * @property int $状態理由区分
 * @property int $受注顧客区分
 * @property string $休日設定
 * @property string $新規登録日
 * @property int $税区分
 * @property int $税処理
 * @property int $祝日配送区分
 * @property string $誕生日１
 * @property string $誕生日２
 * @property string $失客日
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 得意先マスタ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '得意先マスタ';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = '得意先ＣＤ';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'float';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['得意先名', '得意先名カナ', '得意先名略称', '郵便番号', '住所１', '住所２', '電話番号１', 'ＦＡＸ１', '電話番号２', 'ＦＡＸ２', 'お届け先郵便番号', 'お届け先住所１', 'お届け先住所２', 'お届け先電話番号１', 'お届け先ＦＡＸ１', 'お届け先電話番号２', 'お届け先ＦＡＸ２', '部署CD', '売掛現金区分', '電話確認区分', '締区分', '締日１', '締日２', '支払サイト', '支払日', '集金区分', '請求先ＣＤ', '支払方法１', '支払方法２', '集金手数料', '金融機関CD', '金融機関支店CD', '記号番号', '口座種別', '口座番号', '口座名義人', 'チケット枚数', 'サービスチケット枚数', '営業担当者ＣＤ', '獲得営業者ＣＤ', '登録担当者ＣＤ', '受注得意先ＣＤ', '配送ｸﾞﾙｰﾌﾟＣＤ', '受注方法', '電話確認時間_時', '電話確認時間_分', '味噌汁区分', 'ふりかけ区分', '納品書発行区分', '空き容器回収区分', '既定製造パターン', '請求書敬称', '請求書区分別頁', '請求内訳区分', '備考１', '備考２', '備考３', 'ＷＥＢ表示区分', '取扱区分', 'ＩＤ', 'パスワード', '状態区分', '承認日', '承認者ＣＤ', '状態理由区分', '受注顧客区分', '休日設定', '新規登録日', '税区分', '税処理', '祝日配送区分', '誕生日１', '誕生日２', '失客日', '修正担当者ＣＤ', '修正日', '窓口担当者名', '窓口部署', '窓口電話番号', '窓口メール', '得意先名スマホ用'];

}
