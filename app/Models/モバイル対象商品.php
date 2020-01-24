<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $部署ＣＤ
 * @property int $商品ＣＤ
 * @property boolean $主要商品FLG
 * @property boolean $期間限定FLG
 * @property string $販売期間開始
 * @property string $販売期間終了
 * @property string $修正日
 */
class モバイル対象商品 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'モバイル_対象商品';

    /**
     * @var array
     */
    protected $fillable = ['主要商品FLG', '期間限定FLG', '販売期間開始', '販売期間終了', '修正日'];

}
