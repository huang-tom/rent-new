<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

class NewOrderTag extends Model
{
    protected $table = 'trade_new_order_tag';
    protected $primaryKey = 'tag_id';
    public $timestamps = false;
    protected $guarded = [];
}
