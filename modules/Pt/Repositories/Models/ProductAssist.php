<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductAssist.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductAssist extends Model
{

    protected $table = 'pt_product_assist';
    protected $primaryKey = 'assist_id';
    public $timestamps = false;

    protected $guarded = [];
    protected $casts = [
        'assist_is_search' => 'boolean'
    ];
}
