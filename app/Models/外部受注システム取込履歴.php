<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $ID
 * @property string $ファイル名
 * @property string $取込日付
 * @property int $担当者CD
 */
class 外部受注システム取込履歴 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '外部受注システム取込履歴';

    /**
     * @var array
     */
    protected $fillable = ['ID', 'ファイル名', '取込日付', '担当者CD'];

}
