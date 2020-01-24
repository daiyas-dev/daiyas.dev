<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $営業担当者ＣＤ
 * @property string $目標年月
 * @property int $目標食数
 * @property float $目標売上
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 営業目標 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '営業目標';

    /**
     * @var array
     */
    protected $fillable = ['目標食数', '目標売上', '修正担当者ＣＤ', '修正日'];

}
