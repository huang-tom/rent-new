<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ProductItem.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductItem extends Model
{

    protected $table      = 'pt_product_item';
    protected $primaryKey = 'item_id';
    public $timestamps    = false;

    protected $guarded = [];
}
