<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductComment.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductComment extends Model
{

    protected $table = 'pt_product_comment';
    protected $primaryKey = 'comment_id';
    public $timestamps = false;

    protected $guarded = [];
    protected $casts = [
        'comment_enable' => 'boolean'
    ];
}
