<?php

namespace Modules\Account\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserDeliveryAddress.
 *
 * @package Modules\Account\Repositories\Models
 */
class UserDeliveryAddress extends Model
{

    protected $table = 'account_user_delivery_address';
    protected $primaryKey = 'ud_id';
    public $timestamps = false;

    protected $guarded = [];
    protected $casts = [
        'ud_is_default' => 'boolean'
    ];
}
