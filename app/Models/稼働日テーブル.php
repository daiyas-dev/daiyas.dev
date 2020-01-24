<?php

namespace App\Models;

use App\Models\BaseModel;

/**
 * @property int $年度
 * @property int $稼働日１
 * @property int $稼働日２
 * @property int $稼働日３
 * @property int $稼働日４
 * @property int $稼働日５
 * @property int $稼働日６
 * @property int $稼働日７
 * @property int $稼働日８
 * @property int $稼働日９
 * @property int $稼働日１０
 * @property int $稼働日１１
 * @property int $稼働日１２
 * @property int $修正担当者CD
 * @property string $修正日
 */
class 稼働日テーブル extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = '稼働日テーブル';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = '年度';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * @var array
     */
    protected $fillable = ['稼働日１', '稼働日２', '稼働日３', '稼働日４', '稼働日５', '稼働日６', '稼働日７', '稼働日８', '稼働日９', '稼働日１０', '稼働日１１', '稼働日１２', '修正担当者CD', '修正日'];

}
