<?php

namespace Modules\Pt\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ProductAssistItem.
 *
 * @package Modules\Pt\Repositories\Models
 */
class ProductAssistItem extends Model
{

    protected $table      = 'pt_product_assist_item';
    protected $primaryKey = 'assist_item_id';
    public $timestamps    = false;

    protected $guarded = [];
}
