<?php

namespace Modules\Pay\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserResource.
 *
 * @package Modules\Pay\Repositories\Models
 */
class UserResource extends Model
{

    protected $table      = 'pay_user_resource';
    protected $primaryKey = 'user_id';
    public $timestamps    = false;

    protected $guarded = [];
}
