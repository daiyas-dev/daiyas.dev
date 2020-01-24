<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $部署ＣＤ
 * @property float $受注Ｎｏ
 * @property string $配達日付
 * @property string $配達時間
 * @property string $製造締切時間
 * @property float $得意先ＣＤ
 * @property string $注文日付
 * @property string $注文時間
 * @property int $担当者ＣＤ
 * @property int $営業担当者ＣＤ
 * @property string $配達先１
 * @property string $配達先２
 * @property int $エリアＣＤ
 * @property int $地域区分
 * @property int $AMPM区分
 * @property int $配達区分
 * @property int $連絡区分
 * @property int $税区分
 * @property string $預り日付
 * @property float $預り金
 * @property string $製造用特記
 * @property string $事務用特記
 * @property float $合計数量
 * @property float $合計金額
 * @property float $合計消費税
 * @property int $納品書発行フラグ
 * @property float $予備金額１
 * @property float $予備金額２
 * @property int $予備ＣＤ１
 * @property int $予備ＣＤ２
 * @property int $修正担当者ＣＤ
 * @property string $修正日
 */
class 仕出し注文データ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '仕出し注文データ';

    /**
     * @var array
     */
    protected $fillable = ['配達時間', '製造締切時間', '得意先ＣＤ', '注文日付', '注文時間', '担当者ＣＤ', '営業担当者ＣＤ', '配達先１', '配達先２', 'エリアＣＤ', '地域区分', 'AMPM区分', '配達区分', '連絡区分', '税区分', '預り日付', '預り金', '製造用特記', '事務用特記', '合計数量', '合計金額', '合計消費税', '納品書発行フラグ', '予備金額１', '予備金額２', '予備ＣＤ１', '予備ＣＤ２', '修正担当者ＣＤ', '修正日'];

}
