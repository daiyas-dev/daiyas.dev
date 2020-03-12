<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $銀行CD
 * @property int $支店CD
 * @property string $支店名
 * @property string $支店名カナ
 */
class 金融機関支店名称 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '金融機関支店名称';

    /**
     * @var array
     */
    protected $fillable = ['支店名', '支店名カナ', '無効フラグ'];

}
