<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property string $区分
 * @property int $備考ＣＤ
 * @property string $備考
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 備考マスタ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '備考マスタ';

    /**
     * @var array
     */
    protected $fillable = ['備考', '修正担当者ＣＤ', '修正日'];

}
