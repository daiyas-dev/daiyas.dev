<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $Web受注ID
 * @property int $注文ID
 * @property int $注文情報ID
 * @property string $注文日時
 * @property int $商品ＣＤ
 * @property int $単価
 * @property float $現金個数
 * @property float $現金金額
 * @property float $掛売個数
 * @property float $掛売金額
 * @property int $届け先ID
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class Web受注データ利用者別詳細 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'Web受注データ利用者別詳細';

    /**
     * @var array
     */
    protected $fillable = ['注文日時', '商品ＣＤ', '単価', '現金個数', '現金金額', '掛売個数', '掛売金額', '届け先ID', '修正担当者ＣＤ', '修正日'];

}
