<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ProductValidPeriod.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductValidPeriod extends Model
{

    protected $table      = 'pt_product_valid_period';
    protected $primaryKey = 'product_id';
    public $timestamps    = false;

    protected $guarded = [];
    protected $casts = [
        'product_service_date_flag' => 'boolean',
        'product_service_contactor_flag' => 'boolean',
        'product_valid_refund_flag' => 'boolean'
    ];
}
