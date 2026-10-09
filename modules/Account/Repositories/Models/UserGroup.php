<?php

namespace Modules\Account\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserGroup.
 *
 * @package Modules\Account\Repositories\Models
 */
class UserGroup extends Model
{

    protected $table      = 'account_user_group';
    protected $primaryKey = 'group_id';
    public $timestamps    = false;

    protected $guarded = [];

    protected $casts = [];
}
