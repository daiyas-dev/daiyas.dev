<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $Web受注ID
 * @property string $Web得意先ＣＤ
 * @property string $配送日
 * @property string $注文日時
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class Web受注データ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Web受注データ';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'Web受注ID';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['Web得意先ＣＤ', '配送日', '注文日時', '修正担当者ＣＤ', '修正日'];

}
