<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property float $得意先ＣＤ
 * @property int $商品ＣＤ
 * @property float $単価
 * @property string $適用開始日
 * @property int $固定数
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 得意先単価マスタ新 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '得意先単価マスタ新';

    /**
     * @var array
     */
    protected $fillable = ['得意先ＣＤ', '商品ＣＤ', '単価', '適用開始日', '固定数', '修正担当者ＣＤ', '修正日'];

}
