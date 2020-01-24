<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $既定製造パターン
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
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 製造品マスタ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '製造品マスタ';

    /**
     * @var array
     */
    protected $fillable = ['部署グループ', '商品名', '商品略称', '商品区分', '売価単価', '弁当区分', 'ｸﾞﾙｰﾌﾟ区分', '副食ＣＤ', '主食ＣＤ', '表示ＦＬＧ', '部数単位', '修正担当者ＣＤ', '修正日'];

}
