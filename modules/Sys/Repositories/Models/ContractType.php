<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ContractType.
 *
 * @package Modules\Sys\Repositories\Models
 */
class ContractType extends Model
{

    protected $table = 'sys_contract_type';
    protected $primaryKey = 'contract_type_id';
    public $timestamps = false;

    protected $guarded = [];
    protected $casts = [
        'contract_type_enable' => 'boolean',
        'contract_type_buildin' => 'boolean'
    ];
}
