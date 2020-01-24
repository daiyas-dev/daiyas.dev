<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $部署グループ
 * @property int $部署CD
 * @property string $部署名
 * @property string $部署名カナ
 * @property string $会社名称
 * @property string $郵便番号
 * @property string $住所
 * @property string $電話番号
 * @property string $FAX
 * @property int $モバイル_主要商品ＣＤ1
 * @property int $モバイル_主要商品ＣＤ2
 * @property int $モバイル_主要商品ＣＤ3
 * @property string $モバイル_メッセージ
 * @property float $金融機関CD1
 * @property float $金融機関支店CD1
 * @property float $口座種別1
 * @property string $口座番号1
 * @property string $口座名義人1
 * @property float $金融機関CD2
 * @property float $金融機関支店CD2
 * @property float $口座種別2
 * @property string $口座番号2
 * @property string $口座名義人2
 * @property int $工場ＣＤ
 * @property int $修正担当者CD
 * @property string $修正日
 */
class 部署マスタ extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '部署マスタ';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = '部署CD';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['部署グループ', '部署名', '部署名カナ', '会社名称', '郵便番号', '住所', '電話番号', 'FAX', 'モバイル_主要商品ＣＤ1', 'モバイル_主要商品ＣＤ2', 'モバイル_主要商品ＣＤ3', 'モバイル_メッセージ', '金融機関CD1', '金融機関支店CD1', '口座種別1', '口座番号1', '口座名義人1', '金融機関CD2', '金融機関支店CD2', '口座種別2', '口座番号2', '口座名義人2', '工場ＣＤ', '修正担当者CD', '修正日'];

}
