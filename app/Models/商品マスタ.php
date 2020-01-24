<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $商品ＣＤ
 * @property int $部署グループ
 * @property string $商品名
 * @property string $商品略称
 * @property int $商品区分
 * @property float $売価単価
 * @property int $弁当区分
 * @property int $ｸﾞﾙｰﾌﾟ区分
 * @property int $副食ＣＤ
 * @property int $主食ＣＤ
 * @property int $表示ＦＬＧ
 * @property int $部数単位
 * @property int $食事区分
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 商品マスタ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '商品マスタ';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = '商品ＣＤ';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['部署グループ', '商品名', '商品略称', '商品区分', '売価単価', '弁当区分', 'ｸﾞﾙｰﾌﾟ区分', '副食ＣＤ', '主食ＣＤ', '表示ＦＬＧ', '部数単位', '食事区分', '修正担当者ＣＤ', '修正日'];

}
