<?php

namespace Modules\Cms\Repositories\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * Class ArticleComment.
 *
 * @package Modules\Cms\Repositories\Models
 */
class ArticleComment extends Model
{

    protected $table      = 'cms_article_comment';
    protected $primaryKey = 'comment_id';
    public    $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'comment_is_show' => 'boolean'
    ];
}
