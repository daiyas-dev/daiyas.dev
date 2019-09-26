<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $部署ＣＤ
 * @property int $コースＣＤ
 * @property int $ＳＥＱ
 * @property float $得意先ＣＤ
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class コーステーブル extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'コーステーブル';

    /**
     * @var array
     */
    protected $fillable = ['得意先ＣＤ', '修正担当者ＣＤ', '修正日'];

}
