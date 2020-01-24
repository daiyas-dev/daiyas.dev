<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property string $注文区分
 * @property string $注文日付
 * @property string $注文時間
 * @property string $部署ＣＤ
 * @property string $得意先ＣＤ
 * @property string $配送日
 * @property string $明細行Ｎｏ
 * @property string $商品ＣＤ
 * @property string $商品区分
 * @property string $入力区分
 * @property string $現金個数
 * @property string $現金金額
 * @property string $掛売個数
 * @property string $掛売金額
 * @property string $備考１
 * @property string $備考２
 * @property string $備考３
 * @property string $備考４
 * @property string $備考５
 * @property string $予備金額１
 * @property string $予備金額２
 * @property string $予備ＣＤ１
 * @property string $予備ＣＤ２
 * @property string $修正担当者ＣＤ
 * @property string $修正日
 */
class 注文データ20150518予測込 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '注文データ20150518予測込';

    /**
     * @var array
     */
    protected $fillable = ['注文区分', '注文日付', '注文時間', '部署ＣＤ', '得意先ＣＤ', '配送日', '明細行Ｎｏ', '商品ＣＤ', '商品区分', '入力区分', '現金個数', '現金金額', '掛売個数', '掛売金額', '備考１', '備考２', '備考３', '備考４', '備考５', '予備金額１', '予備金額２', '予備ＣＤ１', '予備ＣＤ２', '修正担当者ＣＤ', '修正日'];

}
