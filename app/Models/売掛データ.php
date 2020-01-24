<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property string $日付
 * @property float $請求先ＣＤ
 * @property int $部署ＣＤ
 * @property float $前月残高
 * @property float $今月入金額
 * @property float $差引繰越額
 * @property float $今月売上額
 * @property float $今月残高
 * @property float $予備金額１
 * @property float $予備金額２
 * @property int $予備ＣＤ１
 * @property int $予備ＣＤ２
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 売掛データ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '売掛データ';

    /**
     * @var array
     */
    protected $fillable = ['部署ＣＤ', '前月残高', '今月入金額', '差引繰越額', '今月売上額', '今月残高', '予備金額１', '予備金額２', '予備ＣＤ１', '予備ＣＤ２', '修正担当者ＣＤ', '修正日'];

}
