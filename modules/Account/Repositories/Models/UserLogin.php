<?php

namespace Modules\Account\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserLogin.
 *
 * @package Modules\Account\Repositories\Models
 */
class UserLogin extends Model
{

    protected $table = 'account_user_login';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $guarded = [];

}
