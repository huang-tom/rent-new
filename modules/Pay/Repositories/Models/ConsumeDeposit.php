<?php

namespace Modules\Pay\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ConsumeDeposit.
 *
 * @package Modules\Pay\Repositories\Models
 */
class ConsumeDeposit extends Model
{

    protected $table = 'pay_consume_deposit';
    protected $primaryKey = 'deposit_id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'deposit_review' => 'boolean'
    ];

}
