<?php

namespace Modules\O2o\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ChainBase.
 *
 * @package Modules\O2o\Repositories\Models
 */
class ChainBase extends Model
{

    protected $table = 'o2o_chain_base';
    protected $primaryKey = 'chain_id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'chain_status' => 'boolean'
    ];
}
