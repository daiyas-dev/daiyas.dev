<?php

namespace App\Models2;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $ユニークid
 * @property string $日本語名
 */
class 日本語テーブル extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = '日本語テーブル';

    /**
     * The primary key for the model.
     * 
     * @var string
     */
    protected $primaryKey = 'ユニークid';

    /**
     * The "type" of the auto-incrementing ID.
     * 
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['日本語名'];

}
