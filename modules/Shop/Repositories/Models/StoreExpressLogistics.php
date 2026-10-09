<?php

namespace Modules\Shop\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class StoreExpressLogistics.
 *
 * @package Modules\Shop\Repositories\Models
 */
class StoreExpressLogistics extends Model
{

    protected $table      = 'shop_store_express_logistics';
    protected $primaryKey = 'logistics_id';
    public $timestamps    = false;

    protected $guarded = [];

    protected $casts = [
        'logistics_is_enable'  => 'boolean',
        'logistics_is_default' => 'boolean'
    ];
}
