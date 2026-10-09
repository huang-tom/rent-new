<?php

namespace Modules\Pay\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserPay.
 *
 * @package Modules\Pay\Repositories\Models
 */
class UserPay extends Model
{

    protected $table      = 'pay_user_pay';
    protected $primaryKey = 'user_id';
    public $timestamps    = false;

    protected $guarded = [];
}
