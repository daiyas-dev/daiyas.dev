<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $部署ＣＤ
 * @property int $コースＣＤ
 * @property float $得意先ＣＤ
 * @property string $日付
 * @property float $分配先得意先ＣＤ
 * @property float $行Ｎｏ
 * @property int $商品ＣＤ
 * @property float $単価
 * @property float $見込数
 * @property float $注文数
 * @property float $実績数
 * @property float $金額
 * @property float $値引
 * @property int $現金売掛区分
 * @property int $主食ＣＤ
 * @property int $副食ＣＤ
 * @property string $修正日
 */
class モバイル販売分配 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'モバイル_販売分配';

    /**
     * @var array
     */
    protected $fillable = ['商品ＣＤ', '単価', '見込数', '注文数', '実績数', '金額', '値引', '現金売掛区分', '主食ＣＤ', '副食ＣＤ', '修正日'];

}
