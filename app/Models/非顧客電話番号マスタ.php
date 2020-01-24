<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property string $電話番号
 * @property string $非顧客名称
 * @property string $非顧客カナ
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 非顧客電話番号マスタ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '非顧客電話番号マスタ';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = '電話番号';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['非顧客名称', '非顧客カナ', '修正担当者ＣＤ', '修正日'];

}
