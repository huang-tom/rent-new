<?php

namespace Modules\Cms\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ArticleTag.
 *
 * @package Modules\Cms\Repositories\Models
 */
class ArticleTag extends Model
{

    protected $table      = 'cms_article_tag';
    protected $primaryKey = 'tag_id';
    public $timestamps    = false;

    protected $guarded = [];
}
