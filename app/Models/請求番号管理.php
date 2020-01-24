<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $請求管理No
 * @property float $請求番号１
 * @property float $請求番号２
 * @property float $請求番号３
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 請求番号管理 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '請求番号管理';

    /**
     * @var array
     */
    protected $fillable = ['請求管理No', '請求番号１', '請求番号２', '請求番号３', '修正担当者ＣＤ', '修正日'];

}
