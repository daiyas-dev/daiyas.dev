<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property string $日付
 * @property int $コースＣＤ
 * @property int $行Ｎｏ
 * @property int $商品ＣＤ
 * @property float $移動数
 * @property int $相手コースＣＤ
 * @property int $確認フラグ
 * @property float $予備金額１
 * @property float $予備金額２
 * @property int $予備ＣＤ１
 * @property int $予備ＣＤ２
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 移動データ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '移動データ';

    /**
     * @var array
     */
    protected $fillable = ['行Ｎｏ', '商品ＣＤ', '移動数', '相手コースＣＤ', '確認フラグ', '予備金額１', '予備金額２', '予備ＣＤ１', '予備ＣＤ２', '修正担当者ＣＤ', '修正日'];

}
