<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property float $得意先ＣＤ
 * @property int $商品ＣＤ
 * @property string $適用開始日
 * @property float $単価
 * @property float $固定数
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 得意先単価適用マスタ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '得意先単価適用マスタ';

    /**
     * @var array
     */
    protected $fillable = ['単価', '固定数', '修正担当者ＣＤ', '修正日'];

}
