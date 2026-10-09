<?php

namespace Modules\Shop\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class StoreTransportType.
 *
 * @package Modules\Shop\Repositories\Models
 */
class StoreTransportType extends Model
{

    protected $table      = 'shop_store_transport_type';
    protected $primaryKey = 'transport_type_id';
    public $timestamps    = false;

    protected $guarded = [];

    protected $casts = [
        'transport_type_free' => 'boolean',
        'transport_type_buildin' => 'boolean'
    ];
}
