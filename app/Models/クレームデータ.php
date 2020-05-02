<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $クレームID
 * @property string $受付日時
 * @property int $管轄部門コード
 * @property int $クレーム区分コード
 * @property float $顧客コード
 * @property int $平均食数
 * @property string $顧客担当者名
 * @property int $商品コード
 * @property float $単価
 * @property string $クレーム内容
 * @property int $受付担当者コード
 * @property int $受付方法
 * @property string $クレーム処理日
 * @property int $クレーム処理者コード
 * @property string $クレーム処理品
 * @property float $クレーム処理費用
 * @property string $客先反応
 * @property int $部門コード
 * @property string $部門名
 * @property int $原因部署担当コード
 * @property string $原因
 * @property int $原因入力担当者コード
 * @property string $原因入力担当者名
 * @property string $対策
 * @property int $対策入力担当者コード
 * @property string $対策入力担当者名
 * @property int $その後客先失客
 * @property string $失客日
 * @property int $失客日数
 * @property int $未使用フラグ
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class クレームデータ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'クレームデータ';

    /**
     * @var array
     */
    protected $fillable = ['受付日時', '管轄部門コード', 'クレーム区分コード', '顧客コード', '平均食数', '顧客担当者名', '商品コード', '単価', 'クレーム内容', '受付担当者コード', '受付方法', 'クレーム処理日', 'クレーム処理者コード', 'クレーム処理品', 'クレーム処理費用', '客先反応', '部門コード', '部門名', '原因部署担当コード', '原因', '原因入力担当者コード', '原因入力担当者名', '対策', '対策入力担当者コード', '対策入力担当者名', 'その後客先失客', '失客日', '失客日数', '未使用フラグ', '修正担当者ＣＤ', '修正日'];

}
