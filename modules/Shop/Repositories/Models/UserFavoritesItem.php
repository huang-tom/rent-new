<?php

namespace Modules\Shop\Repositories\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserFavoritesItem.
 *
 * @package Modules\Shop\Repositories\Models
 */
class UserFavoritesItem extends Model
{

    protected $table = 'shop_user_favorites_item';
    protected $primaryKey = 'favorites_item_id';
    public $timestamps = false;

    protected $guarded = [];
}
