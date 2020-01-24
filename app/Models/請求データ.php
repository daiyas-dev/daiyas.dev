<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property string $請求日付
 * @property int $部署ＣＤ
 * @property float $請求先ＣＤ
 * @property float $３回前残高
 * @property float $前々回残高
 * @property float $前回残高
 * @property float $今回入金額
 * @property float $差引繰越額
 * @property float $今回売上額
 * @property float $消費税額
 * @property float $今回請求額
 * @property string $請求日範囲開始
 * @property string $請求日範囲終了
 * @property float $予備金額１
 * @property float $予備金額２
 * @property string $回収予定日
 * @property int $予備ＣＤ１
 * @property int $予備ＣＤ２
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 請求データ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '請求データ';

    /**
     * @var array
     */
    protected $fillable = ['３回前残高', '前々回残高', '前回残高', '今回入金額', '差引繰越額', '今回売上額', '消費税額', '今回請求額', '請求日範囲開始', '請求日範囲終了', '予備金額１', '予備金額２', '回収予定日', '予備ＣＤ１', '予備ＣＤ２', '修正担当者ＣＤ', '修正日'];

}
