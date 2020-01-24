<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property string $日付
 * @property int $最大印刷番号
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 空領収証発行 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '空領収証発行';

    /**
     * @var array
     */
    protected $fillable = ['日付', '最大印刷番号', '修正担当者ＣＤ', '修正日'];

}
