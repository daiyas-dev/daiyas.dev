<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property float $得意先ＣＤ
 * @property int $得意先履歴ID
 * @property int $状態区分
 * @property int $失客理由
 * @property string $失客日
 * @property string $承認日
 * @property int $承認者ＣＤ
 * @property string $登録日
 * @property int $営業担当者ＣＤ
 * @property int $変更者ＣＤ
 */
class 得意先履歴テーブル extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '得意先履歴テーブル';

    /**
     * @var array
     */
    protected $fillable = ['状態区分', '失客理由', '失客日', '承認日', '承認者ＣＤ', '登録日', '営業担当者ＣＤ', '変更者ＣＤ'];

}
