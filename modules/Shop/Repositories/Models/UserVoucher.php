<?php

namespace Modules\Shop\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserVoucher.
 *
 * @package Modules\Shop\Repositories\Models
 */
class UserVoucher extends Model
{

    protected $table = 'shop_user_voucher';
    protected $primaryKey = 'user_voucher_id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'activity_rule'=>'array'
    ];
}
