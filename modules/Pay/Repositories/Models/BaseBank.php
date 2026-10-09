<?php

namespace Modules\Pay\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseBank.
 *
 * @package Modules\Pay\Repositories\Models
 */
class BaseBank extends Model
{

    protected $table = 'pay_base_bank';
    protected $primaryKey = 'bank_id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'bank_enable' => 'boolean'
    ];
}
