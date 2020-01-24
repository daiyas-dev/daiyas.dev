<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $部署ＣＤ
 * @property string $製造日
 * @property int $コースＣＤ
 * @property int $得意先ＣＤ
 * @property int $製造パターン
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 日別得意先製造パターン extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '日別得意先製造パターン';

    /**
     * @var array
     */
    protected $fillable = ['製造パターン', '修正担当者ＣＤ', '修正日'];

}
