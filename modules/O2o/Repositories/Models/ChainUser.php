<?php

namespace Modules\O2o\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ChainUser.
 *
 * @package Modules\O2o\Repositories\Models
 */
class ChainUser extends Model
{

    protected $table = 'o2o_chain_user';
    protected $primaryKey = 'chain_user_id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'chain_user_is_admin' => 'boolean'
    ];
}
