<?php

namespace Modules\Account\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserFriend.
 *
 * @package Modules\Account\Repositories\Models
 */
class UserFriend extends Model
{

    protected $table      = 'account_user_friend';
    protected $primaryKey = 'user_friend_id';
    public $timestamps    = false;

    protected $guarded = [];

    protected $casts = [];
}
