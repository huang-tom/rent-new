<?php

namespace Modules\Account\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserLevel.
 *
 * @package Modules\Account\Repositories\Models
 */
class UserLevel extends Model
{

    protected $table      = 'account_user_level';
    protected $primaryKey = 'user_level_id';
    public $timestamps    = false;

    protected $guarded = [];
}
