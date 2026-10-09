<?php

namespace Modules\Cms\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ArticleCategory.
 *
 * @package Modules\Cms\Repositories\Models
 */
class ArticleCategory extends Model
{

    protected $table      = 'cms_article_category';
    protected $primaryKey = 'category_id';
    public $timestamps    = false;

    protected $guarded = [];
}
