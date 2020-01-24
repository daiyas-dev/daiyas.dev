<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property string $入金日付
 * @property float $伝票Ｎｏ
 * @property int $部署ＣＤ
 * @property float $得意先ＣＤ
 * @property float $入金区分
 * @property float $現金
 * @property float $小切手
 * @property float $振込
 * @property float $バークレー
 * @property float $その他
 * @property float $相殺
 * @property float $値引
 * @property string $摘要
 * @property string $備考
 * @property string $請求日付
 * @property float $予備金額１
 * @property float $予備金額２
 * @property int $予備ＣＤ１
 * @property int $予備ＣＤ２
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 入金データ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '入金データ';

    /**
     * @var array
     */
    protected $fillable = ['部署ＣＤ', '得意先ＣＤ', '入金区分', '現金', '小切手', '振込', 'バークレー', 'その他', '相殺', '値引', '摘要', '備考', '請求日付', '予備金額１', '予備金額２', '予備ＣＤ１', '予備ＣＤ２', '修正担当者ＣＤ', '修正日'];

}
