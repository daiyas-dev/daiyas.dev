<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Information extends Model
{
    protected $table = 'informations';
    protected $primaryKey = 'info_no';

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
