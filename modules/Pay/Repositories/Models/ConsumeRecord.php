<?php

namespace Modules\Pay\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ConsumeRecord.
 *
 * @package Modules\Pay\Repositories\Models
 */
class ConsumeRecord extends Model
{

    protected $table      = 'pay_consume_record';
    protected $primaryKey = 'consume_record_id';
    public $timestamps    = false;

    protected $guarded = [];
}
