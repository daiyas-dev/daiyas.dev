<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $対象日付
 * @property string $名称
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 祝日マスタ extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = '祝日マスタ';

    /**
     * The primary key for the model.
     * 
     * @var string
     */
    protected $primaryKey = '対象日付';

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
    protected $fillable = ['名称', '修正担当者ＣＤ', '修正日'];

}
