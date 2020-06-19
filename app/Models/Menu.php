<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $functionId
 * @property string $programId
 * @property string $title
 * @property string $target
 * @property string $route
 * @property boolean $enabled
 */
class Menu extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menus';

    /**
     * @var array
     */
    protected $fillable = ['functionId', 'programId', 'title', 'target', 'route', 'enabled'];

}
