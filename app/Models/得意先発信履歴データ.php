<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property float $得意先ＣＤ
 * @property string $発信日時
 * @property string $電話番号
 * @property int $履歴区分
 * @property string $コメント
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 得意先発信履歴データ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '得意先発信履歴データ';

    /**
     * @var array
     */
    protected $fillable = ['履歴区分', 'コメント', '修正担当者ＣＤ', '修正日'];

}
