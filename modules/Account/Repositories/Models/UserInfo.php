<?php

namespace Modules\Account\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserInfo.
 *
 * @package Modules\Account\Repositories\Models
 */
class UserInfo extends Model
{
    protected $table      = 'account_user_info';
    protected $primaryKey = 'user_id';
    public $timestamps    = false;

    protected $guarded = [];
}
