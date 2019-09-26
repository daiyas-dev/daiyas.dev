<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property float $各種CD
 * @property float $行NO
 * @property string $各種名称
 * @property string $各種略称
 * @property float $サブ各種CD1
 * @property float $サブ各種CD2
 * @property int $修正担当者CD
 * @property string $修正日
 */
class 各種テーブル extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = '各種テーブル';

    /**
     * @var array
     */
    protected $fillable = ['各種名称', '各種略称', 'サブ各種CD1', 'サブ各種CD2', '修正担当者CD', '修正日'];

}
