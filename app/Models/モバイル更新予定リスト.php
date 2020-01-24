<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property string $日付
 * @property float $得意先ＣＤ
 * @property boolean $更新フラグ
 * @property string $更新日
 * @property string $処理区分
 * @property int $モバイルデータ更新_部署ＣＤ
 * @property int $モバイルデータ更新_コースＣＤ
 * @property string $WebService_メソッド名
 * @property int $WebService_部署ＣＤ
 * @property int $WebService_コースＣＤ
 * @property string $WebService_更新区分
 */
class モバイル更新予定リスト extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'モバイル_更新予定リスト';

    /**
     * @var array
     */
    protected $fillable = ['更新フラグ', '更新日', '処理区分', 'モバイルデータ更新_部署ＣＤ', 'モバイルデータ更新_コースＣＤ', 'WebService_メソッド名', 'WebService_部署ＣＤ', 'WebService_コースＣＤ', 'WebService_更新区分'];

}
