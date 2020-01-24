<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $担当者ＣＤ
 * @property string $日付
 * @property string $アクション
 * @property string $IP
 */
class SYSログ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'SYSログ';

    /**
     * @var array
     */
    protected $fillable = ['担当者ＣＤ', '日付', 'アクション', 'IP'];

}
