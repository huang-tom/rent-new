<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ProductTag.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductTag extends Model
{

    protected $table      = 'pt_product_tag';
    protected $primaryKey = 'product_tag_id';
    public $timestamps    = false;

    protected $guarded = [];
}
