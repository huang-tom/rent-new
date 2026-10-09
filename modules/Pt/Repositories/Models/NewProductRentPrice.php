<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NewProductRentPrice.
 *
 * @package Modules\Pt\Repositories\Models
 */
class NewProductRentPrice extends Model
{
    protected $table = 'pt_new_product_rent_price';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $guarded = [];
}
