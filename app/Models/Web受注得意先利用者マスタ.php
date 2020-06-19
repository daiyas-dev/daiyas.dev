<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $Web得意先ＣＤ
 * @property int $利用者ID
 * @property string $利用者CD
 * @property float $得意先ＣＤ
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class Web受注得意先利用者マスタ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Web受注得意先利用者マスタ';

    /**
     * @var array
     */
    protected $fillable = ['利用者CD', '得意先ＣＤ', '修正担当者ＣＤ', '修正日'];

}
