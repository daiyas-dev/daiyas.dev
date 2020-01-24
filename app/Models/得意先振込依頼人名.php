<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $依頼人ID
 * @property int $得意先ＣＤ
 * @property string $振込依頼人名
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 得意先振込依頼人名 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '得意先振込依頼人名';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = '依頼人ID';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['得意先ＣＤ', '振込依頼人名', '修正担当者ＣＤ', '修正日'];

}
