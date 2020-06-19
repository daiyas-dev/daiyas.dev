<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $Web受注ID
 * @property int $注文ID
 * @property int $利用者ID
 * @property string $利用者CD
 * @property int $備考ID
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class Web受注データ利用者情報 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Web受注データ利用者情報';

    /**
     * @var array
     */
    protected $fillable = ['利用者CD', '備考ID', '修正担当者ＣＤ', '修正日'];

}
