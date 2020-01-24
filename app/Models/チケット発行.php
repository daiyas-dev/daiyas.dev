<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $得意先ＣＤ
 * @property int $チケット管理番号
 * @property string $印刷日時
 * @property string $発行日
 * @property string $有効期限
 * @property float $チケット内数
 * @property float $SV内数
 * @property int $単価
 * @property int $金額
 * @property int $商品ＣＤ
 * @property int $担当者ＣＤ
 * @property boolean $廃棄
 * @property string $修正日
 * @property int $修正担当者ＣＤ
 */
class チケット発行 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'チケット発行';

    /**
     * @var array
     */
    protected $fillable = ['印刷日時', '発行日', '有効期限', 'チケット内数', 'SV内数', '単価', '金額', '商品ＣＤ', '担当者ＣＤ', '廃棄', '修正日', '修正担当者ＣＤ'];

}
