<?php

namespace Modules\Pay\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class UserBankCard.
 *
 * @package Modules\Pay\Repositories\Models
 */
class UserBankCard extends Model
{

    protected $table      = 'pay_user_bank_card';
    protected $primaryKey = 'user_bank_id';
    public $timestamps    = false;

    protected $guarded = [];
}
