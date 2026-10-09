<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NewProductRentPeriod.
 *
 * @package Modules\Pt\Repositories\Models
 */
class NewProductRentPeriod extends Model
{
    protected $table = 'pt_new_product_rent_period';
    protected $primaryKey = 'period_id';
    public $timestamps = false;

    protected $guarded = [];
}
