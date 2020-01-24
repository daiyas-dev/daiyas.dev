<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $グループ種別
 * @property string $グループ区分
 * @property int $グループＳＥＱ
 * @property int $部署CD
 * @property string $グループ種別名
 * @property int $コースＣＤ
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class コース別グループマスタ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'コース別グループマスタ';

    /**
     * @var array
     */
    protected $fillable = ['グループ種別名', 'コースＣＤ', '修正担当者ＣＤ', '修正日'];

}
