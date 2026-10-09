<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class CurrencyBase.
 *
 * @package Modules\Sys\Repositories\Models
 */
class CurrencyBase extends Model
{

    protected $table = 'sys_currency_base';
    protected $primaryKey = 'currency_id';
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];
    protected $casts = [
        'currency_is_default' => 'boolean',
        'currency_default_lang' => 'boolean',
        'currency_is_standard' => 'boolean',
        'currency_status' => 'boolean',
        'currency_decimal_place' => 'boolean'
    ];
}
