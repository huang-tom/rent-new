<?php

namespace Modules\Live\Services\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class WxLive.
 *
 * @package Modules\Live\Services\Models
 */
class WxLive extends Model
{

    protected $table      = 'table_name';
    protected $primaryKey = 'primary_id';
    public $timestamps    = false;

    protected $guarded = [];
}
