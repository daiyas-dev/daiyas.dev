<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $部署ＣＤ
 * @property string $ファイル名
 * @property string $処理日付
 * @property string $ファイル日付
 * @property string $金融機関ＣＤ
 * @property string $金融機関支店ＣＤ
 * @property integer $口座種別
 * @property string $口座番号
 * @property float $振込合計金額
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 振込見出 extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '振込見出';

    /**
     * @var array
     */
    protected $fillable = ['処理日付', 'ファイル日付', '金融機関ＣＤ', '金融機関支店ＣＤ', '口座種別', '口座番号', '振込合計金額', '修正担当者ＣＤ', '修正日'];

}
