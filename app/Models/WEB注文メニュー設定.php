<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property string $注文受付日
 * @property string $ボタン表題１
 * @property int $商品CD１
 * @property string $商品名１
 * @property string $商品説明１
 * @property string $商品画像１
 * @property string $ボタン表題２
 * @property int $商品CD２
 * @property string $商品名２
 * @property string $商品説明２
 * @property string $商品画像２
 * @property string $ボタン表題３
 * @property int $商品CD３
 * @property string $商品名３
 * @property string $商品説明３
 * @property string $商品画像３
 * @property string $修正日
 */
class WEB注文メニュー設定 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'WEB注文メニュー設定';

    /**
     * @var array
     */
    protected $fillable = ['注文受付日', 'ボタン表題１', '商品CD１', '商品名１', '商品説明１', '商品画像１', 'ボタン表題２', '商品CD２', '商品名２', '商品説明２', '商品画像２', 'ボタン表題３', '商品CD３', '商品名３', '商品説明３', '商品画像３', '修正日'];

}
