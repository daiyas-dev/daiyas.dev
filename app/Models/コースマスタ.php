<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $部署ＣＤ
 * @property int $コースＣＤ
 * @property int $コース区分
 * @property string $コース名
 * @property int $担当者ＣＤ
 * @property int $工場区分
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class コースマスタ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'コースマスタ';

    /**
     * @var array
     */
    protected $fillable = ['コース区分', 'コース名', '担当者ＣＤ', '工場区分', '修正担当者ＣＤ', '修正日'];

    /**
     * Join 担当者マスタ on 担当者ＣＤ
     */
    public function 担当者()
    {
        return $this->belongsTo('App\Models\担当者マスタ', '担当者ＣＤ', '担当者ＣＤ');
    }

}
