<?php

namespace Modules\O2o\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ChainItem.
 *
 * @package Modules\O2o\Repositories\Models
 */
class ChainItem extends Model
{

    protected $table = 'o2o_chain_item';
    protected $primaryKey = 'chain_item_id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'chain_item_enable' => 'boolean'
    ];
}
