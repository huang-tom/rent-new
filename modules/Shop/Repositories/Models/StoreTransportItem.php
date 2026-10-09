<?php

namespace Modules\Shop\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class StoreTransportItem.
 *
 * @package Modules\Shop\Repositories\Models
 */
class StoreTransportItem extends Model
{

    protected $table      = 'shop_store_transport_item';
    protected $primaryKey = 'transport_item_id';
    public $timestamps    = false;

    protected $guarded = [];
}
