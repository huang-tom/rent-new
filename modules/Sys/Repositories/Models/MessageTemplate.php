<?php

namespace Modules\Sys\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MessageTemplate.
 *
 * @package Modules\Sys\Repositories\Models
 */
class MessageTemplate extends Model
{

    protected $table = 'sys_message_template';
    protected $primaryKey = 'message_id';
    protected $keyType = 'string';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'message_enable' => 'boolean',
        'message_sms_enable' => 'boolean',
        'message_email_enable' => 'boolean',
        'message_wechat_enable' => 'boolean',
        'message_xcx_enable' => 'boolean',
        'message_app_enable' => 'boolean',
        'message_sms_force' => 'boolean',
        'message_email_force' => 'boolean',
        'message_app_force' => 'boolean',
        'message_force' => 'boolean'
    ];
}
