<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class NumberSeq.
 *
 * @package Modules\Sys\Repositories\Models
 */
class NumberSeq extends Model
{

    protected $table      = 'sys_number_seq';
    protected $primaryKey = 'prefix';
    protected $keyType    = 'string';
    public $timestamps    = false;

    protected $guarded = [];
}
