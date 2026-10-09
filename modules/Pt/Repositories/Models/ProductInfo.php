<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductInfo.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductInfo extends Model
{

    protected $table = 'pt_product_info';
    protected $primaryKey = 'product_id';
    public $timestamps = false;

    protected $guarded = [];
}
