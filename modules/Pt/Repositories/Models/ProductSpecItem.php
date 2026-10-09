<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ProductSpecItem.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductSpecItem extends Model
{

    protected $table      = 'pt_product_spec_item';
    protected $primaryKey = 'spec_item_id';
    public $timestamps    = false;

    protected $guarded = [];
    protected $casts = [
        'spec_item_enable' => 'boolean'
    ];
}
