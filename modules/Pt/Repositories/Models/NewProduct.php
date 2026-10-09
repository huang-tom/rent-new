<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class NewProduct.
 *
 * @package Modules\Pt\Repositories\Models
 */
class NewProduct extends Model
{
    protected $table = 'pt_new_product';
    protected $primaryKey = 'product_id';
    public $timestamps = false;

    protected $guarded = [];
}
