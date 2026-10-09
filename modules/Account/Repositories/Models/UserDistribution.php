<?php

namespace Modules\Account\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserDistribution.
 *
 * @package Modules\Account\Repositories\Models
 */
class UserDistribution extends Model
{

    protected $table = 'account_user_distribution';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'user_active' => 'boolean',
        'user_is_pt' => 'boolean',
        'user_is_sp' => 'boolean'
    ];
}
