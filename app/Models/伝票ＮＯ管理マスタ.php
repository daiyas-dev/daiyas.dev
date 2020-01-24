<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $部署ＣＤ
 * @property int $伝票種類
 * @property string $日付
 * @property float $伝票ＮＯ
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 伝票ＮＯ管理マスタ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '伝票ＮＯ管理マスタ';

    /**
     * @var array
     */
    protected $fillable = ['伝票ＮＯ', '修正担当者ＣＤ', '修正日'];

}
