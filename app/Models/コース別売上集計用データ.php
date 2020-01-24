<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property string $日付
 * @property int $部署ＣＤ
 * @property int $コースＣＤ
 * @property float $経費１
 * @property float $経費２
 * @property float $経費３
 * @property float $経費４
 * @property float $予備１
 * @property float $予備２
 * @property int $予備区分１
 * @property int $予備区分２
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class コース別売上集計用データ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'コース別売上集計用データ';

    /**
     * @var array
     */
    protected $fillable = ['日付', '部署ＣＤ', 'コースＣＤ', '経費１', '経費２', '経費３', '経費４', '予備１', '予備２', '予備区分１', '予備区分２', '修正担当者ＣＤ', '修正日'];

}
