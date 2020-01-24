<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $チケット調整ID
 * @property int $得意先ＣＤ
 * @property string $日付
 * @property integer $調整理由
 * @property int $商品ＣＤ
 * @property float $チケット減数
 * @property float $SV減数
 * @property int $金額
 * @property int $財務月締ID
 * @property int $請求ID
 * @property int $入金ID
 * @property string $修正日
 * @property int $修正担当者ＣＤ
 */
class チケット調整 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'チケット調整';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'チケット調整ID';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['得意先ＣＤ', '日付', '調整理由', '商品ＣＤ', 'チケット減数', 'SV減数', '金額', '財務月締ID', '請求ID', '入金ID', '修正日', '修正担当者ＣＤ'];

}
