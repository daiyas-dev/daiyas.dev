<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $部署ＣＤ
 * @property int $コースＣＤ
 * @property int $ＳＥＱ
 * @property float $得意先ＣＤ
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class コーステーブル extends BaseModel
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

    /**
     * Join コースマスタ on コースＣＤ
     */
    public function コース()
    {
        return $this->belongsTo('App\Models\コースマスタ', 'コースＣＤ', 'コースＣＤ');
    }

    /**
     * Join 得意先マスタ on 得意先ＣＤ
     */
    public function 得意先()
    {
        return $this->belongsTo('App\Models\得意先マスタ', '得意先ＣＤ', '得意先ＣＤ');
    }

}
