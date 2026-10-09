<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewProductCommentReply extends Model
{
    protected $table = 'pt_new_product_comment_reply';
    protected $primaryKey = 'reply_id';
    public $timestamps = false;
    protected $guarded = [];
}
