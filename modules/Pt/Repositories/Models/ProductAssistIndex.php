<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductAssistIndex.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductAssistIndex extends Model
{

    protected $table = 'pt_product_assist_index';
    protected $primaryKey = 'product_assist_index_id';
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];
}
