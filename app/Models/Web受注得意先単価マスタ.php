<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $商品ＣＤ
 * @property string $適用開始日
 * @property string $Web得意先ＣＤ
 * @property float $単価
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class Web受注得意先単価マスタ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Web受注得意先単価マスタ';

    /**
     * @var array
     */
    protected $fillable = ['単価', '修正担当者ＣＤ', '修正日'];

}
