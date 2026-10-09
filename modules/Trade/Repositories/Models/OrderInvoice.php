<?php

namespace Modules\Trade\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class OrderInvoice.
 *
 * @package Modules\Trade\Repositories\Models
 */
class OrderInvoice extends Model
{

    protected $table = 'trade_order_invoice';
    protected $primaryKey = 'order_invoice_id';
    public $timestamps = false;

    protected $guarded = [];
    protected $casts = [
        'invoice_status' => 'boolean',
        'invoice_is_electronic' => 'boolean',
        'invoice_is_company' => 'boolean',
        'order_is_paid' => 'boolean',
        'invoice_cancel' => 'boolean'
    ];
}
