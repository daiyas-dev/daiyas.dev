<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $担当者ＣＤ
 * @property string $担当者名
 * @property string $担当者名カナ
 * @property string $郵便番号
 * @property string $住所
 * @property string $電話番号
 * @property string $ＦＡＸ
 * @property int $所属部署ＣＤ
 * @property boolean $オペレータ
 * @property string $ユーザーＩＤ
 * @property string $パスワード
 * @property int $権限区分
 * @property int $営業業務区分
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 担当者マスタ extends Authenticatable
{
    use Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '担当者マスタ';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = '担当者ＣＤ';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['担当者名', '担当者名カナ', '郵便番号', '住所', '電話番号', 'ＦＡＸ', '所属部署ＣＤ', 'オペレータ', 'ユーザーＩＤ', 'パスワード', '権限区分', '在職区分', '営業業務区分', '修正担当者ＣＤ', '修正日'];

    /**
     * Join 部署マスタ on 所属部署ＣＤ
     */
    public function 部署()
    {
        return $this->belongsTo('App\Models\部署マスタ', '所属部署ＣＤ', '部署CD');
    }

    /**
     * Overrides Illuminate\Auth\Authenticatable getAuthPassword
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->パスワード;
    }
}
