<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $部署ＣＤ
 * @property float $受注Ｎｏ
 * @property string $配達日付
 * @property int $明細Ｎｏ
 * @property int $商品種類
 * @property int $商品ＣＤ
 * @property string $商品名
 * @property string $備考
 * @property float $数量
 * @property float $単価
 * @property float $金額
 * @property float $消費税
 * @property float $提げ袋
 * @property float $風呂敷
 * @property float $予備金額１
 * @property float $予備金額２
 * @property int $予備ＣＤ１
 * @property int $予備ＣＤ２
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 仕出し注文明細データ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '仕出し注文明細データ';

    /**
     * @var array
     */
    protected $fillable = ['商品種類', '商品ＣＤ', '商品名', '備考', '数量', '単価', '金額', '消費税', '提げ袋', '風呂敷', '予備金額１', '予備金額２', '予備ＣＤ１', '予備ＣＤ２', '修正担当者ＣＤ', '修正日'];

}
