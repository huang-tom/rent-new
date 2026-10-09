<?php

namespace Modules\Invoicing\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class StockBillItem.
 *
 * @package Modules\Invoicing\Repositories\Models
 */
class StockBillItem extends Model
{

    protected $table      = 'invoicing_stock_bill_item';
    protected $primaryKey = 'stock_bill_item_id';
    public $timestamps    = false;

    protected $guarded = [];
}
