<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property string $Tel_TelNo
 * @property float $Tel_CustNo
 * @property int $Tel_RepFlg
 * @property int $Tel_DelFlg
 * @property string $Tel_NewDate
 * @property string $Tel_UpdDate
 */
class CTelToCust extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'C_TelToCust';

    /**
     * @var array
     */
    protected $fillable = ['Tel_TelNo', 'Tel_CustNo', 'Tel_RepFlg', 'Tel_DelFlg', 'Tel_NewDate', 'Tel_UpdDate'];

}
