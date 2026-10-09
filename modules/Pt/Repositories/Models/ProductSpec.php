<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ProductSpec.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductSpec extends Model
{

    protected $table      = 'pt_product_spec';
    protected $primaryKey = 'spec_id';
    public $timestamps    = false;

    protected $guarded = [];
}
