<?php

namespace Modules\Account\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserGroupRel.
 *
 * @package Modules\Account\Repositories\Models
 */
class UserGroupRel extends Model
{

    protected $table      = 'account_user_group_rel';
    protected $primaryKey = 'group_rel_id';
    public $timestamps    = false;

    protected $guarded = [];

    protected $casts = [];
}
