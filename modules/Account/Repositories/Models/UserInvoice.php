<?php

namespace Modules\Account\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserInvoice.
 *
 * @package Modules\Account\Repositories\Models
 */
class UserInvoice extends Model
{

    protected $table      = 'account_user_invoice';
    protected $primaryKey = 'user_invoice_id';
    public $timestamps    = false;

    protected $guarded = [];
}
