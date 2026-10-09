<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ProductImage.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductImage extends Model
{

    protected $table      = 'pt_product_image';
    protected $primaryKey = 'product_image_id';
    public $timestamps    = false;

    protected $guarded = [];
}
