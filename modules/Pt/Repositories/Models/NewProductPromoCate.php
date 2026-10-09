<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewProductPromoCate extends Model
{
    protected $table = 'pt_new_product_promo_cate';
    protected $primaryKey = 'cate_id';
    public $timestamps = false;
    protected $guarded = [];
}
