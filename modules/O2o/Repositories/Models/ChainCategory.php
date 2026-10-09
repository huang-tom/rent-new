<?php

namespace Modules\O2o\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ChainCategory.
 *
 * @package Modules\O2o\Repositories\Models
 */
class ChainCategory extends Model
{

    protected $table = 'o2o_chain_category';
    protected $primaryKey = 'chain_category_id';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'chain_category_enable' => 'boolean'
    ];
}
