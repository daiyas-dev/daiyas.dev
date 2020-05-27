<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property string $日付
 * @property int $部署ＣＤ
 * @property int $コースＣＤ
 * @property float $行Ｎｏ
 * @property float $得意先ＣＤ
 * @property int $明細行Ｎｏ
 * @property float $受注Ｎｏ
 * @property int $配送担当者ＣＤ
 * @property int $商品ＣＤ
 * @property int $商品区分
 * @property float $現金個数
 * @property float $現金金額
 * @property float $現金値引
 * @property int $現金値引事由ＣＤ
 * @property float $掛売個数
 * @property float $掛売金額
 * @property float $掛売値引
 * @property int $掛売値引事由ＣＤ
 * @property string $請求日付
 * @property float $予備金額１
 * @property float $予備金額２
 * @property int $売掛現金区分
 * @property int $予備ＣＤ２
 * @property int $主食ＣＤ
 * @property int $副食ＣＤ
 * @property int $分配元数量
 * @property int $食事区分
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 売上データ明細 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '売上データ明細';

    /**
     * @var array
     */
    protected $fillable = ['配送担当者ＣＤ', '商品ＣＤ', '商品区分', '現金個数', '現金金額', '現金値引', '現金値引事由ＣＤ', '掛売個数', '掛売金額', '掛売値引', '掛売値引事由ＣＤ', '請求日付', '予備金額１', '予備金額２', '売掛現金区分', '予備ＣＤ２', '主食ＣＤ', '副食ＣＤ', '分配元数量', '食事区分', '備考', '修正担当者ＣＤ', '修正日'];

}
