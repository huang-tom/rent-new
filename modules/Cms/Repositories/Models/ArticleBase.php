<?php

namespace Modules\Cms\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ArticleBase.
 *
 * @package Modules\Cms\Repositories\Models
 */
class ArticleBase extends Model
{

    protected $table      = 'cms_article_base';
    protected $primaryKey = 'article_id';
    public $timestamps    = false;

    protected $guarded = [];

    protected $casts = [
        'article_reply_flag' => 'boolean',
        'article_status'     => 'boolean',
        'article_is_popular' => 'boolean'
    ];
}
