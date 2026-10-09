<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewProductPromo extends Model
{
    protected $table = 'pt_new_product_promo';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = [];
}
