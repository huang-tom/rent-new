<?php

namespace Modules\Account\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserMessage.
 *
 * @package Modules\Account\Repositories\Models
 */
class UserMessage extends Model
{

    protected $table      = 'account_user_message';
    protected $primaryKey = 'message_id';
    public $timestamps    = false;

    protected $guarded = [];

    protected $casts = [
        'message_is_read'   => 'boolean',
        'message_is_delete' => 'boolean'
    ];
}
