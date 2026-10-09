<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ProductBrand.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductBrand extends Model
{

    protected $table      = 'pt_product_brand';
    protected $primaryKey = 'brand_id';
    public $timestamps    = false;

    protected $guarded = [];
    protected $casts = [
        'brand_recommend' => 'boolean',
        'brand_enable' => 'boolean'
    ];
}
