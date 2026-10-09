<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class DictItem.
 *
 * @package Modules\Sys\Repositories\Models
 */
class DictItem extends Model
{

    protected $table      = 'sys_dict_item';
    protected $primaryKey = 'dict_item_id';
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $guarded = [];
}
