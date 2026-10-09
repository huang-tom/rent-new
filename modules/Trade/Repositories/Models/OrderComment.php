<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class OrderComment.
 *
 * @package Modules\Trade\Repositories\Models
 */
class OrderComment extends Model
{

    protected $table      = 'trade_order_comment';
    protected $primaryKey = 'order_id';
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $guarded = [];
}
