<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $Sta_ID
 * @property string $Sta_Name
 * @property int $Sta_DelFlg
 * @property string $Sta_NewDate
 * @property string $Sta_UpdDate
 */
class CStateMaster extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'C_StateMaster';

    /**
     * @var array
     */
    protected $fillable = ['Sta_ID', 'Sta_Name', 'Sta_DelFlg', 'Sta_NewDate', 'Sta_UpdDate'];

}
