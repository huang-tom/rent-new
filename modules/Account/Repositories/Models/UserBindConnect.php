<?php

namespace Modules\Account\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserBindConnect.
 *
 * @package Modules\Account\Repositories\Models
 */
class UserBindConnect extends Model
{

    protected $table      = 'account_user_bind_connect';
    protected $primaryKey = 'bind_id';
    public $timestamps    = false;

    protected $guarded = [];

    protected $casts = [
        'bind_active' => 'boolean'
    ];
}
