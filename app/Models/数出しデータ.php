<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property string $日付
 * @property int $コースＣＤ
 * @property float $単価１
 * @property float $単価２
 * @property float $単価３
 * @property float $チケット１
 * @property float $チケット２
 * @property float $予備１
 * @property float $予備２
 * @property float $修正担当者ＣＤ
 * @property string $修正日
 */
class 数出しデータ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '数出しデータ';

    /**
     * @var array
     */
    protected $fillable = ['単価１', '単価２', '単価３', 'チケット１', 'チケット２', '予備１', '予備２', '修正担当者ＣＤ', '修正日'];

}
