<?php

namespace Modules\Account\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserTagGroup.
 *
 * @package Modules\Account\Repositories\Models
 */
class UserTagGroup extends Model
{

    protected $table      = 'account_user_tag_group';
    protected $primaryKey = 'tag_group_id';
    public $timestamps    = false;

    protected $guarded = [];

    protected $casts = [
        'tag_group_enable' => 'boolean'
    ];
}
