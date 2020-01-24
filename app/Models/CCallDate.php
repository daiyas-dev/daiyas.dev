<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $Cal_ID
 * @property string $Cal_TelNo
 * @property string $Cal_ExtNo
 * @property string $Cal_Date
 * @property string $Cal_Time
 * @property string $Cal_CallTelNo
 * @property string $Cal_CallTelName
 * @property float $Cal_CustNo
 * @property string $Cal_OpeName
 * @property int $Cal_State
 * @property int $Cal_DelFlg
 * @property string $Cal_NewDate
 * @property string $Cal_UpdDate
 */
class CCallDate extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'C_CallDate';

    /**
     * @var array
     */
    protected $fillable = ['Cal_ID', 'Cal_TelNo', 'Cal_ExtNo', 'Cal_Date', 'Cal_Time', 'Cal_CallTelNo', 'Cal_CallTelName', 'Cal_CustNo', 'Cal_OpeName', 'Cal_State', 'Cal_DelFlg', 'Cal_NewDate', 'Cal_UpdDate'];

}
