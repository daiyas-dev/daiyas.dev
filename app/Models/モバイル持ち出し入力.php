<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $部署ＣＤ
 * @property int $コースＣＤ
 * @property string $持ち出し日付
 * @property int $商品ＣＤ
 * @property float $個数
 * @property string $修正日
 */
class モバイル持ち出し入力 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'モバイル_持ち出し入力';

    /**
     * @var array
     */
    protected $fillable = ['個数', '修正日'];

}
