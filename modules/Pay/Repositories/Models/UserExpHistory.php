<?php

namespace Modules\Pay\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserExpHistory.
 *
 * @package Modules\Pay\Repositories\Models
 */
class UserExpHistory extends Model
{

    protected $table      = 'pay_user_exp_history';
    protected $primaryKey = 'exp_log_id';
    public $timestamps    = false;

    protected $guarded = [];
}
