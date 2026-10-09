<?php

namespace Modules\Live\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserApply.
 *
 * @package Modules\Live\Repositories\Models
 */
class UserApply extends Model
{

    protected $table = 'live_user_apply';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $guarded = [];
}
