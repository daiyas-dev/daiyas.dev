<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $trigger_kind
 * @property string $日付
 * @property int $部署ＣＤ
 * @property int $コースＣＤ
 * @property float $得意先ＣＤ
 * @property int $商品ＣＤ
 * @property float $数量
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 * @property string $GETDATE
 * @property string $IP
 */
class 売上データ明細トリガーログ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '売上データ明細トリガーログ';

    /**
     * @var array
     */
    protected $fillable = ['trigger_kind', '日付', '部署ＣＤ', 'コースＣＤ', '得意先ＣＤ', '商品ＣＤ', '数量', '修正担当者ＣＤ', '修正日', 'GETDATE', 'IP'];

}
