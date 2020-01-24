<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $ＣＤ
 * @property string $ＰＯＰサーバー
 * @property string $ＰＯＰユーザー名
 * @property string $ＰＯＰパスワード
 * @property boolean $ＰＯＰＳＭＴＰ
 * @property string $ＳＭＴＰサーバー
 * @property string $ＳＭＴＰユーザー名
 * @property string $ＳＭＴＰパスワード
 * @property string $ｆｒｏｍ
 * @property string $ｔｏ
 * @property string $タイトル
 * @property string $本文
 * @property boolean $注文ＵＲＬ付加
 * @property string $注文ＵＲＬ
 * @property string $メール送信予定日
 * @property string $メール送信予定時間
 * @property boolean $自動配信
 * @property string $修正日
 */
class メール送信設定 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'メール送信設定';

    /**
     * @var array
     */
    protected $fillable = ['ＣＤ', 'ＰＯＰサーバー', 'ＰＯＰユーザー名', 'ＰＯＰパスワード', 'ＰＯＰＳＭＴＰ', 'ＳＭＴＰサーバー', 'ＳＭＴＰユーザー名', 'ＳＭＴＰパスワード', 'ｆｒｏｍ', 'ｔｏ', 'タイトル', '本文', '注文ＵＲＬ付加', '注文ＵＲＬ', 'メール送信予定日', 'メール送信予定時間', '自動配信', '修正日'];

}
