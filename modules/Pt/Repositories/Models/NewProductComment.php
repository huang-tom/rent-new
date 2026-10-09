<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewProductComment extends Model
{
    protected $table = 'pt_new_product_comment';
    protected $primaryKey = 'comment_id';
    public $timestamps = false;
    protected $guarded = [];
}
