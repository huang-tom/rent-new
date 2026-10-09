<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductBase.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductBase extends Model
{

    protected $table = 'pt_product_base';
    protected $primaryKey = 'product_id';
    public $timestamps = false;

    protected $guarded = [];
}
