<?php

namespace Modules\Shop\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserVoucherNum.
 *
 * @package Modules\Shop\Repositories\Models
 */
class UserVoucherNum extends Model
{

    protected $table = 'shop_user_voucher_num';
    protected $primaryKey = 'uvn_id';
    public $timestamps = false;

    protected $guarded = [];
}
