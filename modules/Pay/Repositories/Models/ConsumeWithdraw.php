<?php

namespace Modules\Pay\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ConsumeWithdraw.
 *
 * @package Modules\Pay\Repositories\Models
 */
class ConsumeWithdraw extends Model
{

    protected $table      = 'pay_consume_withdraw';
    protected $primaryKey = 'withdraw_id';
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $guarded = [];
}
