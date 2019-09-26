<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property float $得意先ＣＤ
 * @property int $商品ＣＤ
 * @property float $単価
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 得意先単価マスタ extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = '得意先単価マスタ';

    /**
     * @var array
     */
    protected $fillable = ['単価', '修正担当者ＣＤ', '修正日'];

}
