<?php

namespace Modules\Shop\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserSearchHistory.
 *
 * @package Modules\Shop\Repositories\Models
 */
class UserSearchHistory extends Model
{

    protected $table = 'shop_user_search_history';
    protected $primaryKey = 'search_id';
    public $timestamps = false;

    protected $guarded = [];
}
