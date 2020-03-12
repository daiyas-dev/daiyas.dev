<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $銀行CD
 * @property string $銀行名
 * @property string $銀行名カナ
 * @property int $郵便フラグ
 */
class 金融機関名称 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '金融機関名称';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = '銀行CD';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['銀行名', '銀行名カナ', '郵便フラグ', '無効フラグ'];

}
