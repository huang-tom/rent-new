<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ProductType.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductType extends Model
{

    protected $table      = 'pt_product_type';
    protected $primaryKey = 'type_id';
    public $timestamps    = false;

    protected $guarded = [];
}
