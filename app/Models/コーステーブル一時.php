<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $部署ＣＤ
 * @property int $コースＣＤ
 * @property int $管理ＣＤ
 * @property int $ＳＥＱ
 * @property float $得意先ＣＤ
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class コーステーブル一時 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'コーステーブル一時';

    /**
     * @var array
     */
    protected $fillable = ['得意先ＣＤ', '修正担当者ＣＤ', '修正日'];

}
