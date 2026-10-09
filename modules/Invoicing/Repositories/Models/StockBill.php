<?php

namespace Modules\Invoicing\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class StockBill.
 *
 * @package Modules\Invoicing\Repositories\Models
 */
class StockBill extends Model
{

    protected $table      = 'invoicing_stock_bill';
    protected $primaryKey = 'stock_bill_id';
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $guarded = [];
}
