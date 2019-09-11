<?php

namespace App\Models2;

use Illuminate\Database\Eloquent\Model;

/**
 * @property integer $info_no
 * @property string $info_title
 * @property string $info_memo
 * @property integer $user_id
 * @property string $start_date
 * @property string $end_date
 * @property string $created_at
 * @property string $updated_at
 */
class Information extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'informations';

    /**
     * The primary key for the model.
     * 
     * @var string
     */
    protected $primaryKey = 'info_no';

    /**
     * The "type" of the auto-incrementing ID.
     * 
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['info_title', 'info_memo', 'user_id', 'start_date', 'end_date', 'created_at', 'updated_at'];

}
