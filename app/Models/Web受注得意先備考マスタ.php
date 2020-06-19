<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $Web得意先ＣＤ
 * @property int $備考ID
 * @property string $文言
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class Web受注得意先備考マスタ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Web受注得意先備考マスタ';

    /**
     * @var array
     */
    protected $fillable = ['文言', '修正担当者ＣＤ', '修正日'];

}
