<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $部署ＣＤ
 * @property int $コースＣＤ
 * @property int $管理ＣＤ
 * @property boolean $一時フラグ
 * @property string $適用開始日
 * @property string $適用終了日
 * @property string $備考
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class コーステーブル管理 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'コーステーブル管理';

    /**
     * @var array
     */
    protected $fillable = ['一時フラグ', '適用開始日', '適用終了日', '備考', '修正担当者ＣＤ', '修正日'];

}
