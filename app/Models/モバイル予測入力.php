<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $部署ＣＤ
 * @property float $得意先ＣＤ
 * @property string $日付
 * @property int $商品ＣＤ
 * @property float $行Ｎｏ
 * @property float $見込数
 * @property boolean $見込入力
 * @property float $注文数
 * @property boolean $注文入力
 * @property boolean $更新フラグ
 * @property string $修正日
 */
class モバイル予測入力 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'モバイル_予測入力';

    /**
     * @var array
     */
    protected $fillable = ['行Ｎｏ', '見込数', '見込入力', '注文数', '注文入力', '更新フラグ', '修正日'];

}
