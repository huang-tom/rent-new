<?php

namespace Modules\Account\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserZone.
 *
 * @package Modules\Account\Repositories\Models
 */
class UserZone extends Model
{

    protected $table      = 'account_user_zone';
    protected $primaryKey = 'zone_id';
    public $timestamps    = false;

    protected $guarded = [];

    protected $casts = [];
}
