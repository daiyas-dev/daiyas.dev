<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $部署ＣＤ
 * @property int $コースＣＤ
 * @property float $相手コースＣＤ
 * @property string $日付
 * @property int $商品ＣＤ
 * @property int $ＳＥＱ
 * @property float $個数
 * @property boolean $確認フラグ
 * @property boolean $相手確認フラグ
 * @property string $修正日
 */
class モバイル移動入力 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'モバイル_移動入力';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    // protected $primaryKey = ['部署ＣＤ', 'コースＣＤ', '相手コースＣＤ', '日付', '商品ＣＤ'];
    // public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['ＳＥＱ', '個数', '確認フラグ', '相手確認フラグ', '修正日'];

}
