<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $部署ＣＤ
 * @property string $ファイル名
 * @property int $レコード番号
 * @property int $店番
 * @property string $取引店
 * @property int $全銀科目コード
 * @property int $預金種類コード
 * @property string $預金種類
 * @property string $口座番号
 * @property string $口座名義
 * @property string $照会期間
 * @property string $照会方法
 * @property string $操作日
 * @property string $操作時刻
 * @property string $取引日
 * @property string $指定日
 * @property string $取引区分
 * @property string $依頼人名
 * @property string $金融機関名
 * @property string $支店名
 * @property string $EDI情報
 * @property float $入金金額
 * @property float $振込手数料
 * @property int $得意先ＣＤ
 * @property int $依頼人登録区分
 * @property int $入金登録区分
 * @property string $入金日
 * @property int $入金伝票Ｎｏ
 * @property integer $結果
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 振込明細 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '振込明細';

    /**
     * @var array
     */
    protected $fillable = ['店番', '取引店', '全銀科目コード', '預金種類コード', '預金種類', '口座番号', '口座名義', '照会期間', '照会方法', '操作日', '操作時刻', '取引日', '指定日', '取引区分', '依頼人名', '金融機関名', '支店名', 'EDI情報', '入金金額', '振込手数料', '得意先ＣＤ', '依頼人登録区分', '入金登録区分', '入金日', '入金伝票Ｎｏ', '結果', '修正担当者ＣＤ', '修正日'];

}
