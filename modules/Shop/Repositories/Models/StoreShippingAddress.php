<?php

namespace Modules\Shop\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class StoreShippingAddress.
 *
 * @package Modules\Shop\Repositories\Models
 */
class StoreShippingAddress extends Model
{

    protected $table      = 'shop_store_shipping_address';
    protected $primaryKey = 'ss_id';
    public $timestamps    = false;

    protected $guarded = [];
    protected $casts = [
        'ss_is_default' => 'boolean'
    ];
}
