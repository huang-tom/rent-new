<?php

namespace Modules\Account\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserTagBase.
 *
 * @package Modules\Account\Repositories\Models
 */
class UserTagBase extends Model
{

    protected $table      = 'account_user_tag_base';
    protected $primaryKey = 'tag_id';
    public $timestamps    = false;

    protected $guarded = [];

    protected $casts = [
        'tag_enable' => 'boolean'
    ];
}
