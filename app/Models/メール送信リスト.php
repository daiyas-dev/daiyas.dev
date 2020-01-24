<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property float $得意先ＣＤ
 * @property string $送信先名
 * @property string $送信先名カナ
 * @property string $メールアドレス
 * @property int $認証ＩＤ
 * @property string $認証ＰＷ
 * @property float $累計ポイント
 * @property int $表示パターン
 * @property boolean $配信停止
 * @property string $修正日
 */
class メール送信リスト extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'メール送信リスト';

    /**
     * @var array
     */
    protected $fillable = ['得意先ＣＤ', '送信先名', '送信先名カナ', 'メールアドレス', '認証ＩＤ', '認証ＰＷ', '累計ポイント', '表示パターン', '配信停止', '修正日'];

}
