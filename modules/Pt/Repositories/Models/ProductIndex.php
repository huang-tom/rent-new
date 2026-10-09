<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductIndex.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductIndex extends Model
{

    protected $table = 'pt_product_index';
    protected $primaryKey = 'product_id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'store_is_open' => 'boolean',
        'product_is_invoices' => 'boolean',
        'product_is_return' => 'boolean',
        'product_is_recommend' => 'boolean',
        'store_is_selfsupport' => 'boolean',
        'product_sp_enable' => 'boolean',
        'product_dist_enable' => 'boolean',
        'product_is_lock' => 'boolean'
    ];
}
