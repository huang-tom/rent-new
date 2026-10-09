<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserCart.
 *
 * @package Modules\Trade\Repositories\Models
 */
class UserCart extends Model
{

    protected $table      = 'trade_user_cart';
    protected $primaryKey = 'cart_id';
    public $timestamps    = false;

    protected $guarded = [];
    protected $casts = [
        'cart_select'  => 'boolean'
    ];
}
