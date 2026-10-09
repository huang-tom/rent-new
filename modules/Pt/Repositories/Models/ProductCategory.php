<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ProductCategory.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductCategory extends Model
{

    protected $table      = 'pt_product_category';
    protected $primaryKey = 'category_id';
    public $timestamps    = false;

    protected $guarded = [];
    protected $casts = [
        'category_is_enable' => 'boolean'
    ];
}
