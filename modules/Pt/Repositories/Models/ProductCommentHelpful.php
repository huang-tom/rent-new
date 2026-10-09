<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductCommentHelpful.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductCommentHelpful extends Model
{

    protected $table = 'pt_product_comment_helpful';
    protected $primaryKey = 'comment_helpful_id';
    public $timestamps = false;

    protected $guarded = [];

}
