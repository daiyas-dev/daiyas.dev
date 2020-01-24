<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property string $日時
 * @property float $得意先ＣＤ
 * @property string $得意先名
 * @property int $認証ＩＤ
 * @property string $アクション
 * @property string $特記
 * @property int $警戒レベル
 */
class WEBログ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'WEBログ';

    /**
     * @var array
     */
    protected $fillable = ['日時', '得意先ＣＤ', '得意先名', '認証ＩＤ', 'アクション', '特記', '警戒レベル'];

}
