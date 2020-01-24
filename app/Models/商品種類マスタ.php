<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $部署グループ
 * @property int $商品種類
 * @property int $商品ＣＤ
 * @property string $商品種類名
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 商品種類マスタ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '商品種類マスタ';

    /**
     * @var array
     */
    protected $fillable = ['商品種類名', '修正担当者ＣＤ', '修正日'];

}
