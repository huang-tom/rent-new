<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewRentOrderTag extends Model
{
    protected $table = 'trade_new_rent_order_tag';
    protected $primaryKey = 'tag_id';
    public $timestamps = false;
    protected $guarded = [];
}
