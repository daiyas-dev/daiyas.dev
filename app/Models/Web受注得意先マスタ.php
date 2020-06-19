<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property float $得意先ＣＤ
 * @property string $Web得意先ＣＤ
 * @property string $Web得意先名
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class Web受注得意先マスタ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Web受注得意先マスタ';

    /**
     * @var array
     */
    protected $fillable = ['Web得意先名', '修正担当者ＣＤ', '修正日'];

}
