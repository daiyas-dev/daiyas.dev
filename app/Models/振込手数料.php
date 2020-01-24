<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property float $振込手数料
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 振込手数料 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '振込手数料';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = '振込手数料';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'float';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['修正担当者ＣＤ', '修正日'];

}
