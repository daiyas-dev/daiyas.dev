<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $部署ＣＤ
 * @property float $受注Ｎｏ
 * @property string $注文日付
 * @property int $エリアＣＤ
 * @property int $配達順
 * @property float $得意先ＣＤ
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 仕出しエリアデータ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '仕出しエリアデータ';

    /**
     * @var array
     */
    protected $fillable = ['エリアＣＤ', '配達順', '得意先ＣＤ', '修正担当者ＣＤ', '修正日'];

}
