<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductCommentReply.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductCommentReply extends Model
{

    protected $table = 'pt_product_comment_reply';
    protected $primaryKey = 'comment_reply_id';
    public $timestamps = false;

    protected $guarded = [];
    protected $casts = [
        'comment_reply_enable' => 'boolean'
    ];
}
