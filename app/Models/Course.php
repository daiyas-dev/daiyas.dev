<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $kbn
 * @property string $cd
 * @property string $name
 * @property integer $user_id
 * @property string $fac_kbn
 */
class Course extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['name', 'user_id', 'fac_kbn'];

}
