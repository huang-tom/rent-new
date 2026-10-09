<?php

namespace Modules\Account\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserZoneRel.
 *
 * @package Modules\Account\Repositories\Models
 */
class UserZoneRel extends Model
{

    protected $table      = 'account_user_zone_rel';
    protected $primaryKey = 'zone_rel_id';
    public $timestamps    = false;

    protected $guarded = [];

    protected $casts = [];
}
