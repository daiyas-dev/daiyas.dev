<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $注文区分
 * @property string $注文日付
 * @property int $部署ＣＤ
 * @property float $得意先ＣＤ
 * @property string $配送日
 * @property int $明細行Ｎｏ
 * @property string $注文時間
 * @property int $商品ＣＤ
 * @property int $商品区分
 * @property int $入力区分
 * @property float $現金個数
 * @property float $現金金額
 * @property float $掛売個数
 * @property float $掛売金額
 * @property string $備考１
 * @property string $備考２
 * @property string $備考３
 * @property string $備考４
 * @property string $備考５
 * @property float $予備金額１
 * @property float $予備金額２
 * @property int $予備ＣＤ１
 * @property int $予備ＣＤ２
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 * @property string $特記_社内用
 * @property string $特記_配送用
 * @property string $特記_通知用
 */
class 注文データ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '注文データ';

    /**
     * @var array
     */
    protected $fillable = ['注文時間', '商品ＣＤ', '商品区分', '入力区分', '現金個数', '現金金額', '掛売個数', '掛売金額', '備考１', '備考２', '備考３', '備考４', '備考５', '予備金額１', '予備金額２', '予備ＣＤ１', '予備ＣＤ２', '修正担当者ＣＤ', '修正日', '特記_社内用', '特記_配送用', '特記_通知用'];

}
