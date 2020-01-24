<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $税区分
 * @property string $消費税名称
 * @property int $消費税率
 * @property int $内外区分
 * @property string $適用年月
 * @property int $現在使用FLG
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 消費税率マスタ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '消費税率マスタ';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = '税区分';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['消費税名称', '消費税率', '内外区分', '適用年月', '現在使用FLG', '修正担当者ＣＤ', '修正日'];

}
