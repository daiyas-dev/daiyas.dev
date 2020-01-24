<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $管理ＫＥＹ
 * @property string $会社名
 * @property string $会社名カナ
 * @property string $郵便番号
 * @property string $住所
 * @property string $電話番号
 * @property string $ＦＡＸ
 * @property string $代表取締役
 * @property int $自社締日
 * @property float $単価１
 * @property float $単価２
 * @property float $単価３
 * @property float $単価４
 * @property float $単価５
 * @property float $単価６
 * @property float $単価７
 * @property float $値引
 * @property float $売上伝票Ｎｏ
 * @property float $入金伝票Ｎｏ
 * @property int $原価率１
 * @property int $原価率２
 * @property int $原価率３
 * @property int $原価率４
 * @property int $原価率５
 * @property int $原価率６
 * @property int $原価率７
 * @property int $手当申請額
 * @property int $仕出し製造者ＣＤ
 * @property string $HACCP制定日
 * @property string $HACCP改定日
 * @property string $HACCP改定番号
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 管理マスタ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '管理マスタ';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = '管理ＫＥＹ';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['会社名', '会社名カナ', '郵便番号', '住所', '電話番号', 'ＦＡＸ', '代表取締役', '自社締日', '単価１', '単価２', '単価３', '単価４', '単価５', '単価６', '単価７', '値引', '売上伝票Ｎｏ', '入金伝票Ｎｏ', '原価率１', '原価率２', '原価率３', '原価率４', '原価率５', '原価率６', '原価率７', '手当申請額', '仕出し製造者ＣＤ', 'HACCP制定日', 'HACCP改定日', 'HACCP改定番号', '修正担当者ＣＤ', '修正日'];

}
