<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $部署ＣＤ
 * @property int $コースＣＤ
 * @property float $得意先ＣＤ
 * @property string $日付
 * @property float $回収金額
 * @property int $月分
 * @property string $修正日
 */
class モバイル回収入力 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'モバイル_回収入力';

    /**
     * @var array
     */
    protected $fillable = ['回収金額', '月分', '修正日'];

}
